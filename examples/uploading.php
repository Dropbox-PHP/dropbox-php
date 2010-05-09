<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '';
$consumerSecret = '';

include 'Dropbox/autoload.php';

session_start();
$dropbox = new Dropbox_API($consumerKey, $consumerSecret);

/* This script uploads itself */


if($dropbox->putFile(basename(__FILE__), __FILE__)) {
 
    echo "Success!";
    
} else {

    echo "Fail :(";

}
