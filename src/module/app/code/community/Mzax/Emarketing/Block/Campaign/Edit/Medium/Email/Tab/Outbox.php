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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Medium_Email_Tab_Outbox
 */
class Mzax_Emarketing_Block_Campaign_Edit_Medium_Email_Tab_Outbox extends Mzax_Emarketing_Block_Outbox_Grid
{
    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {
            $campaignId = (int) $this->getRequest()->getParam('id');

            $this->_campaign = Mage::getModel('mzax_emarketing/campaign');
            $this->_campaign->load($campaignId);
        }

        return $this->_campaign;
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->getCollection()->addFieldToFilter('campaign_id', $this->getCampaign()->getId());
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('campaign');

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/campaignGrid', array('grid_ajax' => 1, '_current'=> true));
    }

    /**
     * @param Mzax_Emarketing_Model_Outbox_Email $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/emarketing_outbox/email', array('id'=>$row->getId()));
    }

    /**
     * @return bool
     */
    public function canDisplayContainer()
    {
        if ($this->getRequest()->getParam('grid_ajax')) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    protected function getAdditionalJavascript()
    {
        return "window.{$this->getId()}_massactionJsObject = {$this->getId()}_massactionJsObject;";
    }
}
