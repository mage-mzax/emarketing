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


class Mzax_Emarketing_Block_Newsletter_List_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mzax_emarketing_newsletter_list_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Newsletter List'));
    }

    protected function _beforeToHtml()
    {
        /* @var $list  Mzax_Emarketing_Model_Newsletter_List */
        $list = Mage::registry('current_list');


        $this->addTab('settings', array(
            'label'     => $this->__('Settings'),
            'content'   => $this->getLayout()->createBlock('mzax_emarketing/newsletter_list_edit_tab_settings')->initForm()->toHtml(),
            'active'    => true
        ));

        if ($list->getId()) {
            $this->addTab('subscribers', array(
                'label'   => $this->__('Subscribers'),
                'content' => $this->getLayout()->createBlock('mzax_emarketing/newsletter_list_edit_tab_subscribers')->toHtml(),
                'active'  => false
            ));
        }

        $this->_updateActiveTab();
        return parent::_beforeToHtml();
    }




    protected function _updateActiveTab()
    {
    	$tabId = $this->getRequest()->getParam('tab');
    	if ( $tabId ) {
    		$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
    		if ($tabId) {
    			$this->setActiveTab($tabId);
    		}
    	}
    }
}
