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


class Mzax_Emarketing_Block_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        
        $this->_blockGroup = 'mzax_emarketing';
        $this->_controller = 'campaign';
        
        parent::__construct();
        
        $this->_updateButton('save', 'label', $this->__('Save Campaign'));
        $this->_updateButton('delete', 'label', $this->__('Delete Campaign'));

    }


    public function getHeaderCssClass()
    {
        return 'head-' . strtr($this->_controller, '_', '-');
    }

    public function getHeaderText()
    {
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');
        if ($campaign->getId()) {
            $text = $this->htmlEscape($campaign->getName());
            if($campaign->isRunning()) {
                return '<span class="mzax-running"></span>' . $text;
            }
            return $text;
        }
        else {
            return $this->__('New %s Campaign', Mage::getSingleton('mzax_emarketing/medium')->getOptionText($campaign->getData('medium')));
        }
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }
    
    protected function _prepareLayout()
    {
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');
        
    	$this->_addButton('save_and_continue', array(
            'label'     => $this->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
            'class' => 'save'
        ), 10);
    	
    	
    	if($campaign->getId() && !$campaign->isArchived()) {
    	    
    	    if($campaign->isRunning()) {
    	        
    	        $this->_removeButton('save');
    	        $this->removeButton('delete');
    	        
    	        $this->_addButton('stop', array(
	                'label'     => $this->__('STOP'),
	                'onclick'   => "confirmSetLocation('{$this->__('Are you sure you want to stop this campaign?')}', '{$this->getUrl('*/*/stop', array('_current' => true))}')",
	                'class' => 'mzax-stop'
    	        ), 100, 0);
    	        
    	        
    	        $this->_addButton('save_and_continue', array(
	                'label'     => $this->__('Save Changes'),
	                'onclick'   => "saveAndContinueEdit('{$this->_getSaveAndContinueUrl()}', '{$this->__('Are you sure you want to apply those changes to this running campaign?')}')",
	                'class' => 'save'
    	        ), 10);
    	    }
    	    else {
    	        $this->_addButton('start', array(
	                'label'     => $this->__('Start'),
	                'onclick'   => "confirmSetLocation('{$this->__('Are you sure you want to start this campaign?')}', '{$this->getUrl('*/*/start', array('_current' => true))}')",
	                'class' => 'mzax-start'
    	        ), 100, 0);
    	    }
    	}

    	return parent::_prepareLayout();
    }
    
    
    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        
        
        return $this->getUrl('*/*/save');
    }
    
    
    protected function _getSaveAndContinueUrl()
    {
    	return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
    	    'tab'       => '{{tab_id}}'
        ));
    }
}
