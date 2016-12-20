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
 * Class Mzax_Emarketing_Model_Recipient_Provider_Newsletter
 */
class Mzax_Emarketing_Model_Recipient_Provider_Newsletter
    extends Mzax_Emarketing_Model_Recipient_Provider_Abstract
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return "Magento Newsletter";
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Object_Subscriber
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_subscriber');
    }

    /**
     * @param Mzax_Emarketing_Model_Recipient $recipient
     *
     * @return void
     */
    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        parent::prepareRecipient($recipient);

        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = $recipient->getObject();
        $recipient->setSubscriber($subscriber);
        $recipient->setEmail($subscriber->getEmail());

        if ($subscriber->getCustomerId()) {
            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            $recipient->setCustomer($customer);
            $recipient->setName($customer->getName());
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

        $this->getSession()->setSubscriberId($recipient->getObjectId());

        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber')->load($recipient->getObjectId());

        if ($subscriber->getCustomerId()) {
            $this->getSession()->setCustomerId($subscriber->getCustomerId());
            $recipient->autologin($subscriber->getCustomerId());
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
            $binding->joinTable(array('customer_id' => '{customer_id}'), 'newsletter/subscriber', 'subscriber');
            $binding->joinTable(array('object_id'   => '`subscriber`.`subscriber_id`'), 'recipient');
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
