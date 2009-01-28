<?php

class Zend_Cache_Manager
{

    protected $_caches = array();

    protected $_configTemplates = array(
        'skeleton' => array(
            'frontend' => array(
                'name' => null,
                'options' => array()
            ),
            'backend' => array(
                'name' => null,
                'options' => array()
            )
        ),
        'default' => array(
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
        ),
        'page' => array(
            'frontend' => array(
                'name' => 'Output',
                'options' => array(
                    'ignore_user_abort' => true
                )
            ),
            'backend' => array(
                'name' => 'Static',
                'options' => array(
                    'public_dir' => '../public',
                )
            )
        )
    );

    public function setCache($name, Zend_Cache_Core $cache) 
    {
        $this->_caches[$name] = $cache;
    }

    public function hasCache($name) 
    {
        if (isset($this->_caches[$name]) || $this->hasCacheTemplate($name)) {
            return true;
        }
        return false;
    }

    public function getCache($name) 
    {
        if (isset($this->_caches[$name])) {
            return $this->_caches[$name];
        }
        if (isset($this->_configTemplates[$name])) {
            $this->_caches[$name] = Zend_Cache::factory(
                $this->_configTemplates[$name]['frontend']['name'],
                $this->_configTemplates[$name]['backend']['name'],
                $this->_configTemplates[$name]['frontend']['options'],
                $this->_configTemplates[$name]['backend']['options']
            );
            return $this->_caches[$name];
        }
    }

    public function setCacheTemplate($name, array $config) 
    {
        $this->_configTemplates[$name] = $config;
    }

    public function hasCacheTemplate($name) 
    {
        if (isset($this->_configTemplates[$name])) {
            return true;
        }
        return false;
    }

    public function getCacheTemplate($name) 
    {
        if (isset($this->_configTemplates[$name])) {
            return $this->_configTemplates[$name];
        }
    }

    public function setTemplateConfig($name, array $config)
    {
        if (!isset($this->_configTemplates[$name])) {
            $this->_configTemplates[$name] = $this->_configTemplates['skeleton'];
        }
        $this->_configTemplates[$name] 
            = $this->_mergeConfigs($this->_configTemplates[$name], $config);
    }

    protected function _mergeConfigs(array $current, array $config) 
    {
        if (isset($config['frontend']['name'])) {
            $current['frontend']['name'] = $config['frontend']['name'];
        }
        if (isset($config['backend']['name'])) {
            $current['backend']['name'] = $config['backend']['name'];
        }
        if (isset($config['frontend']['options'])) {
            foreach ($config['frontend']['options'] as $key=>$value) {
                $current['frontend']['options'][$key] = $value;
            }
        }
        if (isset($config['backend']['options'])) {
            foreach ($config['backend']['options'] as $key=>$value) {
                $current['backend']['options'][$key] = $value;
            }
        }
        return $current;
    }

}