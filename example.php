<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '';
$consumerSecret = '';

include 'Dropbox/OAuth.php';
include 'Dropbox/API.php';

session_start();

$dropbox = new Dropbox_API($consumerKey, $consumerSecret);

header('Content-Type: text/plain');

print_r($dropbox->account_info());

