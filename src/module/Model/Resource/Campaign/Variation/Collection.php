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


class Mzax_Emarketing_Model_Resource_Campaign_Variation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    
    protected function _construct()
    {
        $this->_init('mzax_emarketing/campaign_variation');
    }
    
    
    
    protected function _renderFilters()
    {
        if ($this->_isFiltersRendered) {
            return $this;
        }
        parent::_renderFilters();
        
        $this->_select->where('is_removed = 0');
        
        return $this;
    }
    
    
    
    
    /**
     * Filter queues by campaign
     *
     * @param mixed $campaign campain to filter by, id or model
     * @return Mzax_Emarketing_Model_Resource_Queue_Collection
     */
    public function addCampaignFilter($campaign)
    {
        if ($campaign instanceof Varien_Object) {
            $campaign = $campaign->getId();
        }
    
        $this->addFieldToFilter('campaign_id', array('eq' => $campaign));
        return $this;
    }
    
    
    
    
    
    public function toOptionArray()
    {
        return $this->_toOptionArray('variation_id','name');
    }
    
    
    public function toOptionHash()
    {
        return $this->_toOptionHash('variation_id','name');
    }
    
}
