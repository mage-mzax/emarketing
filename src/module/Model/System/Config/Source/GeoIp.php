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
 * Class Mzax_Emarketing_Model_System_Config_Source_Geoip
 */
class Mzax_Emarketing_Model_System_Config_Source_Geoip
{
    /**
     * @var Mzax_GeoIp_Adapter_Abstract[]
     */
    protected $_adapters;

    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_configNode;

    /**
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_config;

    /**
     * Mzax_Emarketing_Model_Outbox constructor.
     */
    public function __construct()
    {
        $this->_config = Mage::getSingleton('mzax_emarketing/config');
        $this->_configNode = Mage::getConfig()->getNode('global/mzax_emarketing/geoip_adapters');
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->getAdapters() as $name => $adapter) {
            $options[] = array(
                'value' => $name,
                'label' => $adapter->getName()
            );
        }
        return $options;
    }

    /**
     * Retrieve adapters
     *
     * @return Mzax_GeoIp_Adapter_Abstract[]
     */
    public function getAdapters()
    {
        if (!$this->_adapters) {
            $this->_adapters = array();

            foreach ($this->getConfig()->children() as $name => $config) {
                $adapterClass = $config->getClassName();
                if (!class_exists($adapterClass)) {
                    continue;
                }

                $adapter = new $adapterClass;
                if (!$adapter instanceof Mzax_GeoIp_Adapter_Abstract) {
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
     * @return Mzax_GeoIp_Adapter_Abstract[]
     */
    public function getSelectedAdapters()
    {
        $adapters = $this->getAdapters();

        $selectedAdapters = $this->_config->get('mzax_emarketing/tracking/geo_ip_adapters');
        $selectedAdapters = explode(',', $selectedAdapters);

        $selected = array();
        foreach ($selectedAdapters as $name) {
            if (isset($adapters[$name])) {
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
        return $this->_configNode;
    }
}
