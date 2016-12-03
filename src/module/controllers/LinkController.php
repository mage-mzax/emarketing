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
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_LinkController extends Mage_Core_Controller_Front_Action
{
	
    
    /**
     * Follow link tracking
     * 
     */
    public function gotoAction()
    {
        $follow   = $this->getRequest()->getParam('follow');
        $publicId = $this->getRequest()->getParam('hash');
        
        
        /* @var $linkReference  Mzax_Emarketing_Model_Link_Reference */
        $linkReference = Mage::getModel('mzax_emarketing/link_reference')->load($publicId, 'public_id');
        
        if (!$linkReference->getId()) {
            // flag bad request
            $attempts = Mage::helper('mzax_emarketing/request')->bad();
            if ($attempts % 100 === 0) {
                $ip = $this->getRequest()->getServer('REMOTE_ADDR');
                Mage::helper('mzax_emarketing')
                    ->log("Brute force attempt, to many bad requests from '%s' (%s)", $ip, $attempts);
            }
            return $this->_redirectUrl('/');
        }
        
        // stop right here if we can not trust the request anymore
        if (!Mage::helper('mzax_emarketing/request')->isTrustable()) {
            return $this->_redirectUrl('/');
        }
        
        
        $recipient = $linkReference->getRecipient();
        $campaign  = $recipient->getCampaign();
        
        if ($recipient->isMock() && !$follow) 
        {
            $this->loadLayout('mzax_redirect');
            $block = $this->getLayout()->getBlock('redirect');
            $block->setLinkReference($linkReference);
            $block->setRecipient($linkReference->getRecipient());
            $block->setCampaign($campaign);
            $this->renderLayout();
        }
        else {
            $linkReference->captureClick($this->getRequest(), $clickId, $eventId);
                
            $this->getSession()->addClickReference($linkReference, $clickId);
            $campaign->getRecipientProvider()->linkClicked($linkReference);
            
            $this->_redirectUrl($linkReference->getTargetUrl());
        }
    }
    
    
    
    
    /**
     * Retrieve session model
     * 
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mzax_emarketing/session');
    }
    
}
