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



class Mzax_Emarketing_Model_Report_Aggregator_Tracker
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    
    

    protected $_reportTable = 'report_conversion';
    
    
    
    
    
    protected function _aggregate()
    {
        $this->_lastRecordTime = null;
        
        if($this->getOption('full', false)) {
            $this->truncateTable();
        }
        else {
            if($trackerId = $this->getOption('tracker_id')) {
                $this->delete(array('`tracker_id` IN(?)' => $trackerId));
            }
            if($campaignId = $this->getOption('campaign_id')) {
                $this->delete(array('`campaign_id` IN(?)' => $campaignId));
            }
            if($incremental = abs($this->getOption('incremental'))) {
                $this->delete(array("`date` >= DATE_SUB(?, INTERVAL $incremental DAY)" => $this->getLastRecordTime()));
            }
        }
        $this->aggregateConversionReport();
    }
    
    
    
    
    
    
    /**
     * Aggregate the goals for the conversion report
     * which aggregates the goals by each day
     * 
     * @return void
     */
    public function aggregateConversionReport()
    {
        $adapter = $this->_getWriteAdapter();
        
        
        // aggregate all goals
        $select = $this->_select('conversion_tracker_goal', 'goal');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->addBinding('campaign_id', 'recipient.campaign_id');
        $select->addBinding('store_id', 'campaign.store_id');
        $select->addBinding('date_filter', 'goal_time');
        $select->group(array('goal.campaign_id', 'goal.tracker_id', 'DATE(`goal`.`goal_time`)'));
        $select->columns(array(
            'campaign_id'   => 'goal.campaign_id',
            'variation_id'  => new Zend_Db_Expr(-1),
            'date'          => $this->getLocalTimeSql('`goal`.`goal_time`'),
            'tracker_id'    => 'goal.tracker_id',
            'hits'          => 'COUNT(`goal`.`goal_id`)',
            'hit_revenue'   => 'SUM(`goal`.`goal_value`)',
        ));
        
        $select->filter('goal.campaign_id', $this->_options->getCampaignId());
        $select->filter('goal.tracker_id', $this->_options->getTrackerId());
        
        $this->applyDateFilter($select);
        
        $this->insertSelect($select);
        
        
        
        
        
        // aggregate goals for each variation
        $select = $this->_select('conversion_tracker_goal', 'goal');
        
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->addBinding('campaign_id', 'recipient.campaign_id');
        $select->addBinding('store_id', 'campaign.store_id');
        $select->addBinding('date_filter', 'goal_time');
        
        //$select->join($this->_getTable('recipient', 'recipient'), '`recipient`.`recipient_id` = `goal`.`recipient_id`', null);
        $select->where('`recipient`.`variation_id` >= 0');
        $select->group(array('goal.campaign_id', 'recipient.variation_id', 'goal.tracker_id', 'DATE(`goal`.`goal_time`)'));
        $select->columns(array(
            'campaign_id'   => 'goal.campaign_id',
            'variation_id'  => 'recipient.variation_id',
            'date'          => $this->getLocalTimeSql('`goal`.`goal_time`'),
            'tracker_id'    => 'goal.tracker_id',
            'hits'          => 'COUNT(`goal`.`goal_id`)',
            'hit_revenue'   => 'SUM(`goal`.`goal_value`)',
        ));
        
        $select->filter('goal.campaign_id', $this->_options->getCampaignId());
        $select->filter('goal.tracker_id', $this->_options->getTrackerId());
        
        $this->applyDateFilter($select);
        
        $this->insertSelect($select);
        
        
    }
    
    
    
    
    
    
    protected function _getLastRecordTime()
    {
        $adapter = $this->_getWriteAdapter();
        
        $lastDateSelect = $this->_select($this->_reportTable, null, array('date' => 'MAX(`date`)'))
            ->filter('campaign_id', $this->_options->getCampaignId())
            ->filter('tracker_id', $this->_options->getTrackerId())
            ->group('tracker_id')
            ->group('campaign_id');
        
        $lastDateSelect = $this->_select($lastDateSelect, 'max_dates', 'MIN(`date`)');
        
        return $adapter->fetchOne($lastDateSelect);
    }



    
    
}