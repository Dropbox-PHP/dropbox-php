<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '';
$consumerSecret = '';

include 'Dropbox/autoload.php';

session_start();
$dropbox = new Dropbox_API($consumerKey, $consumerSecret);

header('Content-Type: image/jpeg');
echo $dropbox->getFile('flower.jpg');

