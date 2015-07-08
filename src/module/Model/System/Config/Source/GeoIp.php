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



class Mzax_Emarketing_Model_System_Config_Source_Geoip
{
    protected $_adapters;
    
    protected $_config;

    
    public function toOptionArray($isMultiselect=false)
    {
        $options = array();
        foreach($this->getAdapters() as $name => $adapter) {
            $options[] = array(
                'value' => $name, 
                'label' => $adapter->getName()
            );
        }
        return $options;
    }
    
    
    
    
    
    
    public function getAdapters()
    {
        if(!$this->_adapters) {
            $this->_adapters = array();
        
            foreach($this->getConfig()->children() as $name => $cfg) {
        
                $adapterClass = $cfg->getClassName();
        
                if(!class_exists($adapterClass)) {
                    continue;
                }
        
                $adapter = new $adapterClass;
                if(!$adapter instanceof Mzax_GeoIp_Adapter_Abstract) {
                    continue;
                }
        
                $this->_adapters[$name] = $adapter;
            }
        }
        return $this->_adapters;
    }
    
    
    
    /**
     * Retrieve a list of all selected adapters
     * 
     * @return array
     */
    public function getSelectedAdapters()
    {
        $adapters = $this->getAdapters();
        
        $selectedAdapters = Mage::getStoreConfig('mzax_emarketing/tracking/geo_ip_adapters');
        $selectedAdapters = explode(',', $selectedAdapters);
        
        $selected = array();
        foreach($selectedAdapters as $name) {
            if(isset($adapters[$name])) {
                $selected[$name] = $adapters[$name];
            }
        }
        return $selected;
    }
    
    


    /**
     * Retrieve email marketing collection config
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfig()
    {
        if(!$this->_config) {
            $this->_config = Mage::getConfig()->getNode('global/mzax_emarketing/geoip_adapters');
        }
        return $this->_config;
    }
    
    
    
}
