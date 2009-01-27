<?php

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

require_once 'Zend/Cache.php';
require_once 'Zend/Cache/Manager.php';
require_once 'Zend/Config.php';

class Zend_Cache_ManagerTest extends PHPUnit_Framework_TestCase
{

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->_root = dirname(__FILE__);
        date_default_timezone_set('UTC');
        parent::__construct($name, $data, $dataName);
    }

    public function setUp() 
    {
        $this->mkdir();
        $this->_cache = Zend_Cache::factory(
            'Core', 'File',
            array('automatic_serialization'=>true),
            array('cache_dir'=>$this->getTmpDir() . DIRECTORY_SEPARATOR)
        );
    }

    public function tearDown() 
    {
        $this->rmdir();
        $this->_cache = null;
    }

    public function testSetsCacheObject()
    {
        $manager = new Zend_Cache_Manager;
        $manager->setCache('cache1', $this->_cache);
        $this->assertTrue($manager->getCache('cache1') instanceof Zend_Cache_Core);
    }

    // Helper Methods

    public function mkdir()
    {
        @mkdir($this->getTmpDir());
    }

    public function rmdir()
    {
        $tmpDir = $this->getTmpDir(false);
        foreach (glob("$tmpDir*") as $dirname) {
            @rmdir($dirname);
        }
    }

    public function getTmpDir($date = true)
    {
        $suffix = '';
        if ($date) {
            $suffix = date('mdyHis');
        }
        if (is_writeable($this->_root)) {
            return $this->_root . DIRECTORY_SEPARATOR . 'zend_cache_tmp_dir_' . $suffix;
        } else {
            if (getenv('TMPDIR')){
                return getenv('TMPDIR') . DIRECTORY_SEPARATOR . 'zend_cache_tmp_dir_' . $suffix;
            } else {
                die("no writable tmpdir found");
            }
        }
    }

}