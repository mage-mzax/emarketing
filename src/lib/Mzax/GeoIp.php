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
class Mzax_GeoIp
{
    
    
    /**
     * 
     * @var array
     */
    protected $_adapters = array();
    
    
    /**
     * File resource handle for persist managment
     * 
     * @var resource
     */
    protected $_persistence;
    
    
    
    
    protected $_cache = array();
    
    
    
    
    /**
     * 
     * @param string $persist
     */
    public function __construct($persist = null)
    {
        if($persist) {
            $this->persist($persist);
        }
        
        if(empty($this->_adapters)) {
            $this->addAdapter(new Mzax_GeoIp_Adapter_FreeGeoIp());
            $this->addAdapter(new Mzax_GeoIp_Adapter_Ipinfo());
            $this->addAdapter(new Mzax_GeoIp_Adapter_GeoPlugin());
        }
    }
    
    
    
    /**
     * Add another adapter
     * 
     * @param Mzax_GeoIp_Adapter_Abstract $adapter
     * @throws Exception
     * @return Mzax_GeoIp
     */
    public function addAdapter($adapter)
    {
        if(!$adapter instanceof Mzax_GeoIp_Adapter_Abstract) {
            throw new Exception("Invalid Adapter");
        }
        $this->_adapters[] = $adapter;
        return $this;
    }
    
    
    /**
     * Remove all adapters
     * 
     * @return Mzax_GeoIp
     */
    public function clearAdapters()
    {
        $this->_adapters = array();
        return $this;
    }
    
    
    /**
     * Has adapters
     * 
     * @return boolean
     */
    public function hasAdapters()
    {
        return !empty($this->_adapters);
    }
    
    
    
    
    /**
     * Fetch information for the given IP address
     * 
     * @param string $ip
     * @return Mzax_GeoIp_Request|NULL
     */
    public function fetch($ip)
    {
        if(isset($this->_cache[$ip])) {
            return $this->_cache[$ip];
        }
        
        $request = new Mzax_GeoIp_Request($ip);
        
        /* @var $adapter Mzax_GeoIp_Adapter_Abstract */
        foreach($this->_adapters as $adapter) {
            if( $adapter->isReady() ) {
                if($adapter->fetch($request)) {
                    $this->_cache[$ip] = $request;
                    return $request;
                }
            }
        }
        return null;
    }
    
    
    
    /**
     * Get number of remaning request that
     * we can send out without reaching our
     * periodical limits
     * 
     * @return integer
     */
    public function getRemainingRequests()
    {
        $counts = 0;
        /* @var $adapter Mzax_GeoIp_Adapter_Abstract */
        foreach($this->_adapters as $adapter) {
            if(!$adapter->getRestTime()) {
                $counts += $adapter->getRemainingRequests();
            }
            
        }
        
        return $counts;
    }
    
    
    
    /**
     * Retrieve the minimum rest time before we can
     * send out another request
     * 
     * @return integer
     */
    public function getRestTime()
    {
        $time = time();
        /* @var $adapter Mzax_GeoIp_Adapter_Abstract */
        foreach($this->_adapters as $adapter) {
            if($adapter->getRemainingRequests()) {
                $time = min($time, $adapter->getRestTime());
            }
        }
        return $time;
    }
    
    
    
    /**
     * Set file for persistence
     *
     * It will keep track of number of request etc
     *
     * @param string $filename
     * @return Mzax_GeoIp
     */
    public function persist($filename)
    {
        $this->_persistence = fopen($filename, 'c+');
        
        if($this->_persistence) {
            flock($this->_persistence, LOCK_EX);
            try {
                $data = fread($this->_persistence, 1024*1024);
                $this->_adapters = unserialize($data);
            }
            catch(Exception $e) {}
        }
        return $this;
    }
    
    
    
    /**
     * Save to persistence file
     * 
     * @return Mzax_GeoIp
     */
    public function drop()
    {
        if($this->_persistence) {
            ftruncate($this->_persistence, 0);
            fseek($this->_persistence, 0);
            fwrite($this->_persistence, serialize($this->_adapters));
            flock($this->_persistence, LOCK_UN);
            fclose($this->_persistence);
            $this->_persistence = null;
        }
        return $this;
    }
    
    
    
    public function __destruct()
    {
        try {
            $this->drop();
        }
        catch(Exception $e) {}
    }
    
    
}