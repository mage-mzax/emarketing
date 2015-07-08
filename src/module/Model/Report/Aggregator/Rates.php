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
 * Rates should be calculated at the end.
 * 
 * This aggregator calculates the *_rate fields for both
 * report & report_conversion table
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Report_Aggregator_Rates
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    
    
    
    protected function _aggregate()
    {
        $this->syncDateEntires();
        
        $this->calculateDefaultRates();
        $this->calculateConversionRates();
    }
    
    
    
     
    
    /**
     * Since mysql does not support full outer joints, and to mimic those with left/right 
     * joints can be a bit nasty, we will sync all dates.
     * 
     * That way we can preform a quick loop up later doing a single join and savely
     * assume that all records are listed
     * 
     * @return void
     */
    public function syncDateEntires()
    {
        $campaigns = $this->_select('report', null, 'campaign_id')->distinct();
        $campaigns->filter('campaign_id', $this->getOption('campaign_id'));
        
        $campaigns = $this->_getWriteAdapter()->fetchCol($campaigns);
        
        foreach($campaigns as $campaignId) {
            $cols = array('date', 'campaign_id', 'variation_id');
            $select = $this->_select()->union(array(
                $this->_select('report', null, $cols)->where('campaign_id = ?', $campaignId),
                $this->_select('report_conversion', null,$cols)->where('campaign_id = ?', $campaignId)
            ));
                        
            $select = $this->_select($select, 'base', $cols);
            $this->insertSelect($select, 'report');
            
            $select->joinCross($this->_select('report_conversion', null, 'tracker_id')->where('campaign_id = ?', $campaignId)->distinct(), 'tracker_id');
            $this->insertSelect($select, 'report_conversion');
        }
        
    }
    
    
    
    



    /**
     * Calculate the default rate fields
     * such as view_rate, click_rate,...
     *
     * @return void
     */
    protected function calculateDefaultRates()
    {
        $SAME_DATE_OR_OLDER = new Zend_Db_Expr('(`prev`.`date` <= `result`.`date`)');
        
        // we need summary of all previous days till now for each record
        $previousDays = $this->_select('report', null, '*');
        
        
        $select = $this->_select('report', 'result');
        $select->addBinding('date_filter', 'result.date');
        $select->addBinding('sendings', '(SUM(`prev`.`sendings`)-SUM(`prev`.`bounces`))');
        $select->joinSelect(array('campaign_id', 'variation_id', $SAME_DATE_OR_OLDER), $previousDays, 'prev');
        $select->group(array('result.date', 'result.campaign_id' ,'result.variation_id'));
        $select->columns(array(
            'campaign_id'  => 'result.campaign_id',
            'variation_id' => 'result.variation_id',
            'date'         => 'result.date',
        ));
        
        foreach(array('view', 'click', 'bounce', 'optout') as $key) {
            $select->setColumn($key.'_rate', new Zend_Db_Expr("(SUM(`prev`.`{$key}s`)/{sendings})*100"));
        }
        
        
        $select->filter('result.campaign_id', $this->getOption('campaign_id'));
        
        $this->applyDateFilter($select, $this->fetchLastRecordTime('report'));
        
        $this->log('Calculate Default Rates');
        $this->insertSelect($select, 'report');
    }
    
    
    
    
    
    /**
     * Calculate the rate fields for each conversion tracker
     *
     * @return void
     */
    protected function calculateConversionRates()
    {
        $SAME_DATE_OR_OLDER = new Zend_Db_Expr('(`prev`.`date` <= `result`.`date`)');
        
        // we need summary of all previous days till now for each record
        $previousDays = $this->_select('report_conversion', 'conversion');
        $previousDays->joinTable(array('campaign_id', 'variation_id', 'date'), 'report', 'report');
        $previousDays->columns(array(
            'campaign_id'  => 'conversion.campaign_id',
            'variation_id' => 'conversion.variation_id',
            'tracker_id'   => 'conversion.tracker_id', 
            'date'         => 'conversion.date',
            'sendings'     => 'report.sendings',
            'bounces'      => 'report.bounces',
            'hit_revenue'  => 'conversion.hit_revenue',
            'hits'         => 'conversion.hits'
        ));
        
        
        $select = $this->_select('report_conversion', 'result');
        $select->addBinding('date_filter', 'result.date');
        $select->addBinding('sendings', '(SUM(`prev`.`sendings`)-SUM(`prev`.`bounces`))');
        $select->addBinding('hits', 'SUM(`prev`.`hits`)');
        $select->addBinding('revenue', 'SUM(`prev`.`hit_revenue`)');
        
        $select->joinSelect(array('campaign_id', 'variation_id', 'tracker_id', $SAME_DATE_OR_OLDER), $previousDays, 'prev');
        $select->group(array('result.date', 'result.campaign_id' ,'result.variation_id', 'result.tracker_id'));
        $select->columns(array(
            'campaign_id'      => 'result.campaign_id',
            'variation_id'     => 'result.variation_id',
            'tracker_id'       => 'result.tracker_id',
            'date'             => 'result.date',
            'hit_rate'         => $this->_getWriteAdapter()->getIfNullSql('({hits}/{sendings}) * 100'),
            'hit_revenue_rate' => $this->_getWriteAdapter()->getIfNullSql('{revenue}/{sendings}'),
            'hit_revenue_sum'  => $this->_getWriteAdapter()->getIfNullSql('{revenue}')
        ));
        
        $select->filter('result.campaign_id', $this->getOption('campaign_id'));
        
        $this->applyDateFilter($select, $this->fetchLastRecordTime('report_conversion'));
        
        $this->log('Calculate Conversion Rates');
        $this->insertSelect($select, 'report_conversion');
    }
    
    
    
    /**
     * Fetch last record time
     * 
     * @param string $table
     * @return string
     */
    protected function fetchLastRecordTime($table)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $this->_select($table, 'report', 'MAX(`date`)');
        $select->filter('report.campaign_id', $this->_options->getCampaignId());
    
        return $adapter->fetchOne($select);
    }
    
    
    
    
    
    
}