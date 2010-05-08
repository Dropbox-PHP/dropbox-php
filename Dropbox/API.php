<?php

class Dropbox_API {

    const ROOT_SANDBOX = 'sandbox';
    const ROOT_DROPBOX = 'dropbox';

    protected $auth;
    protected $root;

    public function __construct($consumerKey, $consumerSecret, $root = self::ROOT_SANDBOX) {

        $this->auth = new Dropbox_OAuth($consumerKey, $consumerSecret);
        $this->auth->setup();

        $this->root = $root;

    }

    public function account_info() {

        return $this->auth->fetch('account/info');

    }

    public function getFiles($path = '', $root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('files/' . $root . '/' . $path);

    }

    public function putFile($path, $file, $root = null) {

        if (is_null($root)) $root = $this->root;

        if (is_string($file)) {

            $file = fopen($file,'r');

        } elseif (!is_resource($file)) {

            throw new Dropbox_Exception('File must be a file-resource or a string');
            
        }

        $this->multipartFetch('files/' . $root . '/' . $path, array('file' => $file));

    }

    public function copy($from, $to, $root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('fileops/copy', array('from_path' => $from, 'to_path' => $to, 'root' => $root));

    }

    public function createFolder($path, $root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('fileops/create_folder', array('path' => $path, 'root' => $root));

    }

    public function delete($path, $root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('fileops/delete', array('path' => $path, 'root' => $root));

    }

    public function move($from, $to, $root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('fileops/move', array('from_path' => $from, 'to_path' => $to, 'root' => $root));

    }

    public function getLinks($root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('links/' . $root);

    }

    public function getMetaData($path, $list = false, $hash = null, $fileLimit = null, $root = null) {

        if (is_null($root)) $root = $this->root;

        $args = array(
            'list' => $list,
        );

        if (!is_null($hash)) $args['hash'] = $hash; 
        if (!is_null($fileLimit)) $args['file_limit'] = $hash; 

        return $this->auth->fetch('metadata/' . $root . '/' . $path); 

    } 

    public function thumbnails($path, $size = 'small', $root = null) {

        if (is_null($root)) $root = $this->root;
        return $this->auth->fetch('thumbnails/' . $root . '/' . $path,array('size' => $size));

    }

    protected function multipartFetch($uri,$arguments) {

        /* random string */
        $boundary = 'R50hrfBj5JYyfR3vF3wR96GPCC9Fd2q2pVMERvEaOE3D8LZTgLLbRpNwXek3';

        $headers = array(
            'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
        );

        $body = '';
        foreach($arguments as $argName => $argValue) {

            $body.="--" . $boundary . "\r\n";
            if (is_scalar($argValue)) {
                $body.="Content-Disposition: form-data; name=" . $argName . "\r\n";
                $body.="\r\n";
                $body.=$argValue;
            } elseif(is_resource($argValue)) {
                //$body.="Content-Disposition: form-data; name=" . $argName . "; filename=blob.data\r\n";
                //$body.="Content-type: application/octet-stream\r\n";
                $body.="Content-Disposition: form-data; name=" . $argName . "\r\n";
                $body.="\r\n";
                $body.=stream_get_contents($argValue);
            }
            $body.="\r\n";

        }
        $body.="--" . $boundary . "--";

        return $this->auth->fetch($uri, $body, 'POST', $headers);

    }



}
