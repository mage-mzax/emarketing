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


class Mzax_Emarketing_Block_Campaign_Test_Emulate extends Mage_Adminhtml_Block_Template
{

    /**
     * 
     * @var Varien_Data_Form
     */
    protected $_form;
    
    
    
    /**
     * Retrieve Varien Data Form
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if(!$this->_form) {
            $this->_form = new Varien_Data_Form();
            $this->_form->setElementRenderer(Mage::getBlockSingleton('mzax_emarketing/editable')->setFormat('form'));
            $this->_form->setHtmlIdPrefix("emulate_");
            $this->_form->setFieldNameSuffix("emulate");
        }
        return $this->_form;
    }
    
    
    /**
     * Prepare filter
     * 
     * Usally called by parent block class
     * 
     * @param Mzax_Emarketing_Model_Object_Filter_Abstract $filter
     */
    public function prepareEmulation(Mzax_Emarketing_Model_Object_Filter_Abstract $filter)
    {
        $emulate = $this->getRequest()->getParam('emulate');
    
        if($this->emulate('time')) {
            if(isset($emulate['from']) && isset($emulate['to'])) {
                $filter->setParam('current_time', array($emulate['from'], $emulate['to']));
                $filter->setParam('is_local_time', true);
                
            }
        }
    
        if($this->emulate('campaign')) {
            /* @var $campagin Mzax_Emarketing_Model_Campaign */
            $campagin = Mage::getModel('mzax_emarketing/campaign');
            $campagin->load($emulate['campaign_id']);
    
            if($campagin->getId()) {
                $filter->setParam('campaign', $campagin);
            }
        }
    }
    
    
    
    /**
     * Check if we should emulate the specified key
     *
     * @param string $key
     * @return boolean
     */
    public function emulate($key)
    {
        $emulate = $this->getRequest()->getParam('emulate');
    
        if(isset($emulate[$key])) {
            return ($emulate[$key] == 1);
        }
        return false;
    }
    
    
    
    
    
    /**
     *
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getCampaignSelect()
    {
        $params = $this->getRequest()->getParam('emulate');
        
        /* @var $collection Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $collection->addArchiveFilter(false);
    
        $options = array();
        if($this->getParam('tracker') instanceof Mzax_Emarketing_Model_Conversion_Tracker) {
            $options['current'] = $this->__('beeing tracked');
        }
        $options += $collection->toOptionHash();
        
        /* @var $campagin Mzax_Emarketing_Model_Campaign */
        $campagin = Mage::getModel('mzax_emarketing/campaign');
        
        if(isset($params['campaign_id'])) {
            $campagin->load($params['campaign_id']);
        }
                
        return $this->getForm()->addField('campaign_id', 'select', array(
            'name'           => 'campaign_id',
            'value_name'     => (string) $campagin->getName(),
            'value'		     => $campagin->getId(),
            'options'        => $options,
        ));
    
    }
    
    
    
    
    /**
     * Helper for simple select element
     *
     * @param string $key
     * @param array $options
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getDateElement($key)
    {
        $format = Varien_Date::DATE_INTERNAL_FORMAT;
        
        $params = $this->getRequest()->getParam('emulate');
        
        if(isset($params[$key])) {
            $value = $params[$key];
        }
        else {
            $value = Zend_Date::now()->toString($format);
        }
    
        return $this->getForm()->addField($key, 'date',array(
            'name'           => $key,
            'value_name'     => $value,
            'value'		     => $value,
            'explicit_apply' => true,
            'image'          => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'input_format'   => $format,
            'format'         => $format
        ));
    }
    
    
}
