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
 * Class Mzax_Emarketing_Model_Resource_Conversion_Tracker
 */
class Mzax_Emarketing_Model_Resource_Conversion_Tracker extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initiate resources
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/conversion_tracker', 'tracker_id');
    }

    /**
     * Enable multiple trackers at once
     *
     * @param array $trackerIds
     *
     * @return integer The number of changed trackers
     */
    public function enable($trackerIds)
    {
        return $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
            'is_active'     => 1,
            'is_aggregated' => 0
            ),
            array(
                'is_active != 1',
                'tracker_id IN(?)' => $trackerIds
            )
        );
    }

    /**
     * Disable multiple trackers at once
     *
     * @param array $trackerIds
     *
     * @return integer The number of changed trackers
     */
    public function disable($trackerIds)
    {
        return $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'is_active'     => 0,
                'is_aggregated' => 0
            ),
            array(
                'is_default != 1',
                'is_active != 0',
                'tracker_id IN(?)' => $trackerIds
            )
        );
    }

    /**
     * Flag trackers as aggregated
     *
     * @param array $trackerIds
     *
     * @return integer The number of changed trackers
     */
    public function flagAsAggregated($trackerIds)
    {
        return $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'is_aggregated' => 1
            ),
            array(
                'is_active = 1',
                'is_aggregated != 1',
                'tracker_id IN(?)' => $trackerIds
            )
        );
    }

    /**
     * Delete multiple trackers at once
     *
     * @param array $trackerIds
     *
     * @return integer The number deleted trackers
     */
    public function massDelete($trackerIds)
    {
        return $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array(
                'is_default != 1',
                'tracker_id IN(?)' => $trackerIds
            )
        );
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

        // this can not be changed or updated in the default way
        // use setDefaultTracker instead
        unset($data['is_default']);

        return $data;
    }

    /**
     * Set given tracker id as default tracker
     *
     * @param string $id
     * @throws Exception
     *
     * @return $this
     */
    public function setDefaultTracker($id)
    {
        $adapter = $this->_getWriteAdapter();
        $expr = $adapter->quoteInto("`tracker_id` = ?", $id);

        $adapter->beginTransaction();
        $adapter->update($this->getMainTable(), array('is_default' => new Zend_Db_Expr($expr)));
        $validate = $adapter->fetchOne("SELECT COUNT(`tracker_id`) FROM `{$this->getMainTable()}` WHERE is_default = 1");
        if ($validate != 1) {
            $adapter->rollBack();
            throw new Exception("Unable to set tracker id '$id' as default");
        }
        $adapter->commit();

        return $this;
    }
}
