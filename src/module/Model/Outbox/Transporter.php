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


class Mzax_Emarketing_Model_Outbox_Transporter
{
    
    protected $_config;
    
    
    
    /**
     * 
     * @param string $name
     * @throws Exception
     * @return Mzax_Emarketing_Model_Outbox_Email_Transporter_Interface
     */
    public function factory($name)
    {
        $config = $this->getConfig();
        if(!isset($config->$name)) {
            throw new Exception("No such email transporter found ({$name})");
        }
        $config = $config->$name;
        
        
        $transporterClass = $config->getClassName();
        
        if(!class_exists($transporterClass)) {
            throw new Exception("Email transporter config found, but model ($transporterClass) was not found");
        }
        
        /* @var $transporter Mzax_Emarketing_Model_Outbox_Transporter_Interface */
        $transporter = new $transporterClass;
        
        if(!$transporter instanceof Mzax_Emarketing_Model_Outbox_Transporter_Interface) {
            throw new Exception("Email transporter '{$name}' must implement 'Mzax_Emarketing_Model_Outbox_Email_Transporter_Interface'");
        }
        
        return $transporter;
    }
    
    
    
    public function toOptionArray($withEmpty = false)
    {
        return $this->getAllOptions($withEmpty);
    }
    


    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $options = array();
        
        foreach($this->getConfig()->children() as $name => $cfg) {
            $options[] = array(
                'label' => (string) $cfg->title, 
                'value' => $name
            );
        }
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }
    
        return $options;
    }
    
    
    
    
    

    /**
     * Retrieve config
     *
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfig()
    {
        if(!$this->_config) {
            $this->_config = Mage::getConfig()->getNode('global/mzax_emarketing/mediums/email/transporters');
        }
        return $this->_config;
    }
    
    
}
