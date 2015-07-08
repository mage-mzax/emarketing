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
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Block_Tracker_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mzax_emarketing_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Conversion Tracker'));
    }

    protected function _beforeToHtml()
    {
        /* @var $tracker  Mzax_Emarketing_Model_Conversion_Tracker */
        $tracker = Mage::registry('current_tracker');
        
        if(!$tracker->getGoalType())
        {
            $this->addTab('goaltype', array(
                'label'     => $this->__('Choose a goal type'),
                'content'   => $this->getLayout()->createBlock('mzax_emarketing/tracker_edit_tab_goalType')->initForm()->toHtml(),
                'active'    => true
            ));
        }
        else
        {
            $this->addTab('settings', array(
                'label'     => $this->__('Settings'),
                'content'   => $this->getLayout()->createBlock('mzax_emarketing/tracker_edit_tab_settings')->initForm()->toHtml(),
                'active'    => true
            ));
            
            $this->addTab('conditions', array(
                'label'   => $this->__('Conditions'),
                'content' => $this->getLayout()->createBlock('mzax_emarketing/tracker_edit_tab_conditions')->initForm()->toHtml(),
                'active'  => false
            ));
            
            if($tracker->getId()) {
                $this->addTab('task', array(
                    'label'   => $this->__('Tasks'),
                    'content' => $this->getLayout()->createBlock('mzax_emarketing/tracker_edit_tab_tasks')->toHtml(),
                    'active'  => false
                ));
            }
        }
        
        
        $this->_updateActiveTab();
        return parent::_beforeToHtml();
    }

    
    
    protected function _updateActiveTab()
    {
    	$tabId = $this->getRequest()->getParam('tab');
    	if( $tabId ) {
    		$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
    		if($tabId) {
    			$this->setActiveTab($tabId);
    		}
    	}
    }
}