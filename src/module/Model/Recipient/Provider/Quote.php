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
 * Class Mzax_Emarketing_Model_Recipient_Provider_Quote
 */
class Mzax_Emarketing_Model_Recipient_Provider_Quote
    extends Mzax_Emarketing_Model_Recipient_Provider_Abstract
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return "Magento Shopping Carts";
    }

    /**
     * @return Mzax_Emarketing_Model_Object_Quote
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_quote');
    }

    /**
     * Set order default filters
     *
     * @return void
     */
    public function setDefaultFilters()
    {
        parent::setDefaultFilters();

        /* @var $storeFilter Mzax_Emarketing_Model_Object_Filter_Quote_Table */
        $storeFilter = $this->addFilter('quote_table');
        if ($storeFilter && $this->getCampaign()) {
            $storeFilter->setColumn('store_id');
            $storeFilter->setValue($this->getCampaign()->getStoreId());
            $storeFilter->setOperator('()');
        }
    }

    /**
     * @param Mzax_Emarketing_Model_Medium_Email_Snippets $snippets
     *
     * @return void
     */
    public function prepareSnippets(Mzax_Emarketing_Model_Medium_Email_Snippets $snippets)
    {
        parent::prepareSnippets($snippets);

        $snippets->addVar('quote.customer_firstname', 'Customer Firstname', 'Firstname of the customer from the quote');
        $snippets->addVar('quote.customer_lastname', 'Customer Lastname', 'Lastname of the customer from the quote');

        $snippets->addSnippets(
            'mage.cart.products',
            '{{block type="mzax_emarketing/template" area="frontend" template="mzax/email/quote-items.phtml" quote="$quote"}}',
            $this->__('Shopping Cart Products Table'),
            $this->__('Simple table to display the shopping cart products.')
        );

    }

    /**
     * @param Mzax_Emarketing_Model_Recipient $recipient
     *
     * @return void
     */
    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote');
        $quote->loadByIdWithoutStore($recipient->getObjectId());

        $recipient->setQuote($quote);
        $recipient->setEmail($quote->getCustomerEmail());
        $recipient->setName($quote->getCustomerName());

        if ($quote->getCustomerId()) {
            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());

            if ($customer->getId()) {
                $quote->setCustomer($customer);
                $recipient->setCustomer($customer);
                $recipient->setEmail($customer->getEmail());
            }
        }
    }

    /**
     * Every recipient provider gets notified when a link is clicked
     *
     * @param Mzax_Emarketing_Model_Link_Reference $linkReference
     *
     * @return void
     */
    public function linkClicked(Mzax_Emarketing_Model_Link_Reference $linkReference)
    {
        $recipient = $linkReference->getRecipient();

        $this->getSession()->setQuoteId($recipient->getObjectId());

        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->load($recipient->getObjectId());

        if ($quote->getCustomerId()) {
            $this->getSession()->setCustomerId($quote->getCustomerId());
            $recipient->autologin($quote->getCustomerId());
        }
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
     * @param Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder $binder
     *
     * @return void
     */
    public function bindRecipients(Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder $binder)
    {
        if ($binder->hasBinding('customer_id')) {
            $binding = $binder->createBinding();
            $binding->joinTable(array('object_id' => '{customer_id}'), 'recipient');
            $binding->addBinding('campaign_id', 'recipient.campaign_id');
            $binding->addBinding('recipient_id', 'recipient.recipient_id');
            $binding->addBinding('variation_id', 'recipient.variation_id');
            $binding->addBinding('is_mock', 'recipient.is_mock');
            $binding->addBinding('sent_at', 'recipient.sent_at');
        }
        if ($binder->hasBinding('email')) {
            $binding = $binder->createBinding();
            $binding->joinTable(array('address'    => '{email}'), 'recipient_address', 'address');
            $binding->joinTable(array('address_id' => '`address`.`address_id`'), 'recipient');
            $binding->addBinding('campaign_id', 'recipient.campaign_id');
            $binding->addBinding('recipient_id', 'recipient.recipient_id');
            $binding->addBinding('variation_id', 'recipient.variation_id');
            $binding->addBinding('is_mock', 'recipient.is_mock');
            $binding->addBinding('sent_at', 'recipient.sent_at');
        }
    }
}
