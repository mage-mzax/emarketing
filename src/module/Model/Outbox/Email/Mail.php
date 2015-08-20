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
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Outbox_Email_Mail
    extends Zend_Mail 
{
    
    
    const UTF8 = 'utf-8';
    
    
    /**
     * 
     * @var Mzax_Emarketing_Model_Outbox_Email
     */
    protected $_outboxEmail;
    
    
    /**
     * 
     * @param Mzax_Emarketing_Model_Outbox_Email $email
     */
    public function __construct()
    {
        parent::__construct(self::UTF8);
    }
    
    
    /**
     * Set outbox email object
     * 
     * @param Mzax_Emarketing_Model_Outbox_Email $email
     * @return Mzax_Emarketing_Model_Outbox_Email_Mail
     */
    public function setOutboxEmail(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $this->_outboxEmail = $email;
        return $this;
    }
    
    
    
    /**
     * Retrieve outbox email object
     * 
     * @return Mzax_Emarketing_Model_Outbox_Email
     */
    public function getOutboxEmail()
    {
        return $this->_outboxEmail;
    }
    
    
    
    /**
     * Retrieve recipient
     * 
     * @return Mzax_Emarketing_Model_Recipient|NULL
     */
    public function getRecipient()
    {
        if($this->_outboxEmail) {
            return $this->_outboxEmail->getRecipient();
        }
        return null;
    }
    
    
}
