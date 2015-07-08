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
 * Email Inbox Facade
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Inbox
{

    
    
    /**
     * Retrieve new email messages
     * 
     * @return Mzax_Emarketing_Model_Inbox
     */
    public function downloadEmails()
    {
        if(!Mage::getStoreConfigFlag('mzax_emarketing/inbox/enable')) {
            return;
        }
        
        $lock = Mage::helper('mzax_emarketing')->lock('download_emails');
        if(!$lock) {
            return $this;
        }
        
        
        
        /* @var $collector Mzax_Emarketing_Model_Inbox_Email_Collector */
        $collector = Mage::getModel('mzax_emarketing/inbox_email_collector');
        $collector->collect();
        
        $lock->unlock();
        
        return $this;
    }
    
    
    

    /**
     * Parse email messages
     *
     * @return Mzax_Emarketing_Model_Inbox
     */
    public function parseEmails()
    {
        $lock = Mage::helper('mzax_emarketing')->lock('parse_emails');
        if(!$lock) {
            return $this;
        }
                
        /* @var $collection Mzax_Emarketing_Model_Resource_Inbox_Email_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/inbox_email_collection');
        $collection->addFieldToFilter('is_parsed', 0);
        
        /* @var $email Mzax_Emarketing_Model_Inbox_Email */
        foreach($collection as $email) {
            $email->parse();
            $lock->touch();
        }
        
        $lock->unlock();
        return $this;
    }
    
    
    
    
    
}
