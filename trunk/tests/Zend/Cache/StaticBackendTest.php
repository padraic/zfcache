<?php
/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */

/**
 * Zend_Cache
 */
require_once 'Zend/Cache.php';
require_once 'Zend/Cache/Backend/Static.php';

/**
 * Zend_Log
 */
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

/**
 * Common tests for backends
 */
require_once 'CommonBackendTest.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package    Zend_Cache
 * @subpackage UnitTests
 */
class Zend_Cache_StaticBackendTest extends Zend_Cache_CommonBackendTest {

    protected $_instance;
    protected $_instance2;
    protected $_cache_dir;
    protected $_requestUriOld;
    protected $_innerCache;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct('Zend_Cache_Backend_Static', $data, $dataName);
    }

    public function setUp($notag = false)
    {
        $this->mkdir();
        $this->_cache_dir = $this->getTmpDir() . DIRECTORY_SEPARATOR;
        @mkdir($this->_cache_dir.'/tags');

        $this->_innerCache = Zend_Cache::factory('Core','File',
            array('automatic_serialization'=>true), array('cache_dir'=>$this->_cache_dir.'/tags')
        );
        $this->_instance = new Zend_Cache_Backend_Static(array(
            'public_dir' => $this->_cache_dir,
            'tag_cache' => $this->_innerCache
        ));

        $logger = new Zend_Log(new Zend_Log_Writer_Null());
        $this->_instance->setDirectives(array('logger' => $logger));

        $this->_requestUriOld =
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
        $_SERVER['REQUEST_URI'] = '/foo';

        $this->mkdir();
        $this->_instance->setDirectives(array('logging' => true));
        if ($notag) {
            $this->_instance->save('bar : data to cache', '/bar');
            $this->_instance->save('bar2 : data to cache', '/bar2');
            $this->_instance->save('bar3 : data to cache', '/bar3');
        } else {
            $this->_instance->save('bar : data to cache', '/bar', array('tag3', 'tag4'));
            $this->_instance->save('bar2 : data to cache', '/bar2', array('tag3', 'tag1'));
            $this->_instance->save('bar3 : data to cache', '/bar3', array('tag2', 'tag3'));
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->_instance);
        $_SERVER['REQUEST_URI'] = $this->_requestUriOld;
    }

    public function testConstructorCorrectCall()
    {
        $test = new Zend_Cache_Backend_Static(array());
    }

    public function testRemoveCorrectCall()
    {
        $this->assertTrue($this->_instance->remove('/bar'));
        $this->assertFalse($this->_instance->test('/bar'));
        $this->assertFalse($this->_instance->remove('/barbar'));
        $this->assertFalse($this->_instance->test('/barbar'));
    }

    public function testOptionsSetTagCache()
    {
        $test = new Zend_Cache_Backend_Static(array('tag_cache'=>$this->_innerCache));
        $this->assertTrue($test->getInnerCache() instanceof Zend_Cache_Core);
    }

    public function testSaveCorrectCall()
    {
        $res = $this->_instance->save('data to cache', '/foo', array('tag1', 'tag2'));
        $this->assertTrue($res);
    }

    public function testSaveWithNullLifeTime()
    {
        $this->_instance->setDirectives(array('lifetime' => null));
        $res = $this->_instance->save('data to cache', '/foo', array('tag1', 'tag2'));
        $this->assertTrue($res);
    }

    public function testSaveWithSpecificLifeTime()
    {
        $this->_instance->setDirectives(array('lifetime' => 3600));
        $res = $this->_instance->save('data to cache', '/foo', array('tag1', 'tag2'), 10);
        $this->assertTrue($res);
    }

    public function testTestWithAnExistingCacheId()
    {
        $res = $this->_instance->test('/bar');
        if (!$res) {
            $this->fail('test() return false');
        }
        return;
    }

    public function testTestWithANonExistingCacheId()
    {
        $this->assertFalse($this->_instance->test('/barbar'));
    }

    public function testTestWithAnExistingCacheIdAndANullLifeTime()
    {
        $this->_instance->setDirectives(array('lifetime' => null));
        $res = $this->_instance->test('/bar');
        if (!$res) {
            $this->fail('test() return false');
        }
        return;
    }

    public function testGetWithANonExistingCacheId()
    {
        $this->assertFalse($this->_instance->load('/barbar'));
    }

    public function testGetWithAnExistingCacheId()
    {
        $this->assertEquals('bar : data to cache', $this->_instance->load('/bar'));
    }

    public function testGetWithAnExistingCacheIdAndUTFCharacters()
    {
        $data = '"""""' . "'" . '\n' . 'ééééé';
        $this->_instance->save($data, '/foo');
        $this->assertEquals($data, $this->_instance->load('/foo'));
    }

    public function testCleanModeMatchingTags()
    {
        $this->assertTrue($this->_instance->clean('matchingTag', array('tag3')));
        $this->assertFalse($this->_instance->test('/bar'));
        $this->assertFalse($this->_instance->test('/bar2'));
    }

    public function testCleanModeMatchingTags2()
    {
        $this->assertTrue($this->_instance->clean('matchingTag', array('tag3', 'tag4')));
        $this->assertFalse($this->_instance->test('/bar'));
        $this->assertTrue($this->_instance->test('/bar2') > 999999);
    }


    // Deferred tests

    public function testCleanModeNotMatchingTags()
    {
        $this->markTestSkipped('Deferred Test');
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag3')));
        $this->assertTrue($this->_instance->test('/bar') > 999999);
        $this->assertTrue($this->_instance->test('/bar2') > 999999);
    }

    public function testCleanModeNotMatchingTags2()
    {
        $this->markTestSkipped('Deferred Test');
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag4')));
        $this->assertTrue($this->_instance->test('/bar') > 999999);
        $this->assertFalse($this->_instance->test('/bar2'));
    }

    public function testCleanModeNotMatchingTags3()
    {
        $this->markTestSkipped('Deferred Test');
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag4', 'tag1')));
        $this->assertTrue($this->_instance->test('/bar') > 999999);
        $this->assertTrue($this->_instance->test('/bar2') > 999999);
        $this->assertFalse($this->_instance->test('/bar3'));
    }

    public function testCleanModeAll()
    {
        $this->markTestSkipped('Deferred Test');
        $this->assertTrue($this->_instance->clean('all'));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->test('bar2'));
    }


    // Irrelevant Tests (from common tests)

    public function testGetWithAnExpiredCacheId()
    {
        $this->markTestSkipped('Irrelevant Test');
    }

    public function testCleanModeOld()
    {
        $this->markTestSkipped('Irrelevant Test');
    }

}

