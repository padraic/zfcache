<?php

/** Zend_Cache_Exception */
require_once 'Zend/Cache/Exception.php';

class Zend_Cache_Manager
{

    /**
     * Array of caches stored by the Cache Manager instance
     *
     * @var array
     */
    protected $_caches = array();

    /**
     * Array of ready made configuration templates for lazy
     * loading caches.
     *
     * @var array
     */
    protected $_configTemplates = array(
        // Null Cache (Enforce Null/Empty Values)
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
        // Simple Common Default
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
        // Static Page HTML Cache
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

    /**
     * Set a new cache for the Cache Manager to contain
     *
     * @param string $name
     * @param Zend_Cache_Core $cache
     * @return void
     */
    public function setCache($name, Zend_Cache_Core $cache)
    {
        $this->_caches[$name] = $cache;
    }

    /**
     * Check if the Cache Manager contains the named cache object, or a named
     * configuration template to lazy load the cache object
     *
     * @param string $name
     * @return bool
     */
    public function hasCache($name)
    {
        if (isset($this->_caches[$name]) || $this->hasCacheTemplate($name)) {
            return true;
        }
        return false;
    }

    /**
     * Fetch the named cache object, or instantiate and return a cache object
     * using a named configuration template
     *
     * @param string $name
     * @return Zend_Cache_Core
     */
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

    /**
     * Set a named configuration template from which a cache object can later
     * be lazy loaded
     *
     * @param string $name
     * @param array $config
     * @return void
     */
    public function setCacheTemplate($name, array $config)
    {
        $this->_configTemplates[$name] = $config;
    }

    /**
     * Check if the named configuration template
     *
     * @param string $name
     * @return bool
     */
    public function hasCacheTemplate($name)
    {
        if (isset($this->_configTemplates[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Get the named configuration template
     *
     * @param string $name
     * @return array
     */
    public function getCacheTemplate($name)
    {
        if (isset($this->_configTemplates[$name])) {
            return $this->_configTemplates[$name];
        }
    }

    /**
     * Pass an array containing changes to be applied to a named configuration
     * template
     *
     * @param string $name
     * @param array $config
     * @return void
     */
    public function setTemplateConfig($name, array $config)
    {
        if (!isset($this->_configTemplates[$name])) {
            throw new Zend_Cache_Exception('A cache configuration template does not exist with the name "' . $name . '"');
        }
        $this->_configTemplates[$name]
            = $this->_mergeConfigs($this->_configTemplates[$name], $config);
    }

    /**
     * Simple method to merge two configuration arrays
     *
     * @param array $current
     * @param array $config
     * @return array
     */
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