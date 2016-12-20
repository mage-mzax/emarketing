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
 * Aggregate all conversion tracker goals into one table
 *
 * A goal does not need to have a recipient ID - it is perfectly fine to track certain goals
 * using filters.
 * Some mediums like print/tv etc will make direct tracking difficult.
 *
 * +-------------+--------------+------+------------+------------+----------------+
 * | TRACKER_ID | CAMPAIGN_ID | GOAL_ID | goal_time | goal_value | [recipient_id] |
 * +-------------+--------------+------+------------+------------+----------------+
 * |         ... |          ... |  ... |       ...  |       ...  |           ..%  |
 * :             :              :      :            :            :                :
 * +-------------+--------------+------+------------+------------+----------------+
 * [mzax_emarketing_conversion_tracker_goal]
 *
 */
class Mzax_Emarketing_Model_Report_Aggregator_Goals
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    /**
     * @var string
     */
    protected $_reportTable = 'conversion_tracker_goal';

    /**
     * @var Mzax_Emarketing_Model_Conversion_Tracker
     */
    protected $_tracker;

    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    /**
     * @return void
     */
    protected function _aggregate()
    {
        if ($this->getOption('full', false)) {
            $this->truncateTable();
        } else {
            if ($trackerId = $this->getOption('tracker_id')) {
                $this->delete(array('`tracker_id` IN(?)' => $trackerId));
            }
            if ($campaignId = $this->getOption('campaign_id')) {
                $this->delete(array('`campaign_id` IN(?)' => $campaignId));
            }
            if ($incremental = abs($this->getOption('incremental'))) {
                $this->delete(array("`goal_time` >= DATE_SUB(?, INTERVAL $incremental DAY)" => $this->getLastRecordTime()));
            }
        }

        $this->aggregateGoals();
    }

    /**
     * Aggregate all goals for each tracker and campaign
     *
     * This query will use the conditions provided by each tracker
     * and then search for matching goals.
     *
     * We try to bind a recipiend to each goal but we don't require it!
     *
     * @return void
     */
    public function aggregateGoals()
    {
        /* @var $trackers Mzax_Emarketing_Model_Resource_Conversion_Tracker_Collection */
        $trackers = Mage::getResourceModel('mzax_emarketing/conversion_tracker_collection');
        $trackers->addFieldToFilter('is_active', 1);

        if ($this->_options->getTrackerId()) {
            $trackers->addIdFilter($this->_options->getTrackerId());
        }

        /* @var $tracker Mzax_Emarketing_Model_Conversion_Tracker */
        foreach ($trackers as $tracker) {
            $this->_tracker = $tracker;
            $goal = $tracker->getGoal();

            /* @var $campaign Mzax_Emarketing_Model_Campaign */
            foreach ($tracker->getCampaigns() as $campaign) {
                $this->_campaign = $campaign;

                $select = $goal->getAggregationSelect($campaign);

                // only allow date filter if tracker is already aggregated
                if ($tracker->isAggregated()) {
                    $this->applyDateFilter($select, $this->_getLastRecordTime());
                }

                $this->insertSelect($select);
                $this->_options->getLock()->touch();
            }
        }

        $this->_tracker = null;
        $this->_campaign = null;
    }

    /**
     * @return string
     */
    protected function _getLastRecordTime()
    {
        $adapter = $this->_getWriteAdapter();

        $select = $this->_select($this->_reportTable, null, 'MAX(`goal_time`)');
        if ($this->_tracker) {
            $select->where('`tracker_id` = ?', $this->_tracker->getId());
        }
        if ($this->_campaign) {
            $select->where('`campaign_id` = ?', $this->_campaign->getId());
        }

        return $adapter->fetchOne($select);
    }
}
