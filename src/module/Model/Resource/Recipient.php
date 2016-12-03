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



class Mzax_Emarketing_Model_Resource_Recipient extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * Initiate resources
     *
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/recipient', 'recipient_id');
    }
    
    
    
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        // get or create address id
        if (!$object->getAddressId() && $object->getAddress()) {
            $object->setAddressId($this->getAddressId($object->getAddress()));
        }
        
        if (!$object->getId()) {
            $object->setCreatedAt(now());
        }
        
        return parent::_prepareDataForSave($object);
    }

    
    
    /**
     * Retrieve store id by recipient id
     * 
     * @param string $recipientId
     * @return string
     */
    public function getStoreId($recipientId)
    {
        $select = $this->getReadConnection()->select()->from(array('r' => $this->getMainTable()), null);
        $select->joinInner(array('c' => $this->getTable('campaign')), '`r`.`campaign_id` = `c`.`campaign_id`', 'store_id');
        $select->where('r.recipient_id = ?', $recipientId);
        
        return $this->getReadConnection()->fetchOne($select);
    }
    
    
    
    
    /**
     * Retreive address id
     *
     * @see Mzax_Emarketing_Model_Resource_Recipient_Address
     * @param string $email
     * @return number
     */
    public function getAddressId($address)
    {
        return Mage::getResourceSingleton('mzax_emarketing/recipient_address')->getAddressId($address);
    }
    
    
    
    
    
    /**
     * Retreive user agent id
     * 
     * @see Mzax_Emarketing_Model_Resource_Useragent
     * @param string $useragent
     * @return number
     */
    public function getUserAgentId($useragent)
    {
        return Mage::getResourceSingleton('mzax_emarketing/useragent')->getUserAgentId($useragent);
    }
    
    
    
    /**
     * Insert new event
     * 
     * Return event id
     * 
     * @param array $bind
     * @return integer EventID
     */
    public function insertEvent($bind)
    {
        return Mage::getResourceSingleton('mzax_emarketing/recipient_event')->insertEvent($bind);
    }
    

    
    
    /**
     * Remove pending recipients
     * 
     * Note: You should notfiy the medium about any deleted recipients
     * 
     * @param string $campaginId
     * @return Mzax_Emarketing_Model_Resource_Recipient
     */
    public function removePending($campaginId = null)
    {
        $where = array('sent_at IS NULL' , 'is_mock = 0');
        if ($campaginId) {
            $where['campaign_id = ?'] = $campaginId;
        }
        
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        return $this;
    }
    
    
    
    /**
     * Retrieve the number of recipients
     * 
     * @return number
     */
    public function countRecipients()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'COUNT(*)');
        
        return (int) $this->_getReadAdapter()->fetchOne($select);
    }
    
    
}
