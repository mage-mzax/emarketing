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
 * Base report aggregator
 *
 * Aggregate the data by: camapgin, variation and date
 *
 * +-------------+--------------+------+----------+-------+-----------+--------+------------+---------+-------------+---------+-------------+
 * | CAMPAIGN_ID | VARIATION_ID | DATE | sendings | views | view_rate | clicks | click_rate | bounces | bounce_rate | optouts | optout_rate |
 * +-------------+--------------+------+----------+-------+-----------+--------+------------+---------+-------------+---------+-------------+
 * |         ... |          ... |  ... |     ...  |  ...  |      ..%  |   ...  |       ..%  |    ...  |        ..%  |    ...  |        ..%  |
 * :             :              :      :          :       :           :        :            :         :             :         :             :
 * +-------------+--------------+------+----------+-------+-----------+--------+------------+---------+-------------+---------+-------------+
 * [mzax_emarkteting_report table]
 *
 * variation_id = -1  => all variations
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Report_Aggregator_Recipient
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    /**
     * @var string
     */
    protected $_reportTable = 'report';

    /**
     * @return void
     */
    protected function _aggregate()
    {
        $startTime = microtime(true);

        $this->_lastRecordTime = null;
        if ($this->getOption('full', false)) {
            $this->truncateTable($this->_reportTable);
        } else {
            if ($campaignId = $this->getOption('campaign_id')) {
                $this->delete(array('`campaign_id` IN(?)' => $campaignId));
            } elseif ($incremental = abs($this->getOption('incremental'))) {
                $this->delete(array("`date` >= DATE_SUB(?, INTERVAL $incremental DAY)" => $this->getLastRecordTime()));
            }
        }

        $this->aggregateSendings();
        $this->aggregateEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_VIEW, 'views');
        $this->aggregateEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_CLICK, 'clicks');
        $this->aggregateBounces();
        $this->aggregateOptouts();

        $duration = microtime(true)-$startTime;

        $this->log("Aggregation Time: %01.4fsec", $duration);
    }

    /**
     * Aggregate the number of sendings per day
     *
     *
     *
     * +-------------+--------------+------+----------+
     * | campaign_id | variation_id | date | sendings |
     * +-------------+--------------+------+----------+
     * |         ... |           -1 |  ... |     ...  |
     * :             :           -1 :      :          :
     * +-------------+--------------+------+----------+
     *
     * @return void
     */
    protected function aggregateSendings()
    {
        // all variations combined
        $select = $this->_select('recipient', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->addBinding('store_id', 'campaign.store_id');
        $select->addBinding('date_filter', 'sent_at');
        $select->addBinding('store_date', $this->getLocalTimeSql('`recipient`.`sent_at`'));
        $select->where('is_mock = 0');
        $select->where('sent_at IS NOT NULL');
        $select->columns(array(
            'campaign_id'    => 'campaign_id',
            'variation_id'   => new Zend_Db_Expr(-1),
            'date'           => 'DATE({store_date})',
            'sendings'       => 'COUNT(`recipient`.`recipient_id`)',
        ));
        $select->group(array('campaign_id', 'DATE({store_date})'));

        $select->filter('recipient.campaign_id', $this->_options->getCampaignId());
        $this->applyDateFilter($select);

        $this->insertSelect($select);

        // for each variations
        $select->group('variation_id');
        $select->where('variation_id >= 0');
        $select->setColumn('variation_id', 'variation_id');

        $this->insertSelect($select);
    }

    /**
     * Aggregate the number of unique occurens of a given event per day
     *
     * e.g. views or clicks
     *
     *
     * +-------------+--------------+------+------------+
     * | campaign_id | variation_id | date | $fieldName |
     * +-------------+--------------+------+------------+
     * |         ... |          ... |  ... |       ...  |
     * :             :              :      :            :
     * +-------------+--------------+------+------------+
     *
     *
     * @param integer $eventType
     * @param string $fieldName
     *
     * @return void
     */
    protected function aggregateEvent($eventType, $fieldName)
    {
        $uniqueEvents = $this->_select('recipient_event', 'event');
        $uniqueEvents->addBinding('date_filter', 'event.captured_at');
        $uniqueEvents->where('`event`.`event_type` = ?', $eventType);
        $uniqueEvents->group('recipient_id');
        $uniqueEvents->columns(array(
            'recipient_id' => 'recipient_id',
            'date'         => 'MIN(`captured_at`)',
        ));

        $this->applyDateFilter($uniqueEvents);

        // all variations combined
        $select = $this->_select($uniqueEvents, 'event');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->addBinding('campaign_id', 'recipient.campaign_id');
        $select->addBinding('store_id', 'campaign.store_id');
        $select->addBinding('date_filter', 'event.date');
        $select->addBinding('store_date', $this->getLocalTimeSql('`event`.`date`'));
        $select->where('`recipient`.`is_mock` = 0');
        $select->columns(array(
            'campaign_id'  => 'recipient.campaign_id',
            'variation_id' => new Zend_Db_Expr(-1),
            'date'         => 'DATE({store_date})',
            $fieldName     => 'COUNT(DISTINCT `event`.`recipient_id`)',
        ));
        $select->group(array('recipient.campaign_id', 'DATE({store_date})'));

        $this->insertSelect($select);

        // for each variations
        $select->group('recipient.variation_id');
        $select->where('recipient.variation_id >= 0');
        $select->setColumn('variation_id', 'recipient.variation_id');

        $this->insertSelect($select);
    }





    /**
     * Aggregate the number of unique bounces per day
     *
     *
     * +-------------+--------------+------+---------+
     * | campaign_id | variation_id | date | bounces |
     * +-------------+--------------+------+---------+
     * |         ... |          ... |  ... |    ...  |
     * :             :              :      :         :
     * +-------------+--------------+------+---------+
     *
     * GROUP BY campaign & date & variation
     *
     * @return void
     */
    protected function aggregateBounces()
    {
        $uniqueBounces = $this->_select('inbox_email', 'inbox');
        $uniqueBounces->addBinding('date_filter', 'inbox.created_at');
        $uniqueBounces->where('is_parsed = 1');
        $uniqueBounces->where('`type` = "SB" || `type` = "HB"');
        $uniqueBounces->where('recipient_id IS NOT NULL');
        $uniqueBounces->group('recipient_id');
        $uniqueBounces->columns(array(
            'recipient_id'  => 'recipient_id',
            'date'          => $this->_getWriteAdapter()->getIfNullSql('MIN(`sent_at`)', 'MIN(`created_at`)')
        ));

        $this->applyDateFilter($uniqueBounces);

        // all variations combined
        $select = $this->_select($uniqueBounces, 'bounce');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->addBinding('campaign_id', 'recipient.campaign_id');
        $select->addBinding('store_id', 'campaign.store_id');
        $select->addBinding('store_date', $this->getLocalTimeSql('`bounce`.`date`'));
        $select->addBinding('date_filter', 'bounce.date');
        $select->group(array('recipient.campaign_id', 'DATE({store_date})'));
        $select->where('`recipient`.`is_mock` = 0');
        $select->columns(array(
            'campaign_id'    => 'recipient.campaign_id',
            'variation_id'   => new Zend_Db_Expr(-1),
            'date'           => 'DATE({store_date})',
            'bounces'        => 'COUNT(`bounce`.`recipient_id`)',
        ));

        $this->insertSelect($select);

        // for each variations
        $select->group('recipient.variation_id');
        $select->where('recipient.variation_id >= 0');
        $select->setColumn('variation_id', 'recipient.variation_id');

        $this->insertSelect($select);
    }

    /**
     * Aggregate the number of unique optouts per day
     *
     * We are tracking the number of clicks on optout links
     * An email failed if the user clicked on a unsubscribe link.
     *
     *
     *
     * +-------------+--------------+------+---------+
     * | campaign_id | variation_id | date | bounces |
     * +-------------+--------------+------+---------+
     * |         ... |          ... |  ... |    ...  |
     * :             :              :      :         :
     * +-------------+--------------+------+---------+
     *
     * @return void
     */
    protected function aggregateOptouts()
    {
        $unsubscibeClicks = $this->_select('link_reference_click', 'click');
        $unsubscibeClicks->joinTable('reference_id', 'link_reference', 'reference');
        $unsubscibeClicks->joinTable('link_id', 'link');
        $unsubscibeClicks->addBinding('date_filter', 'click.clicked_at');
        $unsubscibeClicks->addBinding('click_id', 'click.click_id');
        $unsubscibeClicks->addBinding('reference_id', 'click.reference_id');
        $unsubscibeClicks->addBinding('link_id', 'reference.link_id');
        $unsubscibeClicks->addBinding('recipient_id', 'reference.recipient_id');
        $unsubscibeClicks->where('`link`.`optout` = 1');
        $unsubscibeClicks->group('{recipient_id}');
        $unsubscibeClicks->columns(array(
             'recipient_id' => 'reference.recipient_id',
             'date'         => 'MIN(`clicked_at`)',
        ));

        $unsubscibeEmails = $this->_select('inbox_email', 'inbox');
        $unsubscibeEmails->addBinding('recipient_id', 'inbox.recipient_id');
        $unsubscibeEmails->where('`inbox`.`type` = ?', Mzax_Emarketing_Model_Inbox_Email::UNSUBSCRIBE);
        $unsubscibeEmails->where('`inbox`.`recipient_id`');
        $unsubscibeEmails->group('{recipient_id}');
        $unsubscibeEmails->columns(array(
            'recipient_id' => 'inbox.recipient_id',
            'date'         => 'MIN(`inbox`.`sent_at`)',
        ));

        $this->applyDateFilter($unsubscibeClicks);
        $this->applyDateFilter($unsubscibeEmails);

        $union = $this->_select()->union(array($unsubscibeClicks, $unsubscibeEmails));

        // All variations combined
        $select = $this->_select($union, 'optout');
        $select->joinTable('recipient_id', 'recipient');
        $select->joinTable('campaign_id', 'campaign');
        $select->addBinding('campaign_id', 'recipient.campaign_id');
        $select->addBinding('store_id', 'campaign.store_id');
        $select->addBinding('store_date', $this->getLocalTimeSql('`optout`.`date`'));
        $select->addBinding('date_filter', 'optout.date');
        $select->where('`recipient`.`is_mock` = 0');
        $select->group(array('recipient.recipient_id', 'recipient.campaign_id', 'DATE({store_date})'));
        $select->columns(array(
            'campaign_id'    => 'recipient.campaign_id',
            'variation_id'   => new Zend_Db_Expr(-1),
            'date'           => 'DATE({store_date})',
            'optouts'        => 'COUNT(`optout`.`recipient_id`)',
        ));

        $this->insertSelect($select);

        // for each variations
        $select->group('recipient.variation_id');
        $select->where('recipient.variation_id >= 0');
        $select->setColumn('variation_id', 'recipient.variation_id');

        $this->insertSelect($select);
    }

    /**
     * Get incremental sql expressions
     *
     * $field '?' will usally get replaced by getLastRecordTime()
     * Overwrite, go back at least 14 days to overcome issues with unique count
     *
     * @see applyDateFilter()
     * @param string $field
     * @return string
     */
    public function getIncrementalSql($field = '?')
    {
        $incremental = abs((int) $this->getOption('incremental'));

        return "DATE_SUB($field, INTERVAL GREATEST($incremental, 14) DAY)";
    }

    /**
     * @return string
     */
    protected function _getLastRecordTime()
    {
        $adapter = $this->_getWriteAdapter();
        return $adapter->fetchOne($this->_select($this->_reportTable, null, 'MAX(`date`)'));
    }
}
