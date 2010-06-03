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
 * This class is an abstract OAuth class.
 *
 * It must be extended by classes who wish to provide OAuth functionality
 * using different libraries.
 */
abstract class Dropbox_OAuth {

    /**
     * After a user has authorized access, dropbox can redirect the user back
     * to this url.
     * 
     * @var string
     */
    public $authorizeCallbackUrl = null; 
   
    /**
     * The user has not yet authorized access
     */
    const STATE_UNAUTHORIZED = 0;

    /**
     * The user is redirect to authorize dropbox access
     */
    const STATE_USERAUTHORIZING = 1;
    const STATE_AUTHORIZED = 2;

    /**
     * The currente authentication state 
     * 
     * @var int 
     */
    protected $currentState = self::STATE_UNAUTHORIZED;

    /**
     * Uri used to fetch request tokens 
     * 
     * @var string
     */
    const URI_REQUEST_TOKEN = 'http://api.dropbox.com/0/oauth/request_token';

    /**
     * Uri used to redirect the user to for authorization.
     * 
     * @var string
     */
    const URI_AUTHORIZE = 'http://api.dropbox.com/0/oauth/authorize';

    /**
     * Uri used to 
     * 
     * @var string
     */
    const URI_ACCESS_TOKEN = 'http://api.dropbox.com/0/oauth/access_token';

    /**
     * An OAuth request token. 
     * 
     * @var string 
     */
    protected $oauth_token = null;

    /**
     * OAuth token secret 
     * 
     * @var string 
     */
    protected $oauth_token_secret = null;


    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    abstract public function __construct($consumerKey, $consumerSecret);

    public function saveState() {

        $_SESSION['dropbox'] = array(
            'oauth_token' => $this->oauth_token,
            'oauth_token_secret' => $this->oauth_token_secret,
            'state' => $this->currentState,
        );

    }

    public function loadState() {

        if (isset($_SESSION['dropbox'])) {
            $this->oauth_token = isset($_SESSION['dropbox']['oauth_token'])?$_SESSION['dropbox']['oauth_token']:null;
            $this->oauth_token_secret = isset($_SESSION['dropbox']['oauth_token_secret'])?$_SESSION['dropbox']['oauth_token_secret']:null;
            $this->currentState = isset($_SESSION['dropbox']['state'])?$_SESSION['dropbox']['state']:null;
        }

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
     * @param string $redirectUrl 
     * @return void
     */
    public function setup() {

        $this->loadState();
        switch($this->currentState) {

            case self::STATE_UNAUTHORIZED :
                $tokens = $this->request_token();
                $this->oauth_token = $tokens['oauth_token'];
                $this->oauth_token_secret = $tokens['oauth_token_secret'];

                // Building the redirect uri
                $uri = self::URI_AUTHORIZE . '?oauth_token=' . $this->oauth_token;
                if ($this->authorizeCallbackUrl) $uri.='&oauth_callback=' . $this->authorizeCallbackUrl;
                $this->currentState = self::STATE_USERAUTHORIZING;

                $this->saveState();
                header('Location: ' . $uri);
                exit();
                break;
            case self::STATE_USERAUTHORIZING :
                $tokens = $this->access_token($this->oauth_token, $this->oauth_token_secret);
                $this->oauth_token = $tokens['oauth_token'];
                $this->oauth_token_secret = $tokens['oauth_token_secret'];
                $this->currentState = self::STATE_AUTHORIZED;
                $this->saveState();

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
    public abstract function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()); 

    /**
     * Requests the OAuth request token.
     *
     * This method must return an array with 2 elements:
     *   * oauth_token
     *   * oauth_token_secret
     * 
     * @return array 
     */
    abstract public function request_token(); 

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
    abstract public function access_token($oauth_token, $oauth_token_secret); 

}
