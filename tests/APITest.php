<?php
ini_set('memory_limit', -1);
class APITest extends PHPUnit_Framework_TestCase
{
    protected $_largeFilename;

    protected $_largeData;

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

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unlink($this->_largeFilename);
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

        $this->_largeFilename = tempnam(sys_get_temp_dir(), '/large-temp.txt');
        $data = $this->_getLargeData();
        file_put_contents($this->_largeFilename, $data);
        $response = $this->dropbox->putFile('Dropbox-php_tests/alpha-large.txt', $this->_largeFilename);
        $this->assertTrue($response, 'putVeryLargeFile should return true');
    }

    /**
     * @depends testPutVeryLargeFile
     */
    public function testGetVeryLargeFile()
    {
        $data = $this->_getLargeData();
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

    /**
     * @return string
     */
    protected function _getLargeData()
    {
        if (null == $this->_largeData) {
            $kb = 1024;
            $mb = 1024 * $kb;
            $this->_largeData = str_repeat('0', 50 * $mb);
        }
        return $this->_largeData;
    }
}
