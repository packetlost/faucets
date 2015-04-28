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
$addresses = $ini['addresses'];
$email = false;
$chain = false;
$code = false;
$address = false;
$tx = false;

if(isset($_POST) && isset($_POST['email'])) $email = $_POST['email'];
if(isset($_POST) && isset($_POST['chain'])) $chain = $_POST['chain'];
if(isset($_POST) && isset($_POST['code'])) $code = $_POST['code'];
if(isset($_POST) && isset($_POST['address'])) $address = $_POST['address'];
if(isset($_POST) && isset($_POST['tx'])) $tx = $_POST['tx'];

$emails = new Mandrill($keys['mandrill']);

/*
$bitcoin = new Bitcoin('test', 'eektesting123', '127.0.0.1');
$raw_unsigned_tx = '01000000011fb555eead147f5c6d5a565958ac8a1d3eb834f115b8b68ee42f7d5b13ee1f050100000000ffffffff03e8030000000000001976a9141fdf0e4b2c97c9afa9ebfccb6f0406e55bbe5d6688acb40c0000000000001976a914c4162517d897e521a34dc83e021503d40e79d9f588ac0000000000000000026a0000000000';
$outputs = null;
$keys = ['KynwwR5imwjVnGz5ibaDwSrx78j67dSywtsmjGVXow9Hwyy54mBC'];
$bitcoin->signrawtransaction($raw_unsigned_tx, $outputs, $keys);
$raw_tx = $bitcoin->signrawtransaction($raw_unsigned_tx, $outputs, $keys);
*/

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
    $activation_code = hash('sha256', $salts['emails'].$email);
    $claim_code = substr($activation_code, 0, 6);
    if($claim_code == $code)
    {
        $api = new bs_api();
        $address_obj = $api->address(array(
            'id' => $addresses[$chain],
            'chain' => $chain
        ));
        $fee = $fees[$chain] * 100000000;
        if(is_array($address_obj) && isset($address_obj['balance']) && $address_obj['balance'] >= ($fee * 11))
        {
            if(isset($keys[$chain]) && $tx)
            {
                try
                {
                    $bitcoin = new Bitcoin('test', 'eektesting123', '127.0.0.1', $ports[$chain]);
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
                            $results['msg'] = '<p>Successfully sent coins to '.$address.'</p>';
                        }
                        else
                        {
                            $results['msg'] = '<p>Unable to rely the transaction</p>';
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
            $results['msg'] = '<p>Sorry, but this faucet does not have enough funds so cannot send you any.</p><p>Please try again shortly as we may have had a donation since then.</p>';
        }
    }
    else
    {
        $results['msg'] = '<p>Invalid claim code!</p>';
    }
}
else if(isset($salts['emails']) && $email && filter_var($email, FILTER_VALIDATE_EMAIL))
{
    $activation_code = hash('sha256', $salts['emails'].$email);
    $claim_code = substr($activation_code, 0, 6);
    $results['return_address'] = $addresses[$chain];
    $results['msg'] = '<p>Email address has been hashed.</p>';
    $email_contents = '<p>Thanks for signing-up to our faucets!</p><p>Your claim code is <strong>'.$claim_code.'</strong></p><p>Kind regards;</p><p>Team Blockstrap</p>';
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
            )
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