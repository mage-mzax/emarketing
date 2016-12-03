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



abstract class Mzax_Emarketing_Model_Report_Aggregator_Dimension_Abstract
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    
    
    
    protected $_reportTable = 'report_dimension';
    
    
    


    abstract public function getTitle();
    
    
    abstract public function getValues();
    
    
    public function getSqlValues()
    {
        return null;
    }
    
    
    public function aggregate(Varien_Object $options)
    {
        $this->_options = $options;
        $this->_lastRecordTime = null;
        
        // only aggregate if we have any values
        if (count($this->getValues())) {
            $this->_aggregate();
        }
    }
    
    
    protected function _aggregate()
    {
        if (!$this->_options->getTrackerId()) {
            $this->aggregateSendings();
            $this->aggregateEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_VIEW,  'views');
            $this->aggregateEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_CLICK, 'clicks');
            $this->aggregateBounces();
            $this->aggregateOptouts();
        }
        $this->aggregateTrackers();
    }
    

    
    
    

    protected function aggregateSendings()
    {
        $select = $this->getAggregateSendingsSelect();
        $this->insertSelect($select);
        
        // change query to group by variations
        $select->group(array('{campaign_id}', '{variation_id}', 'DATE({store_date})', '{value}'), true);
        $select->where('{variation_id} >= 0');
        $select->setColumn('variation_id', null);
        
        $this->insertSelect($select);
        
    }
    
    
    
    protected function aggregateEvent($eventType, $fieldName)
    {
        $select = $this->getAggregateEventSelect($eventType, $fieldName);
        $this->insertSelect($select);
        
        // change query to group by variations
        $select->group(array('{campaign_id}', '{variation_id}', 'DATE({store_date})', '{value}'), true);
        $select->where('{variation_id} >= 0');
        $select->setColumn('variation_id', null);
        
        $this->insertSelect($select);
        
    }
    
    
    protected function aggregateOptouts()
    {
        $select = $this->getAggregateOptoutSelect();
        $this->insertSelect($select);
        
        // change query to group by variations
        $select->group(array('{campaign_id}', '{variation_id}', 'DATE({store_date})', '{value}'), true);
        $select->where('{variation_id} >= 0');
        $select->setColumn('variation_id', null);
        
        $this->insertSelect($select);
        
        
    }
    
    
    
    protected function aggregateBounces()
    {
        $select = $this->getAggregateBounceSelect();
        $this->insertSelect($select);
        
        // change query to group by variations
        $select->group(array('{campaign_id}', '{variation_id}', 'DATE({store_date})', '{value}'), true);
        $select->where('{variation_id} >= 0');
        $select->setColumn('variation_id', null);
        
        $this->insertSelect($select);
        
    }
    
    
    

    
    protected function aggregateTrackers()
    {
        $select = $this->getAggregateTrackersSelect();
        $this->insertSelect($select, $this->_reportTable . '_conversion');
        
        // change query to group by variations
        $select->group(array('{campaign_id}', '{variation_id}', 'DATE({store_date})', '{value}'), true);
        $select->where('{variation_id} >= 0');
        $select->setColumn('variation_id', null);
        
        $this->insertSelect($select, $this->_reportTable . '_conversion');
    }
    
    
    
    
    
    
    protected function prepareAggregationSelect(Mzax_Emarketing_Db_Select $select)
    {}
    
    

    /**
     * Retrieve aggregation select for sending
     * 
     * 
     * +-------------+--------------+------+--------------+----------+----------+
     * | campaign_id | variation_id | date | dimension_id | value_id | sendings |
     * +-------------+--------------+------+--------------+----------+----------+
     * |         ... |    -1 OR >=0 |  ... |          ID  |     ...  |     ...  |
     * :             :              :      :          ID  :          :          :
     * +-------------+--------------+------+--------------+----------+----------+
     * 
     * @return Mzax_Emarketing_Db_Select
     */
    protected function getAggregateSendingsSelect()
    {
        $select = $this->_select('recipient', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        
        $select->where('`recipient`.`sent_at` IS NOT NULL');
        $select->where('`recipient`.`is_mock` = 0');
        $select->where('`campaign`.`archived` = 0');
        
        $select->addBinding('date_filter', 'recipient.sent_at');
        $select->addBinding('recipient_id', 'recipient_id');
        $select->addBinding('campaign_id',  'campaign_id');
        $select->addBinding('variation_id', 'variation_id');
        $select->addBinding('store_id',     'campaign.store_id');
        $select->addBinding('local_date',   $this->getLocalTimeSql('`recipient`.`sent_at`'));
        $select->addBinding('store_date',   $this->getLocalTimeSql('`recipient`.`sent_at`'));
        $select->addBinding('sendings', 'COUNT(DISTINCT {recipient_id})');
        
        $select->setColumn('campaign_id');
        $select->setColumn('variation_id', new Zend_Db_Expr(-1));
        $select->setColumn('date', '{store_date}');
        $select->setColumn('dimension_id', new Zend_Db_Expr($this->getDimensionId()));
        $select->setColumn('value_id', $this->getValueIdExpr());
        $select->setColumn('sendings');
        
        $select->group(array('{campaign_id}', 'DATE({store_date})', '{value}'));
        $select->filter('recipient.campaign_id', $this->_options->getCampaignId());
        $this->applyDateFilter($select);
        
        $this->prepareAggregationSelect($select->lock());
        
        return $select;
    }
    
    
    
    
    /**
     * Retrieve aggregation select for the given event type
     * 
     * +-------------+--------------+------+--------------+----------+------------+
     * | campaign_id | variation_id | date | dimension_id | value_id | $fieldName |
     * +-------------+--------------+------+--------------+----------+------------+
     * |         ... |    -1 OR >=0 |  ... |          ID  |     ...  |        ... |
     * :             :              :      :          ID  :          :            :
     * +-------------+--------------+------+--------------+----------+------------+
     *
     * 
     * @param string $eventType
     * @param string $fieldName
     * @return Mzax_Emarketing_Db_Select
     */
    protected function getAggregateEventSelect($eventType, $fieldName)
    {
        $select = $this->_select('recipient_event', 'event');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        
        $select->where('`event`.`event_type` = ?', $eventType);
        $select->where('`recipient`.`is_mock` = 0');
        $select->where('`campaign`.`archived` = 0');
        
        $select->addBinding('date_filter',  'event.captured_at');
        $select->addBinding('recipient_id', 'event.recipient_id');
        $select->addBinding('campaign_id',  'recipient.campaign_id');
        $select->addBinding('variation_id', 'recipient.variation_id');
        $select->addBinding('store_id',     'campaign.store_id');
        $select->addBinding('local_date',   $this->getLocalTimeSql('`event`.`captured_at`', '`event`.`time_offset` * 15'));
        $select->addBinding('store_date',   $this->getLocalTimeSql('`event`.`captured_at`'));
        $select->addBinding($fieldName,     'COUNT(DISTINCT {recipient_id})');
        
        $select->setColumn('campaign_id');
        $select->setColumn('variation_id', new Zend_Db_Expr(-1));
        $select->setColumn('date', 'DATE({store_date})');
        $select->setColumn('dimension_id', new Zend_Db_Expr($this->getDimensionId()));
        $select->setColumn('value_id', $this->getValueIdExpr());
        $select->setColumn($fieldName);
        
        $select->group(array('{campaign_id}', 'DATE({store_date})', '{value}'));
        $select->filter('recipient.campaign_id', $this->_options->getCampaignId());
        $this->applyDateFilter($select);
        
        $this->prepareAggregationSelect($select->lock());
        
        return $select;
    }
    
    
    


    /**
     * Retrieve aggregation select for bounces
     * 
     * +-------------+--------------+------+--------------+----------+---------+
     * | campaign_id | variation_id | date | dimension_id | value_id | bounces |
     * +-------------+--------------+------+--------------+----------+---------+
     * |         ... |    -1 OR >=0 |  ... |          ID  |     ...  |     ... |
     * :             :              :      :          ID  :          :         :
     * +-------------+--------------+------+--------------+----------+---------+
     *
     * 
     * @return Mzax_Emarketing_Db_Select
     */
    protected function getAggregateBounceSelect()
    {
        $bouncedAt = $this->_getWriteAdapter()->getIfNullSql('`inbox`.`sent_at`', '`inbox`.`created_at`');
        $bouncedAt = $this->getLocalTimeSql($bouncedAt);
        
        $select = $this->_select('inbox_email', 'inbox');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        
        $select->where('`inbox`.`is_parsed` = 1');
        $select->where('`inbox`.`type` = "SB" OR `inbox`.`type` = "HB"');
        $select->where('`recipient`.`is_mock` = 0');
        $select->where('`campaign`.`archived` = 0');
        
        $select->addBinding('date_filter',  'inbox.created_at');
        $select->addBinding('recipient_id', 'inbox.recipient_id');
        $select->addBinding('campaign_id',  'recipient.campaign_id');
        $select->addBinding('variation_id', 'recipient.variation_id');
        $select->addBinding('store_id',     'campaign.store_id');
        $select->addBinding('local_date',   $bouncedAt);
        $select->addBinding('store_date',   $bouncedAt);
        $select->addBinding('bounces',      'COUNT(DISTINCT {recipient_id})');
        
        $select->setColumn('campaign_id');
        $select->setColumn('variation_id', new Zend_Db_Expr(-1));
        $select->setColumn('date', 'DATE({store_date})');
        $select->setColumn('dimension_id', new Zend_Db_Expr($this->getDimensionId()));
        $select->setColumn('value_id', $this->getValueIdExpr());
        $select->setColumn('bounces');
        
        $select->group(array('{campaign_id}', 'DATE({store_date})', '{value}'));
        $select->filter('recipient.campaign_id', $this->_options->getCampaignId());
        $this->applyDateFilter($select);
        
        $this->prepareAggregationSelect($select->lock());
        
        return $select;
    }
    
    
    
    
    
    /**
     * Aggregate the number of unique optouts per day
     *
     * We are tracking the number of clicks on optout links
     * An email failed if the user clicked on a unsubscribe link.
     *
     * +-------------+--------------+------+--------------+----------+---------+
     * | campaign_id | variation_id | date | dimension_id | value_id | optouts |
     * +-------------+--------------+------+--------------+----------+---------+
     * |         ... |    -1 OR >=0 |  ... |          ID  |     ...  |     ... |
     * :             :              :      :          ID  :          :         :
     * +-------------+--------------+------+--------------+----------+---------+
     *
     * 
     * @return void
     */
    protected function getAggregateOptoutSelect()
    {
        $select = $this->_select('link_reference_click', 'click');
        $select->joinTable('reference_id', 'link_reference', 'reference');
        $select->joinTable('link_id', 'link');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->joinTableLeft('event_id', 'recipient_event', 'click_event');
        
        $select->where('`link`.`optout` = 1');
        $select->where('`recipient`.`is_mock` = 0');
        $select->where('`campaign`.`archived` = 0');
        
        $select->addBinding('date_filter',  'click.clicked_at');
        $select->addBinding('click_id',     'click.click_id');
        $select->addBinding('reference_id', 'click.reference_id');
        $select->addBinding('link_id',      'reference.link_id');
        $select->addBinding('recipient_id', 'reference.recipient_id');
        $select->addBinding('campaign_id',  'recipient.campaign_id');
        $select->addBinding('variation_id', 'recipient.variation_id');
        $select->addBinding('store_id',     'campaign.store_id');
        $select->addBinding('local_date',   $this->getLocalTimeSql('`click`.`clicked_at`', '`click_event`.`time_offset` * 15'));
        $select->addBinding('store_date',   $this->getLocalTimeSql('`click`.`clicked_at`'));
        $select->addBinding('optouts',      'COUNT(DISTINCT {recipient_id})');
        
        $select->setColumn('campaign_id');
        $select->setColumn('variation_id', new Zend_Db_Expr(-1));
        $select->setColumn('date', 'DATE({store_date})');
        $select->setColumn('dimension_id', new Zend_Db_Expr($this->getDimensionId()));
        $select->setColumn('value_id', $this->getValueIdExpr());
        $select->setColumn('optouts');
        
        $select->group(array('{campaign_id}', 'DATE({store_date})', '{value}'));
        $select->filter('recipient.campaign_id', $this->_options->getCampaignId());
        $this->applyDateFilter($select);
        
        $this->prepareAggregationSelect($select->lock());
        
        return $select;
    }
    
    
    
    
    
    
    /**
     * Aggregate dimension for each tracker
     * 
     * Use 'conversion_tracker_goal' as source table which has been aggregated
     * by the tracker aggregator.
     * 
     * 
     * +-------------+--------------+------+--------------+----------+------------+------+-----------+
     * | campaign_id | variation_id | date | dimension_id | value_id | tracker_id | hits | hit_value |
     * +-------------+--------------+------+--------------+----------+------------+------+-----------+
     * |         ... |    -1 OR >=0 |  ... |          ID  |     ...  |        ... |  ... |       ... |
     * :             :              :      :          ID  :          :            :      :           :
     * +-------------+--------------+------+--------------+----------+------------+------+-----------+
     * 
     * 
     * @see Mzax_Emarketing_Model_Report_Aggregator_Tracker
     * @return Mzax_Emarketing_Db_Select
     */
    public function getAggregateTrackersSelect()
    {
        $goalTime = $this->getLocalTimeSql('`goal`.`goal_time`');
        
        $select = $this->_select('conversion_tracker_goal', 'goal');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        
        $select->where('`recipient`.`is_mock` = 0');
        $select->where('`campaign`.`archived` = 0');
        
        $select->addBinding('date_filter',  'goal_time');
        $select->addBinding('goal_id',      'goal_id');
        $select->addBinding('goal_time',    'goal_time');
        $select->addBinding('tracker_id',   'tracker_id');
        $select->addBinding('recipient_id', 'recipient_id');
        $select->addBinding('campaign_id',  'recipient.campaign_id');
        $select->addBinding('variation_id', 'recipient.variation_id');
        $select->addBinding('store_id',     'campaign.store_id');
        $select->addBinding('local_date',   $goalTime);
        $select->addBinding('store_date',   $goalTime);
        $select->addBinding('hits',         'COUNT(DISTINCT {goal_id})');
        $select->addBinding('hit_value',    'SUM(`goal`.`goal_value`)');
        
        $select->setColumn('campaign_id');
        $select->setColumn('variation_id', new Zend_Db_Expr(-1));
        $select->setColumn('date', 'DATE({store_date})');
        $select->setColumn('dimension_id', new Zend_Db_Expr($this->getDimensionId()));
        $select->setColumn('value_id', $this->getValueIdExpr());
        $select->setColumn('tracker_id');
        $select->setColumn('hits');
        $select->setColumn('hit_value');
        
        $select->group(array('{campaign_id}', 'DATE({store_date})', '{value}'));
        
        $select->filter('goal.campaign_id', $this->_options->getCampaignId());
        $select->filter('goal.tracker_id', $this->_options->getTrackerId());
        $this->applyDateFilter($select);
        
        $this->prepareAggregationSelect($select->lock());
        
        return $select;
    }
    
    
    
    
    
    

    /**
     * Retreive value ids
     * 
     * @return array
     */
    public function getValueIds()
    {
        return $this->_getValueId($this->getValues());
    }
    
    
    /**
     * Retreive dimension id
     * 
     * @return integer
     */
    public function getDimensionId()
    {
        return $this->_getValueId($this->getTitle());
    }
    
    
    
    
    /**
     * Retreive value ID for the given value.
     * If value is array, return hash array in 
     * form of:
     *    value => value_id
     * 
     * 
     * @param string|array $value
     * @return string|array
     */
    protected function _getValueId($value)
    {
        if (is_array($value)) {
            $result = array();
            foreach ($value as $v) {
                if ($v) {
                    $result[$v] = $this->_getValueId($v);
                }
            }
            return $result;
        }
    
        $adapter = $this->_getWriteAdapter();
        $table = $this->_getTable('report_enum');
    
        $valueId = $adapter->fetchOne(
            $this->_select()->from($table, 'value_id')->where('`value` = ?', $value));
        
        if (!$valueId) {
            $adapter->insert($table, array('value' => $value));
            $valueId = (int) $adapter->lastInsertId($table);
        }
    
        return $valueId;
    }
    
    
    
    
    


    /**
     * Create expression that convers a string value to
     * the value id previously insert into the enum table
     * 
     * @param Zend_Db_Expr $valueExpr
     * @return Zend_Db_Expr
     */
    public function getValueIdExpr()
    {
        $adapter = $this->_getWriteAdapter();
    
        $sqlValues = $this->getSqlValues();
        $values = array();
        $ids = array();
    
        foreach ($this->getValueIds() as $value => $id) {
            if (!empty($sqlValues)) {
                $values[] = array_search($value, $sqlValues);
            }
            else {
                $values[] = $adapter->quote($value);
            }
            $ids[] = (int) $id;
        }
    
        $values = implode(', ', $values);
        $ids = implode(', ', $ids);
    
        return new Zend_Db_Expr("ELT(FIELD({value}, $values), $ids)");
    }
    
    
    
    
    public function getLastRecordTime()
    {
        $adapter = $this->_getWriteAdapter();
        
        $select = $this->_select($this->_reportTable, 'report', 'MAX(`date`)');
        $select->filter('report.campagin_id', $this->_options->getCampaignId());
        $select->filter('report.dimension_id', $this->getDimensionId());
        
        return $adapter->fetchOne($select);
    }
    
    
    
    
}
