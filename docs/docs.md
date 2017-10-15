Dropbox-PHP API Documentation
=============

* [Overview](#Overview)
  * [Creating the object](#CreatingObject)
  * [Authentication](#Authentication)
    * [OAuth workflow](#OAuthWorkflow)
  * [Roots](#Roots)
  * [The API Methods](#Methods)
    * [getAccountInfo](#getAccountInfo)
    * [getFile](#getFile)
    * [putFile](#putFile)
    * [copy](#copy)
    * [createFolder](#createFolder)
    * [delete](#delete)
    * [move](#move)
    * [getMetaData](#getMetaData)
    * [getThumbnail](#getThumbnail)
    * [More Examples](#MoreExamples)
    
  

<h2 id="Overview">Overview</h2>
The Dropbox API class is the main class to interact with the dropbox API.
It has all the convenience methods to access and modify dropbox information.

<h2 id="CreatingObject">Creating the object</h2>

The constructor takes at least 2 arguments:
* $consumerKey
* $consumerSecret

You can find these keys on the Dropbox developer site, under [My applications](https://www.dropbox.com/developers/apps).

example:

    <?php
    
    /* Please supply your own consumer key and consumer secret */
    $consumerKey = '';
    $consumerSecret = '';

    include 'Dropbox/autoload.php';
    
    session_start();
    $oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);
    
    // If the PHP OAuth extension is not available, you can try
    // PEAR's HTTP_OAUTH instead.
    // $oauth = new Dropbox_OAuth_PEAR($consumerKey,$consumerSecret);
    
    $dropbox = new Dropbox_API($oauth);
    ?>

<h2 id="Authentication">Authentication</h2>

<h3 id="OAuthWorkflow">OAuth workflow</h3>

    $oauth = new Dropbox_OAuth_PHP($consumerKey,$consumerSecret);
    $dropbox = new Dropbox_API($oauth);
    
    // For convenience, definitely not required
    header('Content-Type: text/plain');
    
    // We need to start a session
    session_start();
    
    // There are multiple steps in this workflow, we keep a 'state number' here
    if (isset($_SESSION['state'])) {
        $state = $_SESSION['state'];
    } else {
        $state = 1;
    }
    
    switch($state) {
    
        /* In this phase we grab the initial request tokens
           and redirect the user to the 'authorize' page hosted
           on dropbox */
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
    
        /* In this phase, the user just came back from authorizing
           and we're going to fetch the real access tokens */
        case 2 :
            echo "Step 3: Acquiring access tokens\n";
            $oauth->setToken($_SESSION['oauth_tokens']);
            $tokens = $oauth->getAccessToken();
            print_r($tokens);
            $_SESSION['state'] = 3;
            $_SESSION['oauth_tokens'] = $tokens;
            // There is no break here, intentional
    
        /* This part gets called if the authentication process
           already succeeded. We can use our stored tokens and the api 
           should work. Store these tokens somewhere, like a database */
        case 3 :
            echo "The user is authenticated\n";
            echo "You should really save the oauth tokens somewhere, so the first steps will no longer be needed\n";
            print_r($_SESSION['oauth_tokens']);
            $oauth->setToken($_SESSION['oauth_tokens']);
            break;
        }
    
    }


<h2 id="Roots">Roots</h2>

By default Dropbox works with 2 different 'roots', the 
* Sandbox, which is 1 specific directory assigned for your application
* Dropbox, which is the users' entire dropbox directory.

Before 'sandbox' was the default root, but sandbox is disabled for new applications so now
'dropbox' is the default. In most cases you don't have to worry about this.


    // If you do need the sandbox, specify it as the 2nd argument
    $dropbox = new Dropbox_API($oauth, 'sandbox');

<h2 id="Methods">The API methods</h2>

<h3 id="getAccountInfo">getAccountInfo</h3>

Using the getAccountInfo you can grab a users account information, such as their quota.
Simply call $dropbox->getAccountInfo();

<h3 id="getFile">getFile</h3>

Using getFile you can download a file. At the moment the function returns the entire file's body as a string. This might be fixed in the future once PHP's OAuth extension no longer requires this.

    $dropbox->getFile('filename');

You can override the default root by specifying it as a second argument:

    $dropbox->getFile('filename','dropbox');

<h3 id="putFile">putFile</h3>

Using putFile you can upload a file. At the moment the function will store the entire file temporarily as a string. This might be fixed in the future once PHP's OAuth extension no longer requires this.

You can specify the file as a local path, or as an open file stream.

Example 1:

    $dropbox->putFile('newPath.txt','/local/path/tofile');

Example 2:

    $h = fopen('/local/path/to/file','r');
    $dropbox->putFile('newPath.txt',$h);

You can override the default root by specifying it as a third argument:

    $dropbox->putFile('newPath.txt','/local/path/tofile','dropbox');

<h3 id="copy">copy</h3>

Copy makes an exact copy of a file or directory. 

Example:

    $dropbox->copy('oldfile.txt','newfile.txt');

You can override the default root by specifying it as a third argument:

    $dropbox->copy('oldfile.txt','newfile.txt','sandbox');

<h3 id="">createFolder</h2>

createFolder creates a new, empty, folder.

Example:

    $dropbox->createFolder('new folder');

You can override the default root by specifying it as a second argument:

    $dropbox->createFolder('new folder','sandbox');

<h3 id="delete">delete</h3>

delete deletes a file or directory (and all it's contents).

Example:

    $dropbox->delete('myfolder');

You can override the default root by specifying it as a second argument:

    $dropbox->delete('myfolder','sandbox');

<h3 id="move">move</h3>

Move moves a file or directory to a new location:

Example:

    $dropbox->move('oldfile.txt','newfile.txt');

You can override the default root by specifying it as a third argument:

    $dropbox->move('oldfile.txt','newfile.txt','sandbox');


<h3 id="getMetaData">getMetaData</h3>

getMetadata is used to retrieve information about files, or about the contents of a directory. 

Example 1:

    $info = $dropbox->getMetaData('directory');
    print_r($info);

The second parameter specifies what you want to retrieve. If you're fetching info about a 
directory, and it is set to true, the directories contents will be returned. If it's set to 'false', only the directories' information will be returned:

Example 2:

    // Doesn't return directory contents
    $info = $dropbox->getMetaData('directory',false);
    print_r($info);

<h3 id="getThumbnail">getThumbnail</h3>

The getThumbnail method works like getFile, except it can be used to retrieve an image's thumbnail.

The first argument specifies the filename, the second specifies the size (small, medium, large).

    header('Content-Type: image/jpeg');
    echo $dropbox->getThumbnail('image.jpg','large');

The optional third argument can be used to change the default root:

    header('Content-Type: image/jpeg');
    echo $dropbox->getThumbnail('image.jpg','small','dropbox');

<h2 id="MoreExamples">More examples</h2>

The unit test in the package is quite complete, everything you need to know should be in there. You can also [browse the source](https://github.com/Dropbox-PHP/dropbox-php).