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
     * Indexed map of Actions to attempt Page caching on by Controller
     *
     * @var array
     */
    protected $_caching = array();

    /**
     * Indexed map of Tags by Controller and Action
     *
     * @var array
     */
    protected $_tags = array();

    /**
     * Track output buffering condition
     */
    protected $_obStarted = false;

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

    public function removePage($relativeUrl, $recursive = false) 
    {
        if ($recursive) {
            return $this->getCache('page')->removeRecursive($relativeUrl);
        } else {
            return $this->getCache('page')->remove($relativeUrl);
        }
    }

    public function removePagesTagged(array $tags) 
    {
        return $this->getCache('page')
            ->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }

    public function preDispatch()
    {
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        if (isset($this->_caching[$controller]) &&
        in_array($action, $this->_caching[$controller])) {
            $this->getCache('page')->start();
            $this->_obStarted = true;
        }
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
     * Return a list of tags set for all cacheable actions
     *
     * @return array
     */
    public function getCacheableTags() 
    {
        return $this->_tags;
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
        throw new Zend_Controller_Action_Exception('Method does not exist:' . $method);
    }

}