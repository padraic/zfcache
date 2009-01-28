<?php

class Zend_Controller_Action_Helper_Cache extends Zend_Controller_Action_Helper_Abstract
{

    protected $_manager = null;

    public function init()
    {
    }

    public function direct()
    {
    }

    public function preDispatch()
    {
    }

    public function postDispatch()
    {
    }

    protected function __call($method, $args)
    {
        if (method_exists($method, $this->_manager)) {
            return call_user_func_array(array($this->_manager, $method), $args);
        }
    }

}