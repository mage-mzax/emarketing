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
 * Class Mzax_Emarketing_Model_Resource_Link_Reference
 */
class Mzax_Emarketing_Model_Resource_Link_Reference extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initiate resources
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/link_reference', 'reference_id');
    }

    /**
     * Save object object data
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        try {
            return parent::save($object);
        } catch (Zend_Db_Statement_Exception $e) {
            //1062 Duplicate entry 'Tw9xIIhhyIWnK1EX8UXRaeYxI1e6wXU3gfJvZb2z' for key 'UNQ_PUBLIC_ID'"
            if (strpos($e->getMessage(), '1062') && strpos($e->getMessage(), 'UNQ_PUBLIC_ID')) {
                $object->setPublicId($object->makePublicKey($object->getLink()));
                return $this->save($object);
            }
        }

        return $this;
    }

    /**
     * Retrieve last id
     *
     * @internal
     *
     * @return int
     */
    public function getLastId()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->order('reference_id DESC')
            ->limit(1);

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Load public id for the given link and recipient
     *
     * @param Mzax_Emarketing_Model_Link_Reference $object
     * @param string $linkId
     * @param string $recipientId
     *
     * @return Mzax_Emarketing_Model_Resource_Link_Reference
     */
    public function loadPublicId(Mzax_Emarketing_Model_Link_Reference $object, $linkId, $recipientId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.`recipient_id`=?', $recipientId)
            ->where($this->getMainTable().'.`link_id`=?', $linkId);

        $data = $this->_getReadAdapter()->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Log click to link reference and return click id
     *
     * @param Mzax_Emarketing_Model_Link_Reference $reference
     * @param integer $eventId Optional event id
     *
     * @return integer click id
     */
    public function captureClick(Mzax_Emarketing_Model_Link_Reference $reference, $eventId = null)
    {
        if ($reference->getId()) {
            return 0;
        }

        $adapter = $this->_getWriteAdapter();
        $adapter->insert($this->getTable('link_reference_click'), array(
            'reference_id' => $reference->getId(),
            'event_id'     => $eventId ? $eventId : null,
            'clicked_at'   => now()
        ));
        $id = $adapter->lastInsertId($this->getTable('link_reference_click'));

        return $id;
    }
}
