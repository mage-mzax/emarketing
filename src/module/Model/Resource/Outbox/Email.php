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
 * 
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Resource_Outbox_Email extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * Initiate resources
     *
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/outbox_email', 'email_id');
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
     * Mass delete
     * 
     * @param array $mails
     * @return number
     */
    public function massDelete($mails)
    {
        $rows = $this->_getWriteAdapter()->delete(
            $this->getMainTable(), array('email_id IN(?)' => $mails)
        );
        return $rows;
    }
    
    
    
    /**
     * Mass type change
     * 
     * @param array $emails
     * @param string $type
     * @return number
     */
    public function massTypeChange($emails, $type = null)
    {
        switch($type) {
            case Mzax_Emarketing_Model_Outbox_Email::STATUS_DISCARDED:
            case Mzax_Emarketing_Model_Outbox_Email::STATUS_EXPIRED:
            case Mzax_Emarketing_Model_Outbox_Email::STATUS_FAILED:
            case Mzax_Emarketing_Model_Outbox_Email::STATUS_NOT_SEND:
            case Mzax_Emarketing_Model_Outbox_Email::STATUS_SENT:
                break;
            default:
                $type = null;
        }
    
        $rows = $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('status' => $type),
            array(
                'email_id IN(?)' => $emails,
                'status != ?' => $type
            )
        );
    
        return $rows;
    }
    
    
    
    /**
     * Find a recently sent email to the specified address
     * 
     * @param string $address
     * @param string $date Optional date to look for
     * @param integer $lookBack
     * @return Ambigous <>|NULL
     */
    public function findRecentlySent($address, $date = null, $lookBack = 60)
    {
        $lookBack = max(0, (int) $lookBack);
        $select = $this->_getReadAdapter()->select();
        $select->from($this->getMainTable(), 'email_id');
        $select->where('`to` = ?', $address);
        $select->where("`sent_at` >= DATE_SUB(?, INTERVAL $lookBack MINUTE)", $date ? $date : now());
        
        $result = $this->_getReadAdapter()->fetchCol($select);
        // only return if there is exactly one email
        // don't trust result if there is more then one
        if(count($result) === 1) {
            return $result[0];
        }
        return null;
    }
    
    
    
    

    /**
     * Remove content of old emails that is not required anymore
     *
     * Once messages is sent we don't need to keep the full content
     *
     * However leave the row-entry as it is still relevant for reporting
     *
     * @return $this
     */
    public function purge($purgeDays = 30)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'subject'   => null,
                'body_text' => null,
                'body_html' => null,
                'mail'      => null,
                'log'       => null,
                'purged'    => 1
            ),
            array(
                'purged = 0',
                'created_at < DATE_SUB(NOW(), INTERVAL ? DAY)' => $purgeDays
            )
        );
        return $this;
    }



}