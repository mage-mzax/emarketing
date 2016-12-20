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
 * Class Mzax_Emarketing_Model_Recipient_Error
 *
 * @method string getMessage()
 * @method $this setMessage(string $value)
 *
 * @method string getCampaignId()
 * @method $this setCampaignId(string $value)
 *
 * @method string getRecipientId()
 */
class Mzax_Emarketing_Model_Recipient_Error extends Mage_Core_Model_Abstract
{
    /**
     * Model Constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/recipient_error');
    }

    /**
     * @param Exception $error
     *
     * @return $this
     */
    public function setException(Exception $error)
    {
        $this->setMessage("{$error->getMessage()}\n\n{$error->getTraceAsString()}");

        return $this;
    }

    /**
     * set recipient id
     *
     * @param string|integer recipientId
     *
     * @return $this
     */
    public function setRecipientId($recipientId)
    {
        if (($recipient = $this->getData('recipient')) &&  ($recipient->getId() != $recipientId)) {
            $this->getData('recipient', null);
        }
        $this->setData('recipient_id', $recipientId);

        return $this;
    }

    /**
     * set recipient
     *
     * @param Varien_Object $recipient
     *
     * @return $this
     */
    public function setRecipient(Varien_Object $recipient)
    {
        $this->setRecipientId($recipient->getId());
        $this->setData('recipient', $recipient);

        return $this;
    }

    /**
     * Before save
     *
     * @return void
     */
    public function _beforeSave()
    {
        if ($this->getRecipient()) {
            $this->setRecipientId($this->getRecipient()->getId());
            $this->setCampaignId($this->getRecipient()->getCampaignId());
        }

        parent::_beforeSave();
    }

    /**
     * Retrieve recipient
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getRecipient()
    {
        if (!$recipient = $this->getData('recipient')) {
            /** @var Mzax_Emarketing_Model_Recipient $recipient */
            $recipient = Mage::getModel('mzax_emarketing/recipient');
            $recipient->load($this->getRecipientId());
            $this->setData('recipient', $recipient);
        }

        return $recipient;
    }
}
