<?php

class Zend_Cache_Manager
{

    protected $_caches = array();

    protected $_configTemplates = array(
        'page' => array(
            'frontendName' => 'Output',
            'backendName' => 'Static',
            'frontendOptions' => array(),
            'backendOptions' => array(
                'public_dir' => '',
                'cache_dir' => 'html',
                'file_extension' => '.html',
            )
        ),
        'tag_cache' => array(
            'frontendName' => 'Core',
            'backendName' => 'File',
            'frontendOptions' => array(
                'automatic_serialization' => true,
                'lifetime' => null
            ),
            'backendOptions' => array(
            )
        ),
    );

    public function setCache($name, Zend_Cache_Core $cache) 
    {
        $this->_caches[$name] = $cache;
    }

    public function hasCache($name) 
    {
    }

    public function getCache($name) 
    {
        if (isset($this->_caches[$name])) {
            return $this->_caches[$name];
        }
        if (isset($this->_configTemplates[$name])) {
            $this->_caches[$name] = Zend_Cache::factory(
                $this->_configTemplates[$name]['frontendName'],
                $this->_configTemplates[$name]['backendName'],
                $this->_configTemplates[$name]['frontendOptions'],
                $this->_configTemplates[$name]['backendOptions']
            );
            return $this->_caches[$name];
        }
    }

    public function setCacheTemplate() 
    {
    }

    public function hasCacheTemplate() 
    {
    }

    public function getCacheTemplate() 
    {
    }

}