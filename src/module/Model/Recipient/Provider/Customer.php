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


class Mzax_Emarketing_Model_Recipient_Provider_Customer
    extends Mzax_Emarketing_Model_Recipient_Provider_Abstract 
{
    
    
    
    
    public function getTitle()
    {
        return "Magento Customers";
    }
    
    
    
    /**
     * 
     * @return Mzax_Emarketing_Model_Object_Customer
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_customer');
    }
    
    
    
    
    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $customer = Mage::getModel('customer/customer')->load($recipient->getObjectId());
                
        $recipient->setCustomer($customer);
        $recipient->setEmail($customer->getEmail());
        $recipient->setName($customer->getName());
    }
    
    


    /**
     * Every recipient provider gets notified when a link is clicked
     *
     * @param Mzax_Emarketing_Model_Link_Reference $linkReference
     */
    public function linkClicked(Mzax_Emarketing_Model_Link_Reference $linkReference)
    {
    
        $this->getSession()->setCustomerId($linkReference->getRecipient()->getObjectId());
    
        $linkReference->getRecipient()->autologin($linkReference->getRecipient()->getObjectId());
    }
    
    
    
    
    /**
     * Help to bind recipients to provider
     * 
     * It is not straight forwared to link recipients to customers
     * or order to customer or any other address provider dynamically.
     * 
     * There for you can use the binding object to define binding ports available
     * and then have the provider look for any possible ports it can handle
     * 
     * @return void
     */
    public function bindRecipients(Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder $binder)
    {
        
        if($binder->hasBinding('customer_id')) {
            $binder->createBinding()
                ->joinTable(array('object_id' => '{customer_id}'), 'recipient')
                ->addBinding('campaign_id',  'recipient.campaign_id')
                ->addBinding('recipient_id', 'recipient.recipient_id')
                ->addBinding('variation_id', 'recipient.variation_id')
                ->addBinding('is_mock',      'recipient.is_mock')
                ->addBinding('sent_at',      'recipient.sent_at');
        }
        
        
        if($binder->hasBinding('email')) {
            $binder->createBinding()
                ->joinTable(array('address'    => '{email}'), 'recipient_address', 'address')
                ->joinTable(array('address_id' => '`address`.`address_id`'), 'recipient')
                ->addBinding('campaign_id',  'recipient.campaign_id')
                ->addBinding('recipient_id', 'recipient.recipient_id')
                ->addBinding('variation_id', 'recipient.variation_id')
                ->addBinding('is_mock',      'recipient.is_mock')
                ->addBinding('sent_at',      'recipient.sent_at');
        }
        
    }
    
}
