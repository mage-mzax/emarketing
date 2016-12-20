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
 * Class Mzax_Emarketing_Model_Email
 *
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $value)
 *
 * @method string getMessageId()
 * @method $this setMessageId(string $value)
 *
 * @method string getCampaignId()
 * @method $this setCampaignId(string $value)
 *
 * @method string getRecipientId()
 * @method $this setRecipientId(string $value)
 *
 * @method string getStoreId()
 * @method $this setStoreId(string $value)
 */
abstract class Mzax_Emarketing_Model_Email
    extends Mage_Core_Model_Abstract
{
    /**
     * Recipient
     *
     * @var Mzax_Emarketing_Model_Recipient
     */
    protected $_recipient;

    /**
     * Campaign
     *
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    /**
     * Before save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        if ($this->_campaign) {
            $this->setCampaignId($this->_campaign->getId());
        }
        if ($this->_recipient) {
            $this->setRecipientId($this->_recipient->getId());
        }
        parent::_beforeSave();

        return $this;
    }

    /**
     * Set campaign
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return $this
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->_campaign = $campaign;
        if ($this->_recipient) {
            $this->_recipient->setCampaign($campaign);
        }

        return $this;
    }

    /**
     * Retrieve campaign if available
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign && $this->getCampaignId()) {
            $this->_campaign = Mage::getModel('mzax_emarketing/campaign');
            $this->_campaign->load($this->getCampaignId());
        }

        return $this->_campaign;
    }

    /**
     * Set recipient
     *
     * @param Mzax_Emarketing_Model_Recipient $recipient
     *
     * @return $this
     */
    public function setRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $this->_recipient = $recipient;
        if ($this->_campaign) {
            $this->_recipient->setCampaign($this->_campaign);
        } else {
            $this->_campaign = $recipient->getCampaign();
        }

        return $this;
    }

    /**
     * Retrieve recipient if available
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getRecipient()
    {
        if (!$this->_recipient && $this->getRecipientId()) {
            $this->_recipient = Mage::getModel('mzax_emarketing/recipient');
            $this->_recipient->load($this->getRecipientId());
        }

        return $this->_recipient;
    }

    /**
     * Retrieve store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->_campaign) {
            return $this->_campaign->getStore();
        }

        return Mage::app()->getStore($this->getStoreId());
    }

    /**
     * Load email by message id
     *
     * @param string $messageId
     * @return $this
     */
    public function loadByMessageId($messageId)
    {
        $this->getResource()->load($this, $messageId, 'message_id');

        return $this;
    }
}
