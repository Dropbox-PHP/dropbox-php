<?php

/* Please supply your own consumer key and consumer secret */
$consumerKey = '';
$consumerSecret = '';

include 'Dropbox/autoload.php';

/* We need a session to store the token, use whatever you want though to store these */
session_start();

$oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);

// If the PHP OAuth extension is not available, you can try
// PEAR's HTTP_OAUTH instead.
// $oauth = new Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);

header('Content-Type: text/plain');

if (isset($_SESSION['state'])) {
    $state = $_SESSION['state'];
} else {
    $state = 1;
}

switch($state) {

    case 1 :
        echo "Step 1: Acquire request tokens\n";
        $tokens = $oauth->getRequestToken();
        print_r($tokens);

        // Note that if you want the user to automatically redirect back, you can
        // add the 'callback' argument to getAuthorizeUrl.
        echo "Step 2: You must now redirect the user to:\n";
        echo $oauth->getAuthorizeUrl() . "\n";
        $_SESSION['state'] = 2;
        $_SESSION['oauth_tokens'] = $tokens;
        die();
    case 2 :
        echo "Step 3: Acquiring access tokens\n";
        $oauth->setToken($_SESSION['oauth_tokens']);
        $tokens = $oauth->getAccessToken();
        print_r($tokens);
        $_SESSION['state'] = 3;
        $_SESSION['oauth_tokens'] = $tokens;
    case 3 :
        echo "The user is authenticated\n";
        echo "You should really save the oauth tokens somewhere, so the first steps will no longer be needed\n";
        print_r($_SESSION['oauth_tokens']);
        $oauth->setToken($_SESSION['oauth_tokens']);
        break;
}


$dropbox = new Dropbox_API($oauth);

echo "Account info:\n";

print_r($dropbox->getAccountInfo());
