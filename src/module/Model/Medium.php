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
 * Factory class for meidums
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Medium implements Mage_Eav_Model_Entity_Attribute_Source_Interface
{
    
    /**
     * 
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config;
    
    
    
    /**
     * 
     * @var array
     */
    protected $_mediums;
 
    
    
    /**
     * Retrieve Medium
     * 
     * @param string $name
     * @throws Exception
     * @return Mzax_Emarketing_Model_Medium_Abstract
     */
    public function factory($name)
    {
        $config = $this->getConfig();
        if(!isset($config->$name)) {
            throw new Exception("No such email provider ({$name}) found");
        }
        $config = $config->$name;
        
        
        $mediumClass = $config->getClassName();
        
        if(!class_exists($mediumClass)) {
            throw new Exception("Meidum config found, but model ($mediumClass) was not found");
        }
        
        /* @var $medium Mzax_Emarketing_Model_Medium_Abstract */
        $medium = new $mediumClass;
        
        return $medium;
    }
    
    
    
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $options = array();
        
        /* @var medium Mzax_Emarketing_Model_Medium_Abstract */
        foreach($this->getMediums() as $medium => $title) {
            $options[] = array(
                'value' => $medium,
                'label' => $title
            );
        }
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }
        
        return $options;
    }
    
    
    
    
    /**
     * Retrieve all mediums
     * 
     * @return array
     */
    public function getMediums()
    {
        if(!$this->_mediums) {
            $this->_mediums = array();
            
            foreach($this->getConfig()->children() as $name => $cfg) {
                $this->_mediums[$name] = (string) $cfg->title;
            }
        }
        return $this->_mediums;
    }
    
    
    
    
    
    
    /**
     * Retrieve Option value text
     *
     * @param string $value
     * @return mixed
     */
    public function getOptionText($value)
    {
        $options = $this->getMediums();
        if(isset($options[$value])) {
            return $options[$value];
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
        if(!$this->_config) {
            $this->_config = Mage::getConfig()->getNode('global/mzax_emarketing/mediums');
        }
        return $this->_config;
    }
    
    
    
}
