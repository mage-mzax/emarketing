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
class Mzax_Emarketing_Model_Recipient_Error extends Mage_Core_Model_Abstract 
{

    
    protected function _construct()
    {
        $this->_init('mzax_emarketing/recipient_error');
    }
    
    
    
    
    public function setException(Exception $error)
    {
        $this->setMessage("{$error->getMessage()}\n\n{$error->getTraceAsString()}");
        return $this;
    }
    

    /**
     * set recipient id
     * 
     * @param string|integer recipientId
     * @return Mzax_Emarketing_Model_Recipient_Error
     */
    public function setRecipientId($recipientId)
    {
        if(($recipient = $this->getData('recipient')) && 
           ($recipient->getId() != $recipientId)) {
            $this->getData('recipient', null);
        }
        $this->setData('recipient_id', $recipientId);
        return $this;
    }
    
    
    
    
    /**
     * set recipient
     * 
     * @param Varien_Object $recipient
     * @return Mzax_Emarketing_Model_Recipient_Error
     */
    public function setRecipient(Varien_Object $recipient)
    {
        $this->setRecipientId($recipient->getId());
        $this->setData('recipient', $recipient);
        
        return $this;
    }
    
    
    
    /**
     * (non-PHPdoc)
     * @see magento/app/code/core/Mage/Core/Model/Mage_Core_Model_Abstract#_beforeSave()
     */
    public function _beforeSave()
    {
        if($this->getRecipient()) {
            $this->setRecipientId($this->getRecipient()->getId());
            $this->setCampaignId($this->getRecipient()->getCampaignId());
        }
    }
    
    /**
     * Retrieve recipient
     * 
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getRecipient()
    {
        if(!$recipient = $this->getData('recipient')) {
            $recipient = Mage::getModel('mzax_emarketing/recipient')->load($this->getRecipientId());
            $this->setData('recipient', $recipient);
        }
        return $recipient;
    }
    
    
    
}
