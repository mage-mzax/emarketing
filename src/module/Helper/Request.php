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
class Mzax_Emarketing_Helper_Request extends Mage_Core_Helper_Abstract
{
    /**
     * 
     * @var string
     */
    const CACHE_ID = 'mzax_emarketing_bad_requests';
    
    
    /**
     * 
     * @var integer
     */
    const MAX_BAD_REQUESTS = 25;
    
    
    /**
     * 
     * @var integer
     */
    const RESET_TIME = 3600;
    
    
    /**
     * 
     * @var array
     */
    protected $_data;
    
    
    
    /**
     * Check if we can "trust" the request
     * 
     * @param string $request
     * @return boolean
     */
    public function isTrustable($request = null)
    {
        $data = $this->getData($request);
        return ($data[0] < self::MAX_BAD_REQUESTS);
    }
    
    
    
    /**
     * Flag current connection
     * 
     * if a connection (IP) has to many bad requests
     * then we should not trust them anymore with
     * any sensetive data
     * 
     * @param string $request
     * @return number
     */
    public function bad($request = null)
    {
        $data = $this->getData($request);
        
        // reset counter if last bad request is a while ago
        if(time() - $data[1] >= self::RESET_TIME) {
            $data[0] = 0;
        }
        
        // increase
        $data[0]++;
        $data[1] = time();
        
        $this->setData($data, $request);
        return $data[0];
    }
    
    
    

    /**
     * Retrieve data for request
     * 
     * @param string $request
     * @return array
     */
    public function getData($request = null)
    {
        if(!$request) {
            $request = Mage::app()->getRequest();
        }
        $ip = $request->getServer('REMOTE_ADDR');
        
        $data = $this->loadData();
        
        if( isset($data[$ip]) ) {
            return $data[$ip];
        }
        
        // no request yet made by this ip yet
        return array(0, time());
    }
    
    
    
    /**
     * Set dat for request
     * 
     * @param array $data
     * @param string $request
     */
    public function setData(array $data, $request = null)
    {
        if(!$request) {
            $request = Mage::app()->getRequest();
        }
        $ip = $request->getServer('REMOTE_ADDR');
        
        $cacheData = $this->loadData();
        $cacheData[$ip] = $data;
        $this->saveData($cacheData);
    }
    
    
    
    /**
     * Load dat from cache
     * 
     * @return array
     */
    public function loadData()
    {
        if(!$this->_data) {
            $this->_data = Mage::app()->loadCache(self::CACHE_ID);
            if($this->_data) {
                $this->_data = unserialize($this->_data);
            }
            else {
                $this->_data = array();
            }
        }
        return $this->_data;
    }
    
    
    
    /**
     * save data to cache
     * 
     * @param array $data
     */
    public function saveData(array $data)
    {
        $this->_data = $data;
        Mage::app()->saveCache(serialize($data), self::CACHE_ID);
    }
    
    

}
