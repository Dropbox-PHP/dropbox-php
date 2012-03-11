<?php

class APITest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $filename = dirname(__FILE__) . '/oauth.cache';
        if (!file_exists($filename)) {
            die("Run ./setup first to establish an oauth token!\n\n");
        }
        
        $setup = unserialize(file_get_contents($filename));
        
        require_once dirname(__FILE__) . '/../src/Dropbox/autoload.php';
        $this->oauthClass = $setup['class'];
        $oauth = new $this->oauthClass($setup['consumer']['key'], $setup['consumer']['secret']);
        $oauth->setToken($setup['tokens']);
        
        $this->dropbox = new Dropbox_API($oauth);
    }
    
    public function testGetAccountInfo()
    {
        $response = $this->dropbox->getAccountInfo();
        
        $this->assertTrue(isset($response['uid']), 'getAccountInfo should return a "uid" key');
    }
    
    /**
     * @depends testGetAccountInfo
     */
    public function testCreateFolderGetMetaData()
    {
        try {
            $response = $this->dropbox->getMetaData('Dropbox-php_tests');
        } catch (Dropbox_Exception_NotFound $e) {
            $response = $this->dropbox->createFolder('Dropbox-php_tests');
            $this->assertTrue(isset($response['is_dir']), 'createFolder should return an "is_dir" key');
            $this->assertTrue($response['is_dir'], '"is_dir" key of createFolder should be true');
            
            $response = $this->dropbox->getMetaData('Dropbox-php_tests');
        }
        
        $this->assertTrue(isset($response['contents']), 'getMetaData should return a "contents" key');
        $this->assertTrue(is_array($response['contents']), '"contents" key of getMetaData should return an array');
    }
    
    /**
     * @depends testCreateFolderGetMetaData
     */
    public function testPutFile()
    {
        if ($this->oauthClass == 'Dropbox_OAuth_PHP') {
            $this->markTestSkipped('Known issues prevent the Dropbox_API::putFile method from working with the oauth extension');
        }
        
        $filename = dirname(__FILE__) . '/temp.txt';
        file_put_contents($filename, 'abc');
        $response = $this->dropbox->putFile('Dropbox-php_tests/alpha.txt', $filename);
        $this->assertTrue($response, 'putFile should return true');
    }
    /**
     * @depends testCreateFolderGetMetaData
     */
    public function testPutVeryLargeFile()
    {
        if ($this->oauthClass == 'Dropbox_OAuth_PHP') {
            $this->markTestSkipped('Known issues prevent the Dropbox_API::putFile method from working with the oauth extension');
        }
        
        $filename = dirname(__FILE__) . '/large-temp.txt';
        $kb = 1024;
        $mb = 1024 * $kb;
        $data = str_repeat('0', 100 * $mb);
        file_put_contents($filename, $data);
        $response = $this->dropbox->putFile('Dropbox-php_tests/alpha-large.txt', $filename);
        $this->assertTrue($response, 'putVeryLargeFile should return true');
    }
    
    /**
     * @depends testPutVeryLargeFile
     */
    public function testGetVeryLargeFile()
    {
        $kb = 1024;
        $mb = 1024 * $kb;
        $data = str_repeat('0', 100 * $mb);
        $response = $this->dropbox->getFile('Dropbox-php_tests/alpha-large.txt');
        $this->assertEquals($data, $response, 'getVeryLargeFile should return file contents');
    }
    
    /**
     * @depends testPutFile
     */
    public function testGetFile()
    {
        $response = $this->dropbox->getFile('Dropbox-php_tests/alpha.txt');
        $this->assertEquals('abc', $response, 'getFile should return file contents');
    }
    
    /**
     * @depends testGetFile
     */
    public function testCopy()
    {
        $response = $this->dropbox->copy('Dropbox-php_tests/alpha.txt', 'Dropbox-php_tests/bravo.txt');
        $this->assertTrue(isset($response['is_dir']), 'copy should return an "is_dir" key');
        $this->assertFalse($response['is_dir'], '"is_dir" key of copy should be false');
    }
    
    /**
     * @depends testCopy
     */
    public function testMove()
    {
        $response = $this->dropbox->move('Dropbox-php_tests/bravo.txt', 'Dropbox-php_tests/charlie.txt');
        $this->assertTrue(isset($response['is_dir']), 'move should return an "is_dir" key');
        $this->assertFalse($response['is_dir'], '"is_dir" key of move should be false');
    }
    
    /**
     * @depends testMove
     */
    public function testDelete()
    {
        $response = $this->dropbox->delete('Dropbox-php_tests');
        $this->assertTrue(isset($response->is_deleted), 'delete should return an "is_deleted" object');
        $this->assertTrue($response->is_deleted, '"is_deleted" object of delete should be true');
    }
}
