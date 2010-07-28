<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '';
$consumerSecret = '';

include 'Dropbox/autoload.php';

$oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);

// If the PHP OAuth extension is not available, you can try
// PEAR's HTTP_OAUTH instead.
// $oauth = new Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);

$dropbox = new Dropbox_API($oauth);

header('Content-Type: text/plain');

$tokens = $dropbox->getToken('mrhandsome@example.org', 'secretpassword'); 

// Note that it's wise to save these tokens for re-use.
$oauth->setToken($tokens);

header('Content-Type: image/jpeg');
echo $dropbox->getFile('flower.jpg');

