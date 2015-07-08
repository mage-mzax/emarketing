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


class Mzax_Emarketing_Model_Resource_Recipient_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    /**
     * 
     * 
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    
    
    
    
    protected function _construct()
    {
        $this->_init('mzax_emarketing/recipient');
    }
    
    
    
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        
        // inject campaign if available
        if($this->_campaign) {
            /* @var $recipient Mzax_Emarketing_Model_Recipient */
            foreach($this as $recipient) {
                $recipient->setCampaign($this->_campaign);
            }
        }
    }
    
    
    
    /**
     * Set campaign instance
     * 
     * the collection will only load recipients
     * that match the given campaign, it will also set 
     * the campaign on every recipient
     * 
     * @see Mzax_Emarketing_Model_Resource_Recipient_Collection::_renderFiltersBefore()
     * 
     * @param Mzax_Emarketing_Model_Campaign $campaign
     * @return Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->_campaign = $campaign;
        return $this;
    }
    
    

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        return $this->_campaign;
    }
    
    
    
    

    
    /**
     * Add campaign filter
     */
    protected function _renderFiltersBefore()
    {
        if($this->_campaign) {
            $this->addFilter('campaign_id', $this->_campaign->getId());
        }
    }
    
    
    
    /**
     * Add send filter
     * 
     * @return Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    public function addSendFilter($flag = true)
    {
        if($flag) {
            $this->_select->where("main_table.sent_at IS NOT NULL");
        }
        else {
            $this->_select->where("main_table.sent_at IS NULL");
        }
        
        return $this;
    }
    
    
    
    
    /**
     * Add prepared filter
     *
     * @return Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    public function addPrepareFilter($flag = true)
    {
        if($flag) {
            $this->_select->where("main_table.prepared_at IS NOT NULL");
        }
        else {
            $this->_select->where("main_table.prepared_at IS NULL");
        }
    
        return $this;
    }
    
    
    
    
}