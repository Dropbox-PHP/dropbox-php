<?php

/**
 * Dropbox OAuth
 * 
 * @package Dropbox 
 * @copyright Copyright (C) 2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://code.google.com/p/dropbox-php/wiki/License MIT
 */


/**
 * This class is used to sign all requests to dorpbox
 * It's mostly a convenience wrapper around the oath extension
 */
class Dropbox_OAuth {

    /**
     * BaseURI to dropbox api 
     * 
     * @var string
     */
    public $baseUri = 'http://api.dropbox.com/0/';
   
    /**
     * OAuth object
     *
     * @var OAuth
     */
    protected $oAuth;

    /**
     * Reference to session
     */
    public $_SESSION;

    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string$consumerSecret 
     */
    public function __construct($consumerKey, $consumerSecret) {

        $this->OAuth = new OAuth($consumerKey, $consumerSecret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $this->OAuth->enableDebug();
        $this->_SESSION =& $_SESSION; 

    }

    /**
     * Sets up authentication
     *
     * Note that this method will need to be called multiple times for the 
     * different authentication steps.
     *
     * The first time it will request request tokens. The second time it will redirect the
     * user to the permission page. Subsequent times will simply set up the 
     * oauth object.
     * 
     * @return void
     */
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

    /**
     * Fetches a secured oauth url and returns the response body. 
     * 
     * @param string $uri 
     * @param mixed $arguments 
     * @param string $method 
     * @param array $httpHeaders 
     * @return string 
     */
    public function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()) {

        if (strpos($uri,'http://')!==0 && strpos($uri,'https://')!==0) {
            $uri = $this->baseUri . $uri;
        } else {
           // $uri = 'http://localhost:61783/~evert2/code/dropbox/bla.php/' . $uri;
        }

        try { 
            $this->OAuth->fetch($uri, $arguments, $method, $httpHeaders);
            $result = $this->OAuth->getLastResponse();
            return $result;
        } catch (OAuthException $e) {

            $lastResponseInfo = $this->OAuth->getLastResponseInfo();
            switch($lastResponseInfo['http_code']) {

                case 404 : 
                    throw new Dropbox_Exception_NotFound('Resource at uri: ' . $uri . ' could not be found');
                default:
                    // rethrowing
                    echo $e->lastResponse . "<br />";
                    var_dump($lastResponseInfo);
                    throw $e;
            }

        }

    }

    /**
     * Requests the OAuth request token 
     * 
     * @return void 
     */
    public function request_token() {

        if (!isset($this->_SESSION['dropbox_oauth_token'])) {
            $tokens = $this->OAuth->getRequestToken($this->baseUri . 'oauth/request_token');
            $this->_SESSION['dropbox_state'] = 0; 
            $this->_SESSION['dropbox_token'] = $tokens['oauth_token'];
            $this->_SESSION['dropbox_secret'] = $tokens['oauth_token_secret'];
        }

    }

    /**
     * Redirects the user to the authorization url
     */
    public function authorize() {

        $this->request_token();
        $uri = $this->baseUri . 'oauth/authorize?oauth_token=' . $this->_SESSION['dropbox_token'];
        $this->_SESSION['dropbox_state'] = 1;
        header('Location: ' . $uri);
        exit();

    }

    /**
     * Fetches an access token
     */
    public function access_token() {

        $uri = $this->baseUri . 'oauth/access_token';
        
        $this->OAuth->setToken($this->_SESSION['dropbox_token'],$this->_SESSION['dropbox_secret']);
        $tokens = $this->OAuth->getAccessToken($uri);

        $this->_SESSION['dropbox_state'] = 2;
        $this->_SESSION['dropbox_token'] = $tokens['oauth_token'];
        $this->_SESSION['dropbox_secret'] = $tokens['oauth_token_secret'];

    }


}
