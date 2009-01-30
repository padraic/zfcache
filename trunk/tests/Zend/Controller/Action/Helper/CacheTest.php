<?php

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Controller_Action_Helper_CacheTest::main");
}

require_once dirname(__FILE__) . '/../../../../TestHelper.php';
require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'Zend/Controller/Action/Helper/Cache.php';
require_once 'Zend/Controller/Action/HelperBroker.php';
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Request/Http.php';
require_once 'Zend/Controller/Response/Http.php';

/**
 * Test class for Zend_Controller_Action_Helper_Cache
 */
class Zend_Controller_Action_Helper_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Controller_Action_Helper_CacheTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp()
    {
        $this->front = Zend_Controller_Front::getInstance();
        $this->front->resetInstance();
        $this->request = new Zend_Controller_Request_Http();
        $this->request->setModuleName('foo')
                ->setControllerName('bar')
                ->setActionName('baz');
        $this->front->setRequest($this->request);
    }

    public function tearDown()
    {
    }

    public function testGetterInstantiatesManager() 
    {
        $helper = new Zend_Controller_Action_Helper_Cache;
        $this->assertTrue($helper->getManager() instanceof Zend_Cache_Manager);
    }

    public function testMethodsProxyToManager() 
    {
        $helper = new Zend_Controller_Action_Helper_Cache;
        $this->assertTrue($helper->hasCache('page'));
    }

    public function testCacheableActionsStoredAtInit() 
    {
        $helper = new Zend_Controller_Action_Helper_Cache;
        $helper->setFrontController($this->front);
        $helper->direct(array('action1'));
        $cacheable = $helper->getCacheableActions();
        $this->assertEquals('action1', $cacheable['bar'][0]);
    }

    public function testCacheableActionTagsStoredAtInit() 
    {
        $helper = new Zend_Controller_Action_Helper_Cache;
        $helper->setFrontController($this->front);
        $helper->direct(array('action1'), array('tag1','tag2'));
        $cacheable = $helper->getCacheableTags();
        $this->assertSame(array('tag1','tag2'), $cacheable['bar']['action1']);
    }

    public function testCacheableActionsNeverDuplicated() 
    {
        $helper = new Zend_Controller_Action_Helper_Cache;
        $helper->setFrontController($this->front);
        $helper->direct(array('action1','action1'));
        $cacheable = $helper->getCacheableActions();
        $this->assertEquals('action1', $cacheable['bar'][0]);
    }

    public function testCacheableActionTagsNeverDuplicated() 
    {
        $helper = new Zend_Controller_Action_Helper_Cache;
        $helper->setFrontController($this->front);
        $helper->direct(array('action1'), array('tag1','tag1','tag2','tag2'));
        $cacheable = $helper->getCacheableTags();
        $this->assertSame(array('tag1','tag2'), $cacheable['bar']['action1']);
    }

}

if (PHPUnit_MAIN_METHOD == "Zend_Controller_Action_Helper_CacheTest::main") {
    Zend_Controller_Action_Helper_CacheTest::main();
}
