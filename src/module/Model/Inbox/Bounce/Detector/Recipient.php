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



class Mzax_Emarketing_Model_Inbox_Bounce_Detector_Recipient
    extends Mzax_Emarketing_Model_Inbox_Bounce_Detector_Abstract
{
    
    
    /**
     * try to find our beacon from mime boundary
     * 
     * @see Mzax_Emarketing_Model_Recipient
     * @var string
     */
    const BOUNDARY_REGEX = '!\=\_MZAX\_\=([a-zA-Z0-9]{30})\_\=!';
    
    
    /**
     * 
     * @see Mzax_Emarketing_Model_Recipient::getBeaconImage()
     * @var string
     */
    const IMAGE_REGEX = '!emarketing-media/([a-zA-Z0-9]{30})/logo\.gif!i';
    
    
    
    /**
     * 
     * @var string
     */
    const LINK_REGEX = '!link-goto/([a-zA-Z0-9]{40})!';
    
    
    
    
    /**
     * Try to detect unique beacon hash
     * 
     * @param Mzax_Bounce_Message $message
     * @return Ambigous <string, boolean>|unknown|boolean
     */
    public function detectBeaconHash(Mzax_Bounce_Message $message)
    {
        $beacon = $message->header('x-campaign-ref');
        if($beacon) {
            return $beacon;
        }
        
        
        $content = $message->getContent();
        
        if(preg_match(self::BOUNDARY_REGEX, $content, $matches)) {
            return $matches[1];
        }
        if(preg_match(self::IMAGE_REGEX, $content, $matches)) {
            return $matches[1];
        }
        
        return false;
    }
    
    
    
    
    /**
     * Try to detect unique link hash
     * 
     * @param Mzax_Bounce_Message $message
     * @return unknown|boolean
     */
    public function detectLinkHash(Mzax_Bounce_Message $message)
    {
        $content = $message->getContent();
        
        if(preg_match(self::LINK_REGEX, $content, $matches)) {
            return $matches[1];
        }
        return false;
    }
    
    
    
    /**
     * Detect original email by references headers
     * 
     * @see http://th-h.de/faq/headerfaq.php#technisches
     * @param Mzax_Bounce_Message $message
     * @return Mzax_Emarketing_Model_Outbox_Email|NULL
     */
    public function detectReferenceEmail(Mzax_Bounce_Message $message)
    {
        /* @var $email Mzax_Emarketing_Model_Outbox_Email */
        $email = Mage::getModel('mzax_emarketing/outbox_email');
        
        // try to load original email by reference header
        foreach(array_reverse($message->getReferences()) as $messageId) {
            $email->loadByMessageId($messageId);
            if($email->getId()) {
                return $email;
            }
        }
        
        // try using in reply header
        $inReplyTo = $message->getHeader('in-reply-to');
        if(preg_match('/<(.+)>/', $inReplyTo, $match)) {
            $email->loadByMessageId($match[1]);
            if($email->getId()) {
                return $email;
            }
        }
        return null;
    }
    
    
    
    /**
     * Try to find an email that has been recently sent
     * to the sender address within the past 60 minutes
     * 
     * This might not be fail proof, but lets see how we go
     * 
     * @param Mzax_Bounce_Message $message
     * @return Mzax_Emarketing_Model_Outbox_Email|NULL
     */
    public function detectRecientlySentEmail(Mzax_Bounce_Message $message)
    {
        $date  = $message->getDate();
        $email = $message->getFrom();
        
        $emailId = Mage::getResourceSingleton('mzax_emarketing/outbox_email')->findRecentlySent($email, $date, 60);
        if($emailId) {
            return Mage::getModel('mzax_emarketing/outbox_email')->load($emailId);
        }
        return null;
    }
    
    
    
    
    
    
    
    /**
     * Try to find orignal recipient
     * 
     * @return Mzax_Emarketing_Model_Recipient|NULL
     */
    public function findRecipient(Mzax_Bounce_Message $message)
    {
        $email = $this->detectReferenceEmail($message);
        if($email) {
            return $email->getRecipient();
        }
        
        $hash = $this->detectBeaconHash($message);
        if($hash) {
            /* @var $recipient Mzax_Emarketing_Model_Recipient */
            $recipient = Mage::getModel('mzax_emarketing/recipient');
            $recipient = $recipient->load($hash, 'beacon_hash');
            if($recipient->getId()) {
                return $recipient;
            }
        }
        
        $hash = $this->detectLinkHash($message);
        if($hash) {
            /* @var $linkRef Mzax_Emarketing_Model_Link_Reference */
            $linkRef = Mage::getModel('mzax_emarketing/link_reference');
            $linkRef->load($hash, 'public_id');
        
            if($linkRef->getRecipientId()) {
                return $linkRef->getRecipient();
            }
        }
        
        
        // last try, check if we have send anything to the sender email recently
        $email = $this->detectRecientlySentEmail($message);
        if($email) {
            return $email->getRecipient();
        }
        
        
        return null;
    }
    
    
    
    
    /**
     * Try to detect the original recipiet id and campaign id
     * 
     * 
     * (non-PHPdoc)
     * @see Mzax_Bounce_Detector_Abstract::inspect()
     */
    public function inspect(Mzax_Bounce_Message $message)
    {
        $recipient = $this->findRecipient($message);
        if($recipient) {
            $recipient->prepare();
            $message->info('recipient_id', $recipient->getId());
            $message->info('campaign_id',  $recipient->getCampaignId());
            $message->info('recipient',    $recipient->getEmail(), 100);
            
            $storeId = Mage::getResourceSingleton('mzax_emarketing/recipient')->getStoreId($recipient->getId());
            if($storeId) {
                $message->info('store_id', $storeId, 100);
            }
            
        }
    }
    
    

}