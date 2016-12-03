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
 * Class Mzax_Emarketing_Model_Resource_Newsletter_List
 */
class Mzax_Emarketing_Model_Resource_Newsletter_List
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Initiate resources
     *
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/newsletter_list', 'list_id');
    }




    /**
     * Prepare data for save
     *
     * @param   Mage_Core_Model_Abstract $object
     * @return  array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt(now());
        }
        $object->setUpdatedAt(now());
        $data = parent::_prepareDataForSave($object);
        return $data;
    }



    /**
     * Add all current subscriber to list
     *
     * @param $list
     * @return int
     */
    public function addAllSubscribers($list)
    {
        if ( $list instanceof Varien_Object ) {
            $list = $list->getId();
        }
        $list = (int) $list;

        if ($list) {
            $adapter = $this->_getWriteAdapter();

            $select = $adapter->select()
                ->from($this->getSubscriberTable(), null)
                ->columns(array(
                    'list_id'       => new Zend_Db_Expr($list),
                    'subscriber_id' => 'subscriber_id',
                    'changed_at'    => new Zend_Db_Expr('NOW()'),
                    'list_status'   => new Zend_Db_Expr(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
                ));

            $sql = $adapter->insertFromSelect($select, $this->getListSubscriberTable(), array(), $adapter::INSERT_ON_DUPLICATE);
            return $adapter->query($sql)->rowCount();
        }
        return 0;
    }




    /**
     * Add subscriber to list
     *
     * @param $list
     * @return integer
     */
    public function addSubscribers($list, array $subscribers)
    {
        if ( $list instanceof Varien_Object ) {
            $list = $list->getId();
        }
        $list = (int) $list;

        if ($list) {
            $adapter = $this->_getWriteAdapter();

            $select = $adapter->select()
                ->from($this->getSubscriberTable(), null)
                ->where('subscriber_id IN(?)', $subscribers)
                ->columns(array(
                    'list_id'       => new Zend_Db_Expr($list),
                    'subscriber_id' => 'subscriber_id',
                    'changed_at'    => new Zend_Db_Expr('NOW()'),
                    'list_status'   => new Zend_Db_Expr(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
                ));

            $sql = $adapter->insertFromSelect($select, $this->getListSubscriberTable(), array(), $adapter::INSERT_ON_DUPLICATE);
            return $adapter->query($sql)->rowCount();
        }
        return 0;
    }






    /**
     * Add all current subscriber to list
     *
     * @param $list
     * @return int
     */
    public function removeAllSubscribers($list)
    {
        if ( $list instanceof Varien_Object ) {
            $list = $list->getId();
        }
        $list = (int) $list;

        if ($list) {
            $adapter = $this->_getWriteAdapter();

            $select = $adapter->select()
                ->from($this->getListSubscriberTable(), null)
                ->where('list_id = ?', $list)
                ->where('list_status != ?', Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)
                ->columns(array(
                    'list_id'       => new Zend_Db_Expr($list),
                    'subscriber_id' => 'subscriber_id',
                    'changed_at'    => new Zend_Db_Expr('NOW()'),
                    'list_status'   => new Zend_Db_Expr(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)
                ));

            $sql = $adapter->insertFromSelect($select, $this->getListSubscriberTable(), array(), $adapter::INSERT_ON_DUPLICATE);
            return $adapter->query($sql)->rowCount();
        }
        return 0;
    }



    /**
     * Subscribe all current subscriber to list
     *
     * @param $list
     * @return int
     */
    public function removeSubscribers($list, array $subscribers)
    {
        if ( $list instanceof Varien_Object ) {
            $list = $list->getId();
        }
        $list = (int) $list;

        if ($list) {
            $adapter = $this->_getWriteAdapter();

            $select = $adapter->select()
                ->from($this->getListSubscriberTable(), null)
                ->where('list_id = ?', $list)
                ->where('list_status != ?', Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)
                ->where('subscriber_id IN(?)', $subscribers)
                ->columns(array(
                    'list_id'       => new Zend_Db_Expr($list),
                    'subscriber_id' => 'subscriber_id',
                    'changed_at'    => new Zend_Db_Expr('NOW()'),
                    'list_status'   => new Zend_Db_Expr(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)
                ));

            $sql = $adapter->insertFromSelect($select, $this->getListSubscriberTable(), array(), $adapter::INSERT_ON_DUPLICATE);
            return $adapter->query($sql)->rowCount();
        }
        return 0;
    }






    /**
     * Subscribe subscriber to all auto-subscriber lists
     *
     * @param $subscriber
     * @return $this
     */
    public function subscribeToAutoLists($subscriber)
    {
        if ($subscriber instanceof Varien_Object) {
            $subscriber = $subscriber->getId();
        }

        $subscriber = (int) $subscriber;

        if ($subscriber) {
            $adapter = $this->_getReadAdapter();

            $select = $adapter->select()
                ->from($this->getMainTable(), null)
                ->where('auto_subscribe = 1')
                ->columns(array(
                    'list_id'       => 'list_id',
                    'subscriber_id' => new Zend_Db_Expr($subscriber),
                    'changed_at'    => new Zend_Db_Expr('NOW()'),
                    'list_status'   => new Zend_Db_Expr(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
                ));

            $sql = $adapter->insertFromSelect($select, $this->getListSubscriberTable(), array(), $adapter::INSERT_IGNORE);
            $adapter->query($sql);
        }

        return $this;
    }





    /**
     * Retrieve list subscriber table
     *
     * @return string
     */
    public function getListSubscriberTable()
    {
        return $this->getTable('mzax_emarketing/newsletter_list_subscriber');
    }



    /**
     * Retrieve magentos newsletter subscriber table
     *
     * @return string
     */
    public function getSubscriberTable()
    {
        return $this->getTable('newsletter/subscriber');
    }

}
