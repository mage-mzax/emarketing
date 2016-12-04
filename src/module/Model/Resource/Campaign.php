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
 * Class Mzax_Emarketing_Model_Resource_Campaign
 */
class Mzax_Emarketing_Model_Resource_Campaign extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initiate resources
     *
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/campaign', 'campaign_id');
    }

    /**
     * Count number of recipient errors
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return integer
     */
    public function countRecipientErrors(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('mzax_emarketing/recipient_error'), 'COUNT(error_id)')
            ->where('campaign_id = ?', $campaign->getId())
            ->group('campaign_id');

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Retrieve number of recipients
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return integer
     */
    public function countRecipients(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('mzax_emarketing/recipient'), 'COUNT(recipient_id)')
            ->where('campaign_id = ?', $campaign->getId())
            ->group('campaign_id');

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Check if a campaign has any inbox emails
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return integer
     */
    public function countInbox(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('mzax_emarketing/inbox_email'), 'COUNT(email_id)')
            ->where('campaign_id = ?', $campaign->getId())
            ->group('campaign_id');

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Check if a campaign has any outbox emails
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return integer
     */
    public function countOutbox(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('mzax_emarketing/outbox_email'), 'COUNT(email_id)')
            ->where('campaign_id = ?', $campaign->getId())
            ->group('campaign_id');

        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Find and add new recipients for the specified campaign
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return int The number of recipients found
     * @throws Exception
     */
    public function findRecipients(Mzax_Emarketing_Model_Campaign $campaign)
    {
        if (!$campaign->getId() || !$campaign->getRecipientProvider() || !$campaign->getMedium()) {
            return 0;
        }

        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()->from(array('current_recipients' => $this->getTable('recipient')), 'object_id');
        $select->where('`current_recipients`.`campaign_id` = ?', $campaign->getId());
        $select->where('`current_recipients`.`is_mock` = 0');
        $select->group('current_recipients.object_id');

        $time = $campaign->getCurrentTime();
        if (!$time instanceof Zend_Db_Expr) {
            $time = new Zend_Db_Expr('NOW()');
        }


        if ($interval = (int) $campaign->getMinResendInterval()) {
            // only hide if sent within the last X days
            $select->orHaving("MAX(`current_recipients`.`created_at`) > DATE_SUB($time, INTERVAL ? DAY)", $interval);
        }
        if ($maximum = (int) $campaign->getMaxPerRecipient()) {
            // only hide if sent more than maximum times
            $select->orHaving("COUNT(`current_recipients`.`recipient_id`)  >= ?", $maximum);
        }

        /* skip all already queued recipients */
        $filterSelect = $campaign->getRecipientProvider()->getSelect();
        $filterSelect->exists($select, '`current_recipients`.`object_id` = {id}', false);
        $filterSelect->columns(array(
            'created_at'  => $time,
            'campaign_id' => new Zend_Db_Expr($campaign->getId())
        ));

        $adapter->beginTransaction();

        try {
            $insertSql = $adapter->insertFromSelect(
                $filterSelect,
                $this->getTable('recipient'),
                array('object_id', 'created_at', 'campaign_id')
            );

            $stmt = $adapter->query($insertSql);

            $adapter->update(
                $this->getMainTable(),
                array('last_check' => new Zend_Db_Expr('NOW()')),
                $adapter->quoteInto('campaign_id = ?', $campaign->getId())
            );

            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $stmt->rowCount();
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

        if (empty($data['default_tracker_id'])) {
            $data['default_tracker_id'] = null;
        }

        return $data;
    }
}
