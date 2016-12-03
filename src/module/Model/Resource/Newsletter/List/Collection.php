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
 * Class Mzax_Emarketing_Model_Resource_Newsletter_List_Collection
 */
class Mzax_Emarketing_Model_Resource_Newsletter_List_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    protected function _construct()
    {
        $this->_init('mzax_emarketing/newsletter_list');
    }


    public function addSubscriberCount()
    {
        $expr = new Zend_Db_Expr('COUNT(`s`.`subscriber_id`)');

        $this->getSelect()
             ->group('main_table.list_id')
             ->joinLeft(array('s' => $this->getTable('mzax_emarketing/newsletter_list_subscriber')),
                        $this->getResource()->getReadConnection()->quoteInto(
                                's.list_id = main_table.list_id AND s.list_status = ?',
                                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED),
                        array(
                            'subscriber_count' => $expr
                        ));

        $this->addFilterToMap('subscriber_count', $expr);

        return $this;
    }


    /**
     * Add filter to show only visible lists for a given subscriber
     *
     * Private lists are only visible if subscribed to list
     *
     * @param $subscriberId
     * @return $this
     */
    public function addSubscriberToFilter($subscriber)
    {
        if ($subscriber instanceof Varien_Object) {
            $subscriber = $subscriber->getId();
        }


        $adapter = $this->getResource()->getReadConnection();

        $select = $adapter->select()
            ->from($this->getTable('mzax_emarketing/newsletter_list_subscriber'), 'list_id')
            ->where('subscriber_id = ?', $subscriber)
            ->where('list_status = ?', Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
            ->group('list_id');

        $listIds = $adapter->fetchCol($select);

        if (!empty($listIds)) {
            $this->getSelect()->where('is_private = 0 OR list_id IN(?)', $listIds);

            $this->getSelect()->columns(array(
                'is_subscribed_to' => $adapter->quoteInto(
                    $adapter->getCheckSql('FIND_IN_SET(list_id, ?)', 1, 0), implode(',', $listIds))
            ));
        }
        else {
            $this->getSelect()->where('is_private = 0');
        }

        return $this;
    }


    /**
     * Filter only list that are allowed for specified store
     *
     * @param mixed $store
     * @return $this
     */
    public function addStoreFilter($store)
    {
        $store = Mage::app()->getStore($store)->getId();
        $this->getSelect()->where('FIND_IN_SET(0, `store_ids`) OR FIND_IN_SET(?, `store_ids`)', $store);
        return $this;
    }
    
    
    public function toOptionArray()
    {
        return $this->_toOptionArray('list_id','name');
    }
    
    
    public function toOptionHash()
    {
        return $this->_toOptionHash('list_id','name');
    }
    
}
