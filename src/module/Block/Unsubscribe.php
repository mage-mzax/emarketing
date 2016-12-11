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
 * Class Mzax_Emarketing_Block_Unsubscribe
 */
class Mzax_Emarketing_Block_Unsubscribe extends Mage_Core_Block_Template
{
    /**
     * Retrieve session model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mzax_emarketing/session');
    }

    /**
     * Retrieve address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getSession()->getLastAddress();
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->getSession()->getFormKey();
    }

    /**
     * @return Mzax_Emarketing_Model_Resource_Newsletter_List_Collection
     */
    public function getCustomerNewsletterLists()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber');
        $subscriber->loadByCustomer($customer);

        /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
        $collection->addSubscriberToFilter($subscriber);
        $collection->addStoreFilter(Mage::app()->getStore());

        return $collection;
    }

    /**
     * @return Mzax_Emarketing_Model_Resource_Newsletter_List_Collection
     */
    public function getNewsletterLists()
    {
        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber');
        $subscriber->loadByEmail($this->getAddress());

        /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
        $collection->addSubscriberToFilter($subscriber);
        $collection->addStoreFilter(Mage::app()->getStore());

        return $collection;
    }

    /**
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/update');
    }

    /**
     * @return string
     */
    public function getYesUrl()
    {
        return $this->getUrl('*/*/do');
    }

    /**
     * @return string
     */
    public function getNoUrl()
    {
        return $this->getUrl('/');
    }
}
