<?php
//phpinfo();
/*

BLOCKSTRAP FAUCETS
Using - http://blockstrap.com

*/

error_reporting(-1);
$php_base = dirname(__FILE__);
$template = file_get_contents($php_base.'/html/index.html');
$options = json_decode(file_get_contents($php_base.'/json/index.json'), true);

$ini = parse_ini_file(dirname($php_base).'/config.ini', true);
$addresses = $ini['addresses'];
$chains = array();
$index = 0;

foreach($options['chains'] as $key => $chain)
{
    if(isset($addresses[$chain['chain']]) && $addresses[$chain['chain']])
    {
        $chains[$index] = $chain;
        $chains[$index]['address'] = $addresses[$chain['chain']];
        $index++;
    }
}
$options['chains'] = $chains;
$faucet_count = count($chains);
if($faucet_count == 1) $options['col']['css'] = 12;
else if($faucet_count == 2) $options['col']['css'] = 6;
else if($faucet_count == 3 || $faucet_count == 6) $options['col']['css'] = 4;
else if($faucet_count == 4 || $faucet_count == 8) $options['col']['css'] = 3;

include_once($php_base.'/php/faucets.php');
include_once($php_base.'/php/mustache.php');

$faucets = new bs_faucets();

$mustache = new MustachePHP();
$html = $mustache->render($template, $options);
    
echo $html;