<?php

$consumerKey = 'noissdi62q3eb1g';
$consumerSecret = '8ufccwnxrfibhkd';

include 'Dropbox/OAuth.php';
include 'Dropbox/API.php';

session_start();

$dropbox = new Dropbox_API($consumerKey, $consumerSecret);

header('Content-Type: text/plain');

print_r($dropbox->account_info());

