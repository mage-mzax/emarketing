<?php
/**
 * Mzax Emarketing (www.mzax.de)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Use a lock file to make sure a script can only run once at a time
 *
 * @method Mzax_Once|false lock(string $filename, number $timeout, number $maxRunTime)
 */
class Mzax_Once
{
    /**
     * Current lock file resource handle
     *
     * @var resource
     */
    protected $_fileHandle;

    /**
     * The absolute file path
     *
     * @var string
     */
    protected $_filename;

    /**
     * Informational data saved in the file
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Check for lock file and create new one
     * if no file exists.
     *
     * Other wise wait maximumg of $timeout
     * or force it if lockRunTime exceeded $maxRunTime
     *
     * @param string $filename Lock file
     * @param int $timeout In seconds
     * @param int $maxRunTime In seconds
     *
     * @return boolean
     * @throws Exception
     */
    public function acquireLock($filename, $timeout = 5, $maxRunTime = 3600)
    {
        if ($this->_filename) {
            throw new Exception('Lock already in place.');
        }

        if (file_exists($filename)) {
            $data = unserialize(file_get_contents($filename));

            // kill if process last touch exceeds max run time
            if ($data['last_touch']+$maxRunTime < time()) {
                unlink($filename);
            } elseif ($timeout <= 0) {
                return false;
            } else {
                // wait till time out is reached
                $time = microtime(true);
                while (1) {
                    time_nanosleep(0, 100000);

                    if (!file_exists($filename)) {
                        break;
                    } elseif ($timeout < (microtime(true)-$time)) {
                        return false;
                    }
                }
            }
        }

        $this->_data = array(
            'start'      => time(),
            'last_touch' => time(),
            'ip'         => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'uri'        => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
        );

        //file_put_contents($filename, $data)
        $this->_fileHandle = fopen($filename, 'c+');
        $this->_filename   = realpath($filename);

        if (!$this->_filename) {
            throw new Exception('Failed to create lock, file could not be created.');
        }

        // what ever happens, make sure to kill the current lock file
        register_shutdown_function(array('Mzax_Once', 'unlinkFile'), $this->_filename);

        $this->touch();

        return true;
    }

    /**
     * Update timestamp to prevent force kill on long processes
     *
     * @return boolean
     */
    public function touch()
    {
        $this->_data['last_touch'] = time();

        if ($this->_fileHandle) {
            ftruncate($this->_fileHandle, 0);
            fseek($this->_fileHandle, 0);
            fwrite($this->_fileHandle, serialize($this->_data));
            return true;
        }
        return false;
    }

    /**
     * Unlock file
     *
     * @return boolean
     */
    public function unlock()
    {
        if ($this->_fileHandle) {
            fclose($this->_fileHandle);
            @unlink($this->_filename);

            $this->_filename = null;
            $this->_fileHandle = null;

            return true;
        }
        return false;
    }

    /**
     * Static call for lock,
     *
     * @param string $filename
     * @param int $timeout
     * @param int $maxRunTime
     *
     * @return Mzax_Once|boolean
     */
    public static function createLock($filename, $timeout = 5, $maxRunTime = 3600)
    {
        $once = new self;
        if ($once->lock($filename, $timeout, $maxRunTime)) {
            return $once;
        }
        return false;
    }

    /**
     * Always unlock on destruct
     *
     * @return void
     */
    public function __destruct()
    {
        try {
            $this->unlock();
        } catch (Exception $e) {
            // ignore
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @deprecated
     */
    public function __call($name, $arguments)
    {
        if ($name === 'lock') {
            return call_user_func_array(array($this, 'acquireLock'), $arguments);
        }
        throw new BadMethodCallException("Method Mzax_Once::$name() not found");
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @deprecated
     */
    public static function __callStatic($name, $arguments)
    {
        if ($name === 'lock') {
            return call_user_func_array(array('Mzax_Once', 'createLock'), $arguments);
        }
        throw new BadMethodCallException("Method Mzax_Once::$name() not found");
    }

    /**
     * Delete file
     *
     * @param string $filename
     *
     * @return void
     */
    public static function unlinkFile($filename)
    {
        if (file_exists($filename)) {
            @unlink($filename);
        }
    }
}
