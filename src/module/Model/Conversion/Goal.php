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


class Mzax_Emarketing_Model_Conversion_Goal implements Mage_Eav_Model_Entity_Attribute_Source_Interface
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
    protected $_goals;
    
    
    /**
     * 
     * @param string $name
     * @return Mzax_Emarketing_Model_Conversion_Goal_Abstract
     */
    public function factory($name)
    {
        $config = $this->getConfig();
        if(!isset($config->$name)) {
        	return null;
        }
        $config = $config->$name;
        $class  = $config->getClassName();
        
        $instance = new $class($config);
        
        return $instance;
    }
    
    
    
    
    
    
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $options = array();
    
        /* @var $goal Mzax_Emarketing_Model_Conversion_Goal_Abstract */
        foreach($this->getGoals() as $name => $goal) {
            $options[] = array(
                'value' => $name,
                'label' => $goal->getTitle()
            );
        }
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }
    
        return $options;
    }
    
    
    
    
    
    
    /**
     * Retrieve all conversion goals
     *
     * @return array
     */
    public function getGoals()
    {
        if(!$this->_goals) {
            $this->_goals = array();
            
            foreach($this->getConfig()->children() as $name => $cfg) {
                $this->_goals[$name] = self::factory($name);
            }
        }
        return $this->_goals;
    }
    
    
    
    

    /**
     * Retrieve Option value text
     *
     * @param string $value
     * @return mixed
     */
    public function getOptionText($value)
    {
        $options = $this->getFilters();
        if(isset($options[$value])) {
            return $options[$value]->getTitle();
        }
        return false;
    }
    
    
    
    /**
     * Retrieve config
     * 
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfig()
    {
        if(!$this->_config) {
            $this->_config = Mage::getConfig()->getNode('global/mzax_emarketing/goal_types');
        }
        return $this->_config;
    }
    
    
    
}
