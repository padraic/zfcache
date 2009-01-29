<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zend_Controller_Action_Exception
 */
require_once 'Zend/Controller/Action/Exception.php';

/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Cache/Manager.php';

class Zend_Controller_Action_Helper_Cache extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Local Cache Manager object used by Helper
     *
     * @var Zend_Cache_Manager
     */
    protected $_manager = null;

    public function direct()
    {
    }

    public function preDispatch()
    {
    }

    public function postDispatch()
    {
    }

    /**
     * Set an instance of the Cache Manager for this helper
     *
     * @param Zend_Cache_Manager $manager
     * @return void
     */
    public function setManager(Zend_Cache_Manager $manager)
    {
        $this->_manager = $manager;
    }

    /**
     * Get the Cache Manager instance or instantiate the object is not exists
     *
     * @return Zend_Cache_Manager
     */
    public function getManager()
    {
        if (is_null($this->_manager)) {
            $this->_manager = new Zend_Cache_Manager;
        }
        return $this->_manager;
    }

    /**
     * Proxy non-matched methods back to Zend_Cache_Manager where appropriate
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->getManager(), $method)) {
            return call_user_func_array(array($this->getManager(), $method), $args);
        }
    }

}