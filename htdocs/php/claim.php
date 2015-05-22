<?php

include_once(dirname(__FILE__).'/bitcoin.php');
include_once(dirname(__FILE__).'/blockstrap.php');
include_once(dirname(__FILE__).'/cache.php');
include_once(dirname(__FILE__).'/api.php');
include_once(dirname(__FILE__).'/Mandrill.php');

function debug($obj)
{
    echo '<pre>';
    print_r($obj);
    echo '</pre>';
}

$ini = parse_ini_file(dirname(dirname(dirname(__FILE__))).'/config.ini', true);

$salts = $ini['salts'];
$keys = $ini['keys'];
$app = $ini['app'];
$ports = $ini['ports'];
$usernames = $ini['usernames'];
$passwords = $ini['passwords'];
$hosts = $ini['hosts'];
$codes = $ini['codes'];
$addresses = $ini['addresses'];
$email = false;
$chain = false;
$code = false;
$address = false;
$tx = false;
$sent = false;

if(isset($_POST) && isset($_POST['email'])) $email = $_POST['email'];
if(isset($_POST) && isset($_POST['chain'])) $chain = $_POST['chain'];
if(isset($_POST) && isset($_POST['code'])) $code = $_POST['code'];
if(isset($_POST) && isset($_POST['address'])) $address = $_POST[''];
if(isset($_POST) && isset($_POST['sent'])) $sent = $_POST['sent'];
if(isset($_POST) && isset($_POST['tx'])) $tx = $_POST['tx'];

$emails = new Mandrill($keys['mandrill']);

$results = Array(
    'success' => false,
    'msg' => '<p>Valid email address required.</p>'
);

$fees = array(
    'btct' => 0.0001,
    'dasht' => 0.0001,
    'doget' => 1,
    'ltct' => 0.001
);

