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
 * Class Mzax_Emarketing_LinkController
 */
class Mzax_Emarketing_LinkController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Mzax_Emarketing_Model_SessionManager
     */
    protected $_sessionManager;

    /**
     * @var Mzax_Emarketing_Model_Factory
     */
    protected $_factory;

    /**
     * @var Mzax_Emarketing_Helper_Request
     */
    protected $_requestHelper;

    /**
     * @var Mzax_Emarketing_Helper_Data
     */
    protected $_helper;

    /**
     * Controller Constructor.
     * Load dependencies.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_sessionManager = Mage::getSingleton('mzax_emarketing/sessionManager');
        $this->_factory = Mage::getSingleton('mzax_emarketing/factory');
        $this->_requestHelper = Mage::helper('mzax_emarketing/request');
        $this->_helper = Mage::helper('mzax_emarketing');
    }

    /**
     * Follow link tracking
     *
     * @return void
     */
    public function gotoAction()
    {
        $session = $this->_sessionManager->getSession();

        $follow = $this->getRequest()->getParam('follow');
        $publicId = $this->getRequest()->getParam('hash');

        $linkReference = $this->_factory->createLinkReference();
        $linkReference->load($publicId, 'public_id');

        if (!$linkReference->getId()) {
            // flag bad request
            $attempts = $this->_requestHelper->bad();

            // log every 100th attempt
            if ($attempts % 100 === 0) {
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                $this->_helper->log("Brute force attempt, to many bad requests from '%s' (%s)", $ip, $attempts);
            }

            $this->_redirectUrl('/');
            return;
        }

        // stop right here if we can not trust the request anymore
        if (!$this->_requestHelper->isTrustable()) {
            $this->_redirectUrl('/');
            return;
        }

        $recipient = $linkReference->getRecipient();
        $campaign  = $recipient->getCampaign();

        if ($recipient->isMock() && !$follow) {
            $this->loadLayout('mzax_redirect');

            /** @var Mage_Page_Block_Html $block */
            $block = $this->getLayout()->getBlock('redirect');
            $block->setData('link_reference', $linkReference);
            $block->setData('recipient', $linkReference->getRecipient());
            $block->setData('campaign', $campaign);

            $this->renderLayout();
        } else {
            $linkReference->captureClick($this->getRequest(), $clickId, $eventId);

            $session->addClickReference($linkReference, $clickId);
            $campaign->getRecipientProvider()->linkClicked($linkReference);

            $this->_redirectUrl($linkReference->getTargetUrl());
        }
    }
}
