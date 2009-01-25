<?php

/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/Interface.php';

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';

class Zend_Cache_Backend_Static extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{

    protected $_options = array(
        'public_dir' => null,
        'file_extension' => '.html',
        'index_filename' => 'index',
        'file_locking' => true,
        'cache_file_umask' => 0600,
        'debug_header' => false,
        'tag_cache' => null
    );

    protected $_tagCache = null;

    public function setOption($name, $value)
    {
        if ($name == 'tag_cache') {
            $this->setInnerCache($value);
        } else {
            parent::setOption($name, $value);
        }
    }

    public function load($id, $doNotTestCacheValidity = false)
    {
        if (empty($id)) {
            $id = $this->_detectId();
        }
        self::_validateIdOrTag($id);
        if (!$this->_verifyPath($id)) {
            Zend_Cache::throwException('Invalid cache id: does not match expected public_dir path');
        }
        if ($doNotTestCacheValidity) {
            $this->_log("Zend_Cache_Backend_Static::load() : \$doNotTestCacheValidity=true is unsupported by the Static backend");
        }
        $fileName = basename($id);
        if (empty($fileName)) {
            $fileName = $this->_options['index_filename'];
        }
        $pathName = $this->_options['public_dir'] . dirname($id);
        $file = $pathName . '/' . $fileName . $this->_options['file_extension'];
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return $content;
        }
        return false;
    }

    public function test($id)
    {
        self::_validateIdOrTag($id);
        if (!$this->_verifyPath($id)) {
            Zend_Cache::throwException('Invalid cache id: does not match expected public_dir path');
        }
        $fileName = basename($id);
        if (empty($fileName)) {
            $fileName = $this->_options['index_filename'];
        }
        $pathName = $this->_options['public_dir'] . dirname($id);
        $file = $pathName . '/' . $fileName . $this->_options['file_extension'];
        if (file_exists($file)) {
            return true;
        }
        return false;
    }

    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        self::_validateTagsArray($tags);
        clearstatcache();
        if (is_null($id) || strlen($id) == 0) {
            $id = $this->_detectId();
        }
        self::_validateIdOrTag($id);
        $fileName = basename($id);
        if (empty($fileName)) {
            $fileName = $this->_options['index_filename'];
        }
        $pathName = $this->_options['public_dir'] . dirname($id);
        if (!file_exists($pathName)) {
            mkdir($pathName, $this->_options['cache_file_umask'], true);
        }
        if (is_null($id) || strlen($id) == 0) {
            $dataUnserialized = unserialize($data);
            $data = $dataUnserialized['data'];
        }
        $file = $pathName . '/' . $fileName . $this->_options['file_extension'];
        if ($this->_options['file_locking']) {
            $result = file_put_contents($file, $data, LOCK_EX);
        } else {
            $result = file_put_contents($file, $data);
        }
        @chmod($file, $this->_options['cache_file_umask']);
        if (count($tags) > 0) {
            $this->_log('TAGS_UNSUPPORTED_BY_SAVE_OF_STATIC_BACKEND');
        }
        return (bool) $result;
    }

    public function remove($id)
    {
        self::_validateIdOrTag($id);
        if (!$this->_verifyPath($id)) {
            Zend_Cache::throwException('Invalid cache id: does not match expected public_dir path');
        }
        $fileName = basename($id);
        if (empty($fileName)) {
            $fileName = $this->_options['index_filename'];
        }
        $pathName = $this->_options['public_dir'] . dirname($id);
        $file = $pathName . '/' . $fileName . $this->_options['file_extension'];
        if (!file_exists($file)) {
            return false;
        }
        return unlink($file);
    }

    public function removeRecursively($id)
    {
        self::_validateIdOrTag($id);
        if (!$this->_verifyPath($id)) {
            Zend_Cache::throwException('Invalid cache id: does not match expected public_dir path');
        }
        $fileName = basename($id);
        if (empty($fileName)) {
            $fileName = $this->_options['index_filename'];
        }
        $pathName = $this->_options['public_dir'] . dirname($id);
        $file = $pathName . '/' . $fileName . $this->_options['file_extension'];
        $directory = $pathName . '/' . $fileName;
        if (file_exists($directory)) {
            if (!is_writable($directory)) {
                return false;
            }
            foreach (new DirectoryIterator($directory) as $file) {
                if (true === $file->isFile()) {
                    if (false === unlink($file->getPathName())) {
                        return false;
                    }
                }
            }
            rmdir(dirname($path));
        }
        if (file_exists($file)) {
            if (!is_writable($file)) {
                return false;
            }
            return unlink($file);
        }
    }

    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        self::_validateTagsArray($tags);
        $result = false;
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                if (empty($tags)) {
                    throw new Zend_Exception('Cannot use tag matching modes as no tags were defined');
                }
                $innerCache = $this->getInnerCache();
                if (!$tagged = $innerCache->load('zfextcache_tagged')) {
                    return true;
                }
                foreach ($tags as $tag) {
                    if (isset($tagged[$tag]) && !empty($tagged[$tag])) {
                        foreach ($tagged[$tag] as $requestUri) {
                            $this->remove($requestUri);
                            $index = array_search($requestUri,$tagged[$tag]);
                            unset($tagged[$tag][$index]);
                        }
                    }
                }
                $innerCache->save($tagged, 'zfextcache_tagged');
                $result = true;
                break;
            case Zend_Cache::CLEANING_MODE_ALL:
            case Zend_Cache::CLEANING_MODE_OLD:
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                $this->_log('STATIC backend unsupported mode');
                break;
            default:
                Zend_Cache::throwException('Invalid mode for clean() method');
                break;
        }
        return $result;
    }

    public function setInnerCache(Zend_Cache_Core $cache)
    {
        $this->_tagCache = $cache;
    }

    public function getInnerCache()
    {
        if (is_null($this->_tagCache)) {
            Zend_Cache::throwException('An Inner Cache has not been set; use setInnerCache()');
        }
        return $this->_tagCache;
    }

    protected function _verifyPath($path)
    {
        $path = realpath($path);
        $base = realpath($this->_options['public_dir']);
        return strncmp($path, $base, strlen($base)) !== 0;
    }

    protected function _detectId()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Validate a cache id or a tag (security, reliable filenames, reserved prefixes...)
     *
     * Throw an exception if a problem is found
     *
     * @param  string $string Cache id or tag
     * @throws Zend_Cache_Exception
     * @return void
     */
    protected static function _validateIdOrTag($string)
    {
        if (!is_string($string)) {
            Zend_Cache::throwException('Invalid id or tag : must be a string');
        }
        // internal only checked in Frontend - not here!
        if (substr($string, 0, 9) == 'internal-') {
            return;
        }
        // validation assumes no query string, fragments or scheme included - only the path
        if (!preg_match('/^(?:\/(?:(?:%[[:xdigit:]]{2}|[A-Za-z0-9-_.!~*\'()\[\]:@&=+$,;])*)?)+$/',
        $string)) {
            Zend_Cache::throwException("Invalid id or tag '$string' : must be a valid URL path");
        }
    }

}