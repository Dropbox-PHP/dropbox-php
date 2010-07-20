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
 * This class is used to sign all requests to dropbox
 * 
 * This classes use the PEAR HTTP_OAuth package. Make sure this is installed.
 */
class Dropbox_OAuth_PEAR extends Dropbox_OAuth {

    /**
     * OAuth object
     *
     * @var OAuth
     */
    protected $oAuth;

    /**
     * OAuth consumer key
     * 
     * We need to keep this around for later. 
     * 
     * @var string 
     */
    protected $consumerKey;

    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    public function __construct($consumerKey, $consumerSecret) {

        if (!class_exists('HTTP_OAuth_Consumer')) {

            // We're going to try to load in manually
            include 'HTTP/OAuth/Consumer.php';

        }
        if (!class_exists('HTTP_OAuth_Consumer')) 
            throw new Dropbox_Exception('The HTTP_OAuth_Consumer class could not be found! Did you install the pear HTTP_OAUTH class?');

        $this->OAuth = new HTTP_OAuth_Consumer($consumerKey, $consumerSecret);
        $this->consumerKey = $consumerKey;

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

        $this->OAuth->setToken($this->oauth_token);
        $this->OAuth->setTokenSecret($this->oauth_token_secret);

        $consumerRequest = new HTTP_OAuth_Consumer_Request();
        $consumerRequest->setUrl($uri);
        $consumerRequest->setMethod($method);
        $consumerRequest->setSecrets($this->OAuth->getSecrets());
     
        $parameters = array(
            'oauth_consumer_key'     => $this->consumerKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $this->oauth_token,
        );


        if (is_array($arguments)) {
            $parameters = array_merge($parameters,$arguments);
        } elseif (is_string($arguments)) {
            $consumerRequest->setBody($arguments);
        }
        $consumerRequest->setParameters($parameters);


        if (count($httpHeaders)) {
            foreach($httpHeaders as $k=>$v) {
                $consumerRequest->setHeader($k, $v);
            }
        }

        $response = $consumerRequest->send();
        return $response->getBody();

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
        
        $this->OAuth->getRequestToken(self::URI_REQUEST_TOKEN);
        return array(
            'oauth_token' => $this->OAuth->getToken(),
            'oauth_token_secret' => $this->OAuth->getTokenSecret(),
        );

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

        $this->OAuth->setToken($oauth_token);
        $this->OAuth->setTokenSecret($oauth_token_secret);
        $this->OAuth->getAccessToken(self::URI_ACCESS_TOKEN);
        return array(
            'oauth_token' => $this->OAuth->getToken(),
            'oauth_token_secret' => $this->OAuth->getTokenSecret(),
        );

    }


}
