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

    public function testLazyLoadsDefaultPageCache() 
    {
        $manager = new Zend_Cache_Manager;
        $this->assertTrue($manager->getCache('page') instanceof Zend_Cache_Frontend_Output);
    }

    public function testCanOverrideCacheFrontendNameConfiguration() 
    {
        $manager = new Zend_Cache_Manager;
        $manager->setTemplateConfig('page', array(
            'frontend' => array(
                'name'=> 'Page'
            )    
        ));
        $this->assertTrue($manager->getCache('page') instanceof Zend_Cache_Frontend_Page);
    }

    public function testCanOverrideCacheBackendendNameConfiguration() 
    {
        $manager = new Zend_Cache_Manager;
        $manager->setTemplateConfig('page', array(
            'backend' => array(
                'name'=> 'File'
            )    
        ));
        $this->assertTrue($manager->getCache('page')->getBackend() instanceof Zend_Cache_Backend_File);
    }

    public function testCanOverrideCacheFrontendOptionsConfiguration() 
    {
        $manager = new Zend_Cache_Manager;
        $manager->setTemplateConfig('page', array(
            'frontend' => array(
                'options'=> array(
                    'lifetime' => 9999
                )
            )    
        ));
        $this->assertEquals(9999, $manager->getCache('page')->getOption('lifetime'));
    }

    public function testCanOverrideCacheBackendOptionsConfiguration() 
    {
        $manager = new Zend_Cache_Manager;
        $manager->setTemplateConfig('page', array(
            'backend' => array(
                'options'=> array(
                    'sub_dir' => './cacheDir'
                )
            )    
        ));
        $this->assertEquals('./cacheDir', $manager->getCache('page')->getBackend()->getOption('sub_dir'));
    }

    public function testSetsConfigTemplate() 
    {
        $manager = new Zend_Cache_Manager;
        $config = array(
            'frontend' => array(
                'name' => 'Core',
                'options' => array(
                    'automatic_serialization' => true
                )
            ),
            'backend' => array(
                'name' => 'File',
                'options' => array(
                    'cache_dir' => '../cache'
                )
            )
        );
        $manager->setCacheTemplate('myCache', $config);
        $this->assertSame($config, $manager->getCacheTemplate('myCache'));
    }

    public function testConfigTemplatesDetectedAsAvailableCaches() 
    {
        $manager = new Zend_Cache_Manager;
        $this->assertTrue($manager->hasCache('page'));
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