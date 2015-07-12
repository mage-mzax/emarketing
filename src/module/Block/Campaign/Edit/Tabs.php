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


class Mzax_Emarketing_Block_Campaign_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mzax_emarketing_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Setup Campaign'));
    }

    protected function _beforeToHtml()
    {
        /* @var $campaign  Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');
        
        
        
        if(!$campaign->getMedium()) {
            $this->addTab('medium', array(
                'label'     => $this->__('Choose Medium'),
                'content'   => $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_medium')->initForm()->toHtml(),
                'active'    => true
            ));
        }
        else {
            
            $mediumTitle = Mage::getSingleton('mzax_emarketing/medium')->getOptionText($campaign->getData('medium'));
            if($campaign->getId()) {
                $this->setTitle($this->__('%s Campaign', $mediumTitle));
            }
            else {
                $this->setTitle($this->__('New %s Campaign', $mediumTitle));
            }
            
            
            $this->addTab('settings', array(
                'label'     => $this->__('Settings'),
                'content'   => $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_settings')->initForm()->toHtml(),
                'active'    => true
            ));
            
            
            $this->addTab('content', array(
                'label'     => $this->__('Content'),
                'content'   => $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_content')->toHtml(),
                'active'    => false
            ));
    
            // only available if saved
            if($campaign->getId()) 
            {
                
                $this->addTab('filters', array(
                    'label'   => $this->__('Filters / Segmentation'),
                    'content' => $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_filters')->initForm()->toHtml(),
                    'active'  => false
                ));                
                $this->addTab('recipients', array(
                    'label'   => $this->__('Find Recipients'),
                    'class'   => 'ajax',
                    'url'     => $this->getUrl('*/*/recipients', array('_current' => true)),
                ));
                
                if(!$campaign->isArchived()) {
                    // we want to initalize it
                    $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_report');
                    $this->addTab('report', array(
                        'label'   => $this->__('Report'),
                        'class'   => 'ajax',
                        'url'     => $this->getUrl('*/*/report', array('_current' => true)),
                    ));
                }
                
                
                $this->addTab('tasks', array(
                    'label'   => $this->__('Tasks'),
                    'content' => $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_tasks')->toHtml(),
                    'active'  => false
                ));
                
                $campaign->getMedium()->prepareCampaignTabs($this);
                
                if($count = $campaign->countRecipientErrors()) {
                    $this->addTab('errors', array(
                        'label'   => $this->__('Recipient Errors (%s)', $count),
                        'class'   => 'ajax',
                        'url'     => $this->getUrl('*/*/errorGrid', array('_current' => true))
                    ));
                }
                
                
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