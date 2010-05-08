<?php

/**
 * Dropbox OAuth 
 * 
 * @package Dropbox 
 * @copyright Copyright (C) 2007-2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Dropbox_OAuth {

    public $baseUri = 'http://api.dropbox.com/0/';
    
    protected $oAuth;

    public $_SESSION;

    public function __construct($consumerKey, $consumerSecret) {

        $this->OAuth = new OAuth($consumerKey, $consumerSecret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $this->OAuth->enableDebug();
        $this->_SESSION =& $_SESSION; 

    }

    public function setup() {

        if (!isset($this->_SESSION['dropbox_state'])) $this->_SESSION['dropbox_state'] = 0;

        switch($this->_SESSION['dropbox_state']) {

            case 0:
                $this->authorize();
                break;
            case 1:
                $this->access_token();
                //Note: the lack of a break statement is intentional
            case 2:
                $this->OAuth->setToken($this->_SESSION['dropbox_token'],$this->_SESSION['dropbox_secret']);
                break;

        }

    }

    public function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()) {

        try {
            //$this->OAuth->fetch('http://localhost:61783/~evert2/code/dropbox/bla.php/' . $uri, $arguments, $method, $httpHeaders);
            $this->OAuth->fetch($this->baseUri . $uri, $arguments, $method, $httpHeaders);
            $result = $this->OAuth->getLastResponse();
            return json_decode($result);
        } catch (OAuthException $e) {
            print_r($this->OAuth->getLastResponse());
            die();
        }

    }

    public function request_token() {

        if (!isset($this->_SESSION['dropbox_oauth_token'])) {
            $tokens = $this->OAuth->getRequestToken($this->baseUri . 'oauth/request_token');
            $this->_SESSION['dropbox_state'] = 0; 
            $this->_SESSION['dropbox_token'] = $tokens['oauth_token'];
            $this->_SESSION['dropbox_secret'] = $tokens['oauth_token_secret'];
        }

    }

    public function authorize() {

        $this->request_token();
        $uri = $this->baseUri . 'oauth/authorize?oauth_token=' . $this->_SESSION['dropbox_token'];
        $this->_SESSION['dropbox_state'] = 1;
        header('Location: ' . $uri);
        exit();

    }

    public function access_token() {

        $uri = $this->baseUri . 'oauth/access_token';
        
        $this->OAuth->setToken($this->_SESSION['dropbox_token'],$this->_SESSION['dropbox_secret']);
        $tokens = $this->OAuth->getAccessToken($uri);

        $this->_SESSION['dropbox_state'] = 2;
        $this->_SESSION['dropbox_token'] = $tokens['oauth_token'];
        $this->_SESSION['dropbox_secret'] = $tokens['oauth_token_secret'];

    }


}
