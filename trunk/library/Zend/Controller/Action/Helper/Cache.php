<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zend_Controller_Action_Exception
 */
require_once 'Zend/Controller/Action/Exception.php';

/**
 * @see Zend_Cache_Manager
 */
require_once 'Zend/Cache/Manager.php';

class Zend_Controller_Action_Helper_Cache extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Local Cache Manager object used by Helper
     *
     * @var Zend_Cache_Manager
     */
    protected $_manager = null;

    /**
     * Indexed map of Action to attempt Page caching on
     *
     * @var array
     */
    protected $_caching = array();

    protected $_tags = array();

    public function direct(array $actions, array $tags = array())
    {
        $controller = $this->getRequest()->getControllerName();
        $actions = array_unique($actions);
        if (!isset($this->_caching[$controller])) {
            $this->_caching[$controller] = array();
        }
        if (!empty($tags)) {
            $tags = array_unique($tags);
            if (!isset($this->_tags[$controller])) {
                $this->_tags[$controller] = array();
            }
        }
        foreach ($actions as $action) {
            $this->_caching[$controller][] = $action;
            if (!empty($tags)) {
                $this->_tags[$controller][$action] = array();
                foreach ($tags as $tag) {
                    $this->_tags[$controller][$action][] = $tag;
                }
            }
        }
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
     * Return a list of actions for the current Controller marked for caching
     *
     * @return array
     */
    public function getCacheableActions() 
    {
        return $this->_caching;
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