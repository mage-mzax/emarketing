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
 * Factory class for recipient providers
 */
class Mzax_Emarketing_Model_Recipient_Provider implements Mage_Eav_Model_Entity_Attribute_Source_Interface
{
    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config;

    /**
     * @var Mzax_Emarketing_Model_Recipient_Provider_Abstract[]
     */
    protected $_providers;

    /**
     * Retrieve Provider
     *
     * @param string $name
     * @throws Exception
     *
     * @return Mzax_Emarketing_Model_Recipient_Provider_Abstract
     */
    public function factory($name)
    {
        $config = $this->getConfig();
        if (!isset($config->$name)) {
            throw new Exception("No such email provider ({$name}) found");
        }
        $config = $config->$name;

        $providerClass = $config->getClassName();

        if (!class_exists($providerClass)) {
            throw new Exception("Email provider config found, but model ($providerClass) was not found");
        }

        /* @var $provider Mzax_Emarketing_Model_Recipient_Provider_Abstract */
        $provider = new $providerClass;
        $provider->setName($name);
        $provider->setType($name);

        return $provider;
    }

    /**
     * Retrieve All options
     *
     * @param bool $withEmpty
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        $options = array();

        /* @var $provider Mzax_Emarketing_Model_Recipient_Provider_Abstract */
        foreach ($this->getProviders() as $name => $provider) {
            $options[] = array(
                'value' => $name,
                'label' => $provider->getTitle()
            );
        }
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getOptionHash()
    {
        $options = array();

        /* @var $provider Mzax_Emarketing_Model_Recipient_Provider_Abstract */
        foreach ($this->getProviders() as $name => $provider) {
            $options[$name] = $provider->getTitle();
        }

        return $options;
    }

    /**
     * Retrieve all providers
     *
     * Only use those providers to retrieve genaral information,
     * they are meant to treat as singletons.
     *
     * Create an instance using the facory() method if you would like to use one.
     *
     * @return Mzax_Emarketing_Model_Recipient_Provider_Abstract[]
     */
    public function getProviders()
    {
        if (!$this->_providers) {
            $this->_providers = array();

            foreach ($this->getConfig()->children() as $name => $cfg) {
                $this->_providers[$name] = $this->factory($name);
            }
        }
        return $this->_providers;
    }

    /**
     * Retrieve Option value text
     *
     * @param string $value
     * @return mixed
     */
    public function getOptionText($value)
    {
        $options = $this->getProviders();
        if (isset($options[$value])) {
            return $options[$value]->getTitle();
        }
        return false;
    }

    /**
     * Retrieve email marketing collection config
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = Mage::getConfig()->getNode('global/mzax_emarketing/providers');
        }
        return $this->_config;
    }
}
