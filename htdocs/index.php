<?php

/*

BLOCKSTRAP FAUCETS
Using - http://blockstrap.com

*/

error_reporting(-1);
$php_base = dirname(__FILE__);
$template = file_get_contents($php_base.'/html/index.html');
$options = json_decode(file_get_contents($php_base.'/json/index.json'), true);

include_once($php_base.'/php/faucets.php');
include_once($php_base.'/php/mustache.php');

$faucets = new bs_faucets();
$mustache = new MustachePHP();
$html = $mustache->render($template, $options);
    
echo $html;