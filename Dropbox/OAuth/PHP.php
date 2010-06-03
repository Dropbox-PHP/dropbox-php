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
 * This class is used to sign all requests to dropbox.
 *
 * This specific class uses the PHP OAuth extension
 */
class Dropbox_OAuth_PHP extends Dropbox_OAuth {

    /**
     * OAuth object
     *
     * @var OAuth
     */
    protected $oAuth;

    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    public function __construct($consumerKey, $consumerSecret) {

        if (!class_exists('OAuth')) 
            throw new Dropbox_Exception('The OAuth class could not be found! Did you install and enable the oauth extension?');

        $this->OAuth = new OAuth($consumerKey, $consumerSecret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $this->OAuth->enableDebug();

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

        $this->OAuth->setToken($this->oauth_token, $this->oauth_token_secret);
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
                    throw $e;
            }

        }

    }

    /**
     * Requests the OAuth request token.
     *
     * This method must return an array with 2 elements:
     *   * oauth_token
     *   * oauth_token_secret
     * 
     * @return array 
     */
    public function request_token() {
        
        try {

            return $this->OAuth->getRequestToken(self::URI_REQUEST_TOKEN);

        } catch (OAuthException $e) {

            print_r($this->OAuth);
            throw new Dropbox_Exception_RequestToken('We were unable to fetch request tokens. This likely means that your consumer key and/or secret are incorrect.',0,$e);

        }

    }


    /**
     * Requests the OAuth access tokens.
     *
     * This method requires the 'unauthorized' request tokens
     * and, if successful will return the authorized request tokens.
     * 
     * This method must return an array with 2 elements:
     *   * oauth_token
     *   * oauth_token_secret
     *
     * @return array
     */
    public function access_token($oauth_token, $oauth_token_secret) {

        $uri = self::URI_ACCESS_TOKEN;
        $this->OAuth->setToken($oauth_token,$oauth_token_secret);
        return $this->OAuth->getAccessToken($uri);

    }


}
