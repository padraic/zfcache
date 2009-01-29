<?php

class Zend_Cache_Backend_Database extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{

    protected $dbObject = null;

    /**
     * Interceptor child method to handle the case where a Model or
     * Database object is being set since it's not supported by the
     * standard backend interface
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setOption($name, $value)
    {
        if ($name == 'db_object') {
            $this->setDbObject($value);
        } else {
            parent::setOption($name, $value);
        }
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false);

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id);

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data            Datas to cache
     * @param  string $id              Cache id
     * @param  array $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int   $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false);

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id);

    /**
     * Clean some cache records
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @return boolean true if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array());

    /**
     * Set a database object which stores/retrieves caches.
     *
     * @param object $db
     * @return void
     */
    public function setDbObject($db)
    {
        $this->_dbObject = $db;
    }

    /**
     * Get the database or Model object if set
     *
     * @return Zend_Cache_Core
     */
    public function getDbObject()
    {
        if (is_null($this->_dbObject)) {
            Zend_Cache::throwException('A database object or Model has not been set; use setDbObject()');
        }
        return $this->_dbObject;
    }

}