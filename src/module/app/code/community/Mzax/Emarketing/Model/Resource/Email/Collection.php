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
 * Email Collection abstract
 *
 * Used by inbox and outbox
 * Allow to assign the correct campaign/recipient models to each email item
 */
abstract class Mzax_Emarketing_Model_Resource_Email_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Filter by ids
     *
     * @param array $ids
     *
     * @return $this
     */
    public function addIdFilter(array $ids)
    {
        $this->addFieldToFilter('email_id', array('IN' => $ids));

        return $this;
    }

    /**
     * Assign any model instance using the provided id
     *
     * @param string $model
     * @param string $idField Optional id field, default is $mode . '_id'
     *
     * @return $this
     */
    protected function _assignModel($model, $idField = null)
    {
        // only run ones
        if ($this->getFlag($model . '_assigned')) {
            return $this;
        }

        if (!$idField) {
            $idField = $model . '_id';
        }

        $ids = array_unique($this->getColumnValues($idField));

        /* @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('mzax_emarketing/'.$model)->getCollection();
        $collection->addFieldToFilter($idField, array('in' => $ids));

        /* @var $email Mzax_Emarketing_Model_Email */
        foreach ($this as $email) {
            if ($id = $email->getData($idField)) {
                if ($item = $collection->getItemById($id)) {
                    $email->setDataUsingMethod($model, $item);
                }
            }
        }

        $this->setFlag($model . '_assigned', true);

        return $this;
    }

    /**
     * Assign recipients module on each email item
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function assignRecipients($flag = true)
    {
        if (!$this->isLoaded()) {
            return $this->setFlag('assign_recipients', $flag);
        }
        return $this->_assignModel('recipient');
    }

    /**
     * Assign campaign module on each email item
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function assignCampaigns($flag = true)
    {
        if (!$this->isLoaded()) {
            return $this->setFlag('assign_campaigns', $flag);
        }
        return $this->_assignModel('campaign');
    }

    /**
     * Check for assignments after load
     *
     * @see Mage_Core_Model_Resource_Db_Collection_Abstract::_afterLoad()
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('assign_campaigns')) {
            $this->assignCampaigns();
        }
        if ($this->getFlag('assign_recipients')) {
            $this->assignCampaigns();
        }
    }

    /**
     * @see Varien_Data_Collection::toOptionArray()
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('email_id', 'message_id');
    }

    /**
     *@see Varien_Data_Collection::toOptionHash()
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('email_id', 'message_id');
    }
}