if(isset($salts['emails']) && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $code && $chain)
{
    $today = date('U', strtotime(date('y-m-d H:00')));
    $activation_code = hash('sha256', $salts['emails'].$email.$today);
    $claim_code = substr($activation_code, 0, 6);
    
    $tag = false;
    $got_tag = false;
    $check_addresses = $emails->tags->getList();
    if(is_array($check_addresses) && count($check_addresses) > 0)
    {
        foreach($check_addresses as $key => $list)
        {
            if($list['tag'] == 'em_'.md5($email).'_sent')
            {
                $got_tag = true;
            }
        }
    }
    if($got_tag === true)
    {
        $tag = $emails->tags->info('em_'.md5($email).'_sent');
    }
    
    $reason = 'Unable to verify ruleset.';
    $original_reason = $reason;
    
    if(is_array($tag))
    {
        $results['tags'] = $tag;
        
        $lifetime_sent = $tag['sent'] - 1;
        $daily_sent = $tag['stats']['today']['sent'];
        $weekly_sent = $tag['stats']['last_7_days']['sent'];
        $monthly_sent = $tag['stats']['last_30_days']['sent'];
        
        if($lifetime_sent >= $codes['lifetime'] && $codes['lifetime'] > 0)
        {
            $reason = 'Lifetime limit for this email address has been surpassed.';
        }
        if($monthly_sent >= $codes['monthly'] && $codes['monthly'] > 0)
        {
            $reason = 'Monthly limit for this email address has been surpassed.';
        }
        if($weekly_sent >= $codes['weekly'] && $codes['weekly'] > 0)
        {
            $reason = 'Weekly limit for this email address has been surpassed.';
        }
        if($daily_sent >= $codes['daily'] && $codes['daily'] > 0)
        {
            $reason = 'Daily limit for this email address has been surpassed.';
        }
        $white_list = false;
        if($reason != $original_reason && isset($codes['whitelist']))
        {
            $white_list = explode(', ', $codes['whitelist']);
            foreach($white_list as $key => $address)
            {
                if($address === $email)
                {
                    $reason = $original_reason;
                }
            }   
        }
    }
    
    if($claim_code == $code && $reason == $original_reason)
    {
        $api = new bs_api();
        $address_obj = $api->address(array(
            'id' => $addresses[$chain],
            'chain' => $chain
        ));
        $fee = $fees[$chain] * 100000000;
        $amount_to_send = $fee * 3;
        if($sent) $amount_to_send = $sent;
        if(is_array($address_obj) && isset($address_obj['balance']) && $address_obj['balance'] >= ($amount_to_send))
        {
            if(isset($keys[$chain]) && $tx)
            {
                try
                {
                    $bitcoin = new Bitcoin($usernames[$chain], $passwords[$chain], $hosts[$chain], $ports[$chain]);
                    $outputs = null;
                    $keys = [$keys[$chain]];
                    $raw_tx_results = $bitcoin->signrawtransaction($tx, $outputs, $keys);
                    if(is_array($raw_tx_results) && isset($raw_tx_results['hex']))
                    {
                        $raw_tx = $raw_tx_results['hex'];
                        $txid = $bitcoin->sendrawtransaction($raw_tx);
                        if($txid)
                        {
                            $results['success'] = true;
                            $results['txid'] = $txid;
                            $results['msg'] = '<p>Successfully sent '.($amount_to_send / 100000000).' coins to '.$address.'</p>';
                            $bc_chain = $chain;
                            if($bc_chain == 'doget') $bc_chain = 'dogt';
                            if($bc_chain == 'dasht') $bc_chain = 'drkt';
                            if($bc_chain == 'dash') $bc_chain = 'drk';
                            $message = array(
                                'subject' => 'Blockstrap Verification',
                                'html' => $results['msg'].'<p>TXID: <a href="http://blockchains.io/'.$bc_chain.'/transaction/'.$txid.'/">'.$txid.'</a></p>',
                                'from_email' => $app['email'],
                                'from_name' => $app['name'],
                                'to' => array(
                                    array(
                                        'email' => $email,
                                        'type' => 'to'
                                    )
                                ),
                                'tags' => array('em_'.md5($email).'_sent')
                            );
                            $result = $emails->messages->send($message);
                        }
                        else
                        {
                            $results['msg'] = '<p>Unable to relay the transaction</p>';
                        }
                        
                    }
                    else
                    {
                        $results['msg'] = '<p>Unable to sign the transaction</p>';
                    }
                }
                catch(error $e) 
                {
                    $results['msg'] = '<p>Unable to send the transaction</p>';
                }
            }
            else
            {
                $results['msg'] = '<p>Now need to sign un-signed rawTX</p>';
            }
        }
        else
        {
            $results['msg'] = '<p>Sorry, but this faucet does not have enough funds.</p><p>Please try again shortly as we may have had a donation since then.</p><p>If you would like to notify us of this problem please contact us at support@blockstrap.com or reach out to us on Twitter @blockstrap</p>';
        }
    }
    else
    {
        if($reason == $original_reason)
        {
            $results['msg'] = '<p>Invalid claim code!</p>';
        }
        else
        {
            $results['msg'] = $reason;
        }
    }
}
else if(isset($salts['emails']) && $email && filter_var($email, FILTER_VALIDATE_EMAIL))
{
    $today = date('U', strtotime(date('y-m-d H:00')));
    $activation_code = hash('sha256', $salts['emails'].$email.$today);
    $claim_code = substr($activation_code, 0, 6);
    $results['return_address'] = $addresses[$chain];
    $results['msg'] = '<p>Email address has been hashed.</p>';
    $email_contents = '<p>Thanks for signing-up to Blockstrap faucets!</p><p>Your claim code is <strong>'.$claim_code.'</strong></p><p>It will remain valid for the next 60 minutes.</p><p>Kind regards;</p><p>Team Blockstrap</p>';
    try
    {
        $message = array(
            'subject' => $app['subject'],
            'html' => $email_contents,
            'from_email' => $app['email'],
            'from_name' => $app['name'],
            'to' => array(
                array(
                    'email' => $email,
                    'type' => 'to'
                )
            ),
            'tags' => array('em_'.md5($email))
        );
        $result = $emails->messages->send($message);
        if(is_array($result) && isset($result[0]['status']) && $result[0]['status'] == 'sent')
        {
            $results['success'] = true;
            $results['msg'] = '<p>An email containing the claim code has been sent to you.</p>';
        }
        else
        {
            $results['msg'] = '<p>Unable to send email.</p>';
        }
    } 
    catch(Mandrill_Error $e) 
    {
        $results['msg'] = '<p>Unable to send email.</p>';
    }
}

echo json_encode($results); exit;