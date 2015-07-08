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
 * @version     {{version}}
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * 
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Presist
{
    
    /**
     * 
     * @var resource
     */
    protected $_fileHandle;
    
    
    
    public $data;
    
    
    /**
     * Open presistence file and try to get lock
     * 
     * @return mixed peristence data
     */
    public function open($filename)
    {
        $this->_fileHandle = fopen($filename, 'c+');
    
        if($this->_fileHandle) {
            flock($this->_fileHandle, LOCK_EX);
            try {
                $data = fread($this->_fileHandle, 1024*1024*10);
                $this->data = unserialize($data);
            }
            catch(Exception $e) {
                $this->data = false;
                return false;
            }
        }
        return true;
    }
    
    
    
    /**
     * Close presistence file
     * 
     * @param mixed $data
     * @return Mzax_Presist
     */
    public function close()
    {
        if($this->_fileHandle) {
            ftruncate($this->_fileHandle, 0);
            fseek($this->_fileHandle, 0);
            fwrite($this->_fileHandle, serialize($this->data));
            flock($this->_fileHandle, LOCK_UN);
            fclose($this->_fileHandle);
            $this->_fileHandle = null;
        }
        return $this;
    }
        
    
    
    public function __destruct()
    {
        try {
            $this->close();
        }
        catch(Exception $e) {}
    }
    
    
    
}