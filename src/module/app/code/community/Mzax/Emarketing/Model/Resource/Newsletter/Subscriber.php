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
 * Class Mzax_Emarketing_Model_Resource_Newsletter_Subscriber
 *
 * Overwrite magento subscriber to add multi-store support
 */
class Mzax_Emarketing_Model_Resource_Newsletter_Subscriber
    extends Mage_Newsletter_Model_Resource_Subscriber
{
    /**
     * Check if fix is enabled
     *
     * @return bool
     */
    public function allowMultiStoreSupport()
    {
        /** @var Mzax_Emarketing_Model_Config $config */
        $config = Mage::getSingleton('mzax_emarketing/config');

        return $config->flag('mzax_emarketing/general/newsletter_multistore');
    }

    /**
     * Load subscriber from DB by email
     *
     * @param string $subscriberEmail
     * @param mixed $storeId
     *
     * @return array
     */
    public function loadByEmail($subscriberEmail, $storeId = null)
    {
        if (!$this->allowMultiStoreSupport() && is_null($storeId)) {
            return parent::loadByEmail($subscriberEmail);
        }

        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('subscriber_email=:subscriber_email')
            ->where('store_id=:store_id');

        $result = $this->_read->fetchRow($select, array(
            'subscriber_email' => $subscriberEmail,
            'store_id' => Mage::app()->getStore($storeId)->getId()
        ));

        if (!$result) {
            return array();
        }

        return $result;
    }

    /**
     * Load subscriber by customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param mixed $storeId
     *
     * @return array
     */
    public function loadByCustomer(Mage_Customer_Model_Customer $customer, $storeId = null)
    {
        if (!$this->allowMultiStoreSupport() && is_null($storeId)) {
            return parent::loadByCustomer($customer);
        }

        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('customer_id=:customer_id')
            ->where('store_id=:store_id');

        $result = $this->_read->fetchRow($select, array(
            'customer_id' => $customer->getId(),
            'store_id'    => is_null($storeId)
                ? $customer->getStoreId()
                : Mage::app()->getStore($storeId)->getId()
        ));

        if ($result) {
            return $result;
        }

        $result = $this->loadByEmail($customer->getEmail(), $storeId);

        return $result;
    }
}
