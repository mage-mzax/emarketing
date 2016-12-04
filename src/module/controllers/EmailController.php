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




class Mzax_Emarketing_EmailController extends Mage_Core_Controller_Front_Action
{
    
    
    public function indexAction()
    {
        $recipientId = $this->getSession()->getLastRecipientId();
        
        if (!$recipientId) {
            return $this->_redirectUrl('/');
        }
        
        $email = Mage::getSingleton('mzax_emarketing/outbox')->getEmailByRecipient($recipientId);
        if (!$email->getId() || $email->isPurged()) {
            return $this->_redirectUrl('/');
        }
        
        $this->getResponse()->setBody($email->getBodyHtml());
        
        
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
