<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cache_Backend
{
    /**
     * Frontend or Core directives
     *
     * =====> (int) lifetime :
     * - Cache lifetime (in seconds)
     * - If null, the cache is valid forever
     *
     * =====> (int) logging :
     * - if set to true, a logging is activated throw Zend_Log
     *
     * @var array directives
     */
    protected $_directives = array(
        'lifetime' => 3600,
        'logging'  => false,
        'logger'   => null
    );

    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = array();

    /**
     * Constructor
     *
     * @param  array $options Associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct(array $options = array())
    {
        while (list($name, $value) = each($options)) {
            $this->setOption($name, $value);
        }
    }

    /**
     * Set the frontend directives
     *
     * @param  array $directives Assoc of directives
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setDirectives($directives)
    {
        if (!is_array($directives)) Zend_Cache::throwException('Directives parameter must be an array');
        while (list($name, $value) = each($directives)) {
            if (!is_string($name)) {
                Zend_Cache::throwException("Incorrect option name : $name");
            }
            $name = strtolower($name);
            if (array_key_exists($name, $this->_directives)) {
                $this->_directives[$name] = $value;
            }

        }

        $this->_loggerSanity();
    }

    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            Zend_Cache::throwException("Incorrect option name : $name");
        }
        $name = strtolower($name);
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }
    }

    /**
     * Get an option
     *
     * @param  string $name
     * @throws Zend_Cache_Exception
     * @return mixed
     */
    public function getOption($name)
    {
        if (!is_string($name)) {
            Zend_Cache::throwException("Incorrect option name : $name");
        }
        $name = strtolower($name);
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }
    }

    /**
     * Get the life time
     *
     * if $specificLifetime is not false, the given specific life time is used
     * else, the global lifetime is used
     *
     * @param  int $specificLifetime
     * @return int Cache life time
     */
    public function getLifetime($specificLifetime)
    {
        if ($specificLifetime === false) {
            return $this->_directives['lifetime'];
        }
        return $specificLifetime;
    }

    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * DEPRECATED : use getCapabilities() instead
     *
     * @deprecated
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }

    /**
     * Return a system-wide tmp directory
     *
     * @return string System-wide tmp directory
     */
    static function getTmpDir()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // windows...
            foreach (array($_ENV, $_SERVER) as $tab) {
                foreach (array('TEMP', 'TMP', 'windir', 'SystemRoot') as $key) {
                    if (isset($tab[$key])) {
                        $result = $tab[$key];
                        if (($key == 'windir') or ($key == 'SystemRoot')) {
                            $result = $result . '\\temp';
                        }
                        return $result;
                    }
                }
            }
            return '\\temp';
        } else {
            // unix...
            if (isset($_ENV['TMPDIR']))    return $_ENV['TMPDIR'];
            if (isset($_SERVER['TMPDIR'])) return $_SERVER['TMPDIR'];
            return '/tmp';
        }
    }

    /**
     * Make sure if we enable logging that the Zend_Log class
     * is available.
     * Create a default log object if none is set.
     *
     * @throws Zend_Cache_Exception
     * @return void
     */
    protected function _loggerSanity()
    {
        if (!isset($this->_directives['logging']) || !$this->_directives['logging']) {
            return;
        }
        try {
            /**
             * @see Zend_Log
             */
            require_once 'Zend/Log.php';
        } catch (Zend_Exception $e) {
            Zend_Cache::throwException('Logging feature is enabled but the Zend_Log class is not available');
        }
        if (isset($this->_directives['logger']) && $this->_directives['logger'] instanceof Zend_Log) {
            return;
        }
        // Create a default logger to the standard output stream
        require_once 'Zend/Log/Writer/Stream.php';
        $logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
        $this->_directives['logger'] = $logger;
    }

    /**
     * Log a message at the WARN (4) priority.
     *
     * @param  string $message
     * @throws Zend_Cache_Exception
     * @return void
     */
    protected function _log($message, $priority = 4)
    {
        if (!$this->_directives['logging']) {
            return;
        }
        if (!(isset($this->_directives['logger']) || $this->_directives['logger'] instanceof Zend_Log)) {
            Zend_Cache::throwException('Logging is enabled but logger is not set');
        }
        $logger = $this->_directives['logger'];
        $logger->log($message, $priority);
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
        if (!preg_match('~^[\w]+$~D', $string)) {
            Zend_Cache::throwException("Invalid id or tag '$string' : must use only [a-zA-Z0-9_]");
        }
    }

    /**
     * Validate a tags array (security, reliable filenames, reserved prefixes...)
     *
     * Throw an exception if a problem is found
     *
     * @param  array $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return void
     */
    protected static function _validateTagsArray($tags)
    {
        if (!is_array($tags)) {
            Zend_Cache::throwException('Invalid tags array : must be an array');
        }
        foreach($tags as $tag) {
            self::_validateIdOrTag($tag);
        }
        reset($tags);
    }

}
