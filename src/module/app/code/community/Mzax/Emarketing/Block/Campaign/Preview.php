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
 * Class Mzax_Emarketing_Block_Campaign_Preview
 *
 * @method $this setEditorId(string $value)
 * @method $this setError(Exception $value)
 */
class Mzax_Emarketing_Block_Campaign_Preview extends Mage_Adminhtml_Block_Widget
{
    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    /**
     * @var Mzax_Emarketing_Model_Recipient
     */
    protected $_recipient;

    /**
     * @var Mzax_Emarketing_Model_Outbox_Email
     */
    protected $_email;

    /**
     * Prepare preview
     *
     * @return void
     */
    protected function _preparePreview()
    {
        $campaign = $this->getCampaign();

        $this->setTemplate("mzax/emarketing/campaign/medium/{$campaign->getMedium()->getMediumId()}/preview.phtml");

        /** @var Mage_Page_Block_Html_Head $head */
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $content = $this->getRecipient()->getContent();

            if ($content instanceof Mzax_Emarketing_Model_Campaign_Variation) {
                $head->setTitle($this->__('Preview - %s / %s', $campaign->getName(), $content->getName()));
            } else {
                $head->setTitle($this->__('Preview - %s / Original', $campaign->getName(), $content->getName()));
            }
        }
    }

    /**
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return $this
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->_campaign = $campaign;
        $this->_recipient = null;
        $this->_preparePreview();

        return $this;
    }

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     * @throws Exception
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {
            $id = (int) $this->getRequest()->getParam('campaign');

            /* @var $campaign Mzax_Emarketing_Model_Campaign */
            $campaign = Mage::getModel('mzax_emarketing/campaign');
            $campaign->load($id);

            if (!$campaign->getId()) {
                throw new Exception("No campaign found");
            }

            $this->_campaign = $campaign;
        }
        return $this->_campaign;
    }

    /**
     * Retrieve recipient model
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getRecipient()
    {
        if (!$this->_recipient) {
            $objectId = $this->getRequest()->getParam('entity');

            $content = $this->getCampaign();
            // check if we want to preview a certain variation
            if ($variationId = $this->getRequest()->getParam('variation')) {
                $content = $this->getCampaign()->getVariation($variationId);
                $this->setEditorId('variation_' . $content->getId() . '_body');
            } else {
                $this->setEditorId('campaign_body');
            }

            $this->_recipient = $this->getCampaign()->createMockRecipient($objectId);
            $this->_recipient->setContent($content);
        }
        return $this->_recipient;
    }

    /**
     * Retrieve email body
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->getEmail()->getSubject();
    }

    /**
     * Retrieve email body
     *
     * @return string
     */
    public function getBodyHtml()
    {
        return $this->getEmail()->getBodyHtml();
    }

    /**
     * Retrieve email body
     *
     * @return string
     */
    public function getBodyText()
    {
        return $this->getEmail()->getBodyText();
    }

    /**
     * Retrieve link references
     *
     * @return Mzax_Emarketing_Model_Link_Reference[]
     */
    public function getLinkReferences()
    {
        return $this->getEmail()->getLinkReferences();
    }

    /**
     * Prepare recipient and render email in preview mode
     *
     * @return Mzax_Emarketing_Model_Outbox_Email
     */
    public function getEmail()
    {
        if (!$this->_email) {
            /* @var $email Mzax_Emarketing_Model_Outbox_Email */
            $this->_email = Mage::getModel('mzax_emarketing/outbox_email');
            try {
                $recipient = $this->getRecipient();
                $recipient->prepare();

                $this->_email->setTo($recipient->getAddress());
                $this->_email->setRecipient($recipient);
                $this->_email->render(true);
            } catch (Exception $error) {
                $this->setError($error);
            }
        }
        return $this->_email;
    }

    /**
     * Retrieve render time
     *
     * @return float
     */
    public function getRenderTime()
    {
        if ($this->_email) {
            return (float) $this->_email->getRenderTime();
        }
        return 0;
    }

    /**
     * Is ACE editor enabled
     *
     * @return boolean
     */
    public function aceEnabled()
    {
        return Mage::getStoreConfigFlag('mzax_emarketing/content_management/enable_ace');
    }
}
