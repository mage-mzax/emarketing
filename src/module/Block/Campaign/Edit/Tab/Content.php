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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_Content
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_BLOCK_CAMPAIGN = 'mzax_emarketing/campaign_edit_tab_content_original';
    const TAB_BLOCK_VARIATION = 'mzax_emarketing/campaign_edit_tab_content_variation';

    /**
     * Mzax_Emarketing_Block_Campaign_Edit_Tab_Content constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('campaign_email_tab');
        $this->setDestElementId('mzax_emarketing_info_tabs_content_content');
        $this->setTemplate('mzax/emarketing/campaign/content-tabs.phtml');
        $this->setCurrentTabId('mzax_emarketing_info_tabs_content');
    }

    /**
     * Prepare Layout Content
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');

        $this->addTab('original', array(
            'label' => Mage::helper('mzax_emarketing')->__('Orignal'),
            'content' => $campaign,
            'active' => true,
        ));

        if (!$campaign->getId()) {
            $this->setDisabled(true);
            return $this;
        }

        $activeTab = (int)$this->getRequest()->getParam('variation');

        /* @var $variation Mzax_Emarketing_Model_Campaign_Variation */
        foreach ($campaign->getVariations() as $variation) {
            $this->addTab('variation_' . $variation->getId(), array(
                'label' => $variation->getName(),
                'content' => $variation,
                'active' => $activeTab == $variation->getId(),
                'remove_url' => $this->getRemoveUrl($variation)
            ));
        }

        return $this;
    }

    /**
     * Retrieve add variation url
     *
     * @return string
     */
    public function getAddUrl()
    {
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');

        return $this->getUrl('*/*/addVariation', array(
            'id' => $campaign->getId(),
            'tab' => $this->getCurrentTabId()
        ));
    }

    /**
     * Retrieve delete variation url
     *
     * @param Mzax_Emarketing_Model_Campaign_Variation $variation
     *
     * @return string
     */
    public function getRemoveUrl(Mzax_Emarketing_Model_Campaign_Variation $variation = null)
    {
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');

        $params = array('id' => $campaign->getId(), 'tab' => $this->getCurrentTabId());

        if ($variation) {
            $params['variation'] = $variation->getId();
        } else {
            $params['variation'] = 'all';
        }

        return $this->getUrl('*/*/deleteVariation', $params);
    }

    /**
     * @param Mage_Adminhtml_Block_Widget_Tab_Interface $tab
     *
     * @return string
     */
    public function getTabContent($tab)
    {
        $content = $tab->getContent();

        if ($content instanceof Mzax_Emarketing_Model_Campaign) {
            $content = $this->getLayout()
                ->createBlock(self::TAB_BLOCK_CAMPAIGN)
                ->setContent($content)
                ->initForm();
        } elseif ($content instanceof Mzax_Emarketing_Model_Campaign_Variation) {
            $content = $this->getLayout()
                ->createBlock(self::TAB_BLOCK_VARIATION)
                ->setContent($content)
                ->initForm();
        }

        if ($content instanceof Mage_Core_Block_Abstract) {
            $content = $content->toHtml();
        }

        return $content;
    }

    /**
     * @param Mage_Adminhtml_Block_Widget_Tab_Interface $tab
     *
     * @return string
     */
    public function getTabClass($tab)
    {
        $classes[] = parent::getTabClass($tab);

        $content = $tab->getContent();
        if ($content instanceof Mzax_Emarketing_Model_Campaign) {
            $classes[] = "original";
        }
        if ($content instanceof Mzax_Emarketing_Model_Campaign_Variation) {
            $classes[] = "variation";
            if (!$content->getIsActive()) {
                $classes[] = "inactive";
            }
        }
        return implode(' ', $classes);
    }
}
