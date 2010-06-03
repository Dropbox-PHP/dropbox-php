<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '';
$consumerSecret = '';

include 'Dropbox/autoload.php';

session_start();
$oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);

// If the PHP OAuth extension is not available, you can try
// PEAR's HTTP_OAUTH instead.
// $oauth = new Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);

$dropbox = new Dropbox_API($oauth);

/* This script uploads itself */
if($dropbox->putFile(basename(__FILE__), __FILE__)) {
 
    echo "Success!";
    
} else {

    echo "Fail :(";

}
