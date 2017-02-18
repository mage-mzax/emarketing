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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tabs
 *
 * @method setTitle(string $title)
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Mzax_Emarketing_Block_Campaign_Edit_Tabs constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('mzax_emarketing_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Setup Campaign'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        /* @var $campaign  Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');

        if (!$campaign->getMedium()) {
            /** @var Mzax_Emarketing_Block_Campaign_Edit_Tab_Medium $mediumTab */
            $mediumTab = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_medium');
            $mediumTabContent = $mediumTab->initForm()->toHtml();

            $this->addTab('medium', array(
                'label'     => $this->__('Choose Medium'),
                'content'   => $mediumTabContent,
                'active'    => true
            ));
        } else {
            /** @var Mzax_Emarketing_Model_Medium $mediumFactory */
            $mediumFactory = Mage::getSingleton('mzax_emarketing/medium');
            $mediumTitle = $mediumFactory->getOptionText($campaign->getData('medium'));
            if ($campaign->getId()) {
                $this->setTitle($this->__('%s Campaign', $mediumTitle));
            } else {
                $this->setTitle($this->__('New %s Campaign', $mediumTitle));
            }

            /** @var Mzax_Emarketing_Block_Campaign_Edit_Tab_Settings $settingsTab */
            $settingsTab = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_settings');

            /** @var Mzax_Emarketing_Block_Campaign_Edit_Tab_Content $contentTab */
            $contentTab = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_content');

            $this->addTab('settings', array(
                'label'     => $this->__('Settings'),
                'content'   => $settingsTab->initForm()->toHtml(),
                'active'    => true
            ));

            $this->addTab('content', array(
                'label'     => $this->__('Content'),
                'content'   => $contentTab->toHtml(),
                'active'    => false
            ));

            // only available if saved
            if ($campaign->getId()) {
                /** @var Mzax_Emarketing_Block_Campaign_Edit_Tab_Filters $filtersTab */
                $filtersTab = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_filters');

                /** @var Mzax_Emarketing_Block_Campaign_Edit_Tab_Tasks $taskTab */
                $taskTab = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_tasks');

                $this->addTab('filters', array(
                    'label'   => $this->__('Filters / Segmentation'),
                    'content' => $filtersTab->initForm()->toHtml(),
                    'active'  => false
                ));
                $this->addTab('recipients', array(
                    'label'   => $this->__('Find Recipients'),
                    'class'   => 'ajax',
                    'url'     => $this->getUrl('*/*/recipients', array('_current' => true)),
                ));

                if (!$campaign->isArchived()) {
                    $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_report');
                    $this->addTab('report', array(
                        'label'   => $this->__('Report'),
                        'class'   => 'ajax',
                        'url'     => $this->getUrl('*/*/report', array('_current' => true)),
                    ));
                }

                $this->addTab('tasks', array(
                    'label'   => $this->__('Tasks'),
                    'content' => $taskTab->toHtml(),
                    'active'  => false
                ));

                $campaign->getMedium()->prepareCampaignTabs($this);

                if ($count = $campaign->countRecipientErrors()) {
                    $this->addTab('errors', array(
                        'label'   => $this->__('Recipient Errors (%s)', $count),
                        'class'   => 'ajax',
                        'url'     => $this->getUrl('*/*/errorGrid', array('_current' => true))
                    ));
                }
            } elseif ($campaign->getData('preset')) {
                /** @var Mzax_Emarketing_Block_Campaign_Edit_Tab_Filters $filtersTab */
                $filtersTab = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_filters');

                $this->addTab('filters', array(
                    'label'   => $this->__('Filters / Segmentation'),
                    'content' => $filtersTab->initForm()->toHtml(),
                    'active'  => false
                ));
            }
        }

        $this->_updateActiveTab();

        return parent::_beforeToHtml();
    }

    /**
     * Update current active tab
     *
     * @return void
     */
    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}
