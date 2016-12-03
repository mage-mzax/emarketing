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



class Mzax_Emarketing_Model_Resource_Recipient_Event extends Mage_Core_Model_Resource_Db_Abstract
{
    
    /**
     * Initiate resources
     *
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/recipient_event', 'event_id');
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
     * Retrieve all pending rows for which we need to
     * fetch country and region using the IP
     * 
     * @return array
     */
    public function fetchPendingGeoIpRows($expire = 3)
    {
        $now = now();
        
        $select = $this->_getWriteAdapter()->select();
        $select->from(array('e' => $this->getMainTable()));
        $select->joinLeft(array('r' => $this->getTable('recipient')), 'e.recipient_id = r.recipient_id', null);
        $select->joinLeft(array('c' => $this->getTable('campaign')), 'r.campaign_id = c.campaign_id', 'store_id');
        $select->where("captured_at >= DATE_SUB('$now', INTERVAL ? HOUR)", $expire);
        $select->where('country_id IS NULL OR region_id IS NULL');
        
        $result = $this->_getWriteAdapter()->fetchAll($select);
        if (empty($result)) {
            $result = array();
        }
        return $result;
    }
    
    
    
    
    
    /**
     * Insert event record and preform basic data transform
     * 
     * @param array $bind
     * @return integer
     */
    public function insertEvent($bind)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        $id = false;
        try {
            $this->_prepareData($bind);
            $adapter->insert($this->getMainTable(), $bind);
            $id = $adapter->lastInsertId($this->getMainTable());
            $adapter->commit();
        }
        catch(Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
        return $id;
    }
    
    
    
    
    public function updateEvent($eventId, $bind)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $this->_prepareData($bind);
            // don't overwrite capture timestamp
            unset($bind['captured_at']);
            $adapter->update($this->getMainTable(), $bind, array('event_id = ?' => $eventId));
            $adapter->commit();
        }
        catch(Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
    }
    
    
    
    
    protected function _prepareData(&$bind)
    {
        if (isset($bind['useragent'])) {
            $bind['useragent_id'] = $this->getUserAgentId($bind['useragent']);
            unset($bind['useragent']);
        }
        if (!isset($bind['captured_at'])) {
            $bind['captured_at'] = now();
        }
        if (isset($bind['time_offset'])) {
            $bind['time_offset'] = $bind['time_offset'] / 15;
        }
        
        if (isset($bind['ip'])) {
            $bind['ip'] = @inet_pton($bind['ip']);
        }
    }
    
    
    
    
    /**
     * Update the time offset value for the given recipient
     * 
     * @param integer $offset
     * @param string|array $recipientId
     * @return Mzax_Emarketing_Model_Resource_Recipient_Event
     */
    public function updateTimeOffset($offset, $recipientId)
    {
        $this->_getWriteAdapter()
            ->update($this->getMainTable(), 
                     array('time_offset' => round($offset/15)), 
                     array('`recipient_id` IN(?)' => $recipientId, 
                           '`time_offset` IS NULL'));
        return $this;
    }
    
    
    
    

    
}
