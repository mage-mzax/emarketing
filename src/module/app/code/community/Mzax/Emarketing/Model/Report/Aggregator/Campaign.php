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
 * Final summary data for each campaign for grid view
 */
class Mzax_Emarketing_Model_Report_Aggregator_Campaign
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    /**
     * @return void
     */
    protected function _aggregate()
    {
        $startTime = microtime(true);

        $this->aggregateReportTable();
        $this->aggregateReportConversionTable();

        $this->log("Aggregation Time: %01.4fsec", microtime(true)-$startTime);
    }

    /**
     * Retrieve the campaigns interactions and send stats from
     * the report table previously aggregated by the recipient
     *
     * @return void
     */
    protected function aggregateReportTable()
    {
        $select = $this->_select('report');
        $select->group('campaign_id');
        $select->setColumn('campaign_id', 'campaign_id');

        $select->setColumn('sending_stats', new Zend_Db_Expr('SUM(sendings)'));
        $select->setColumn('view_stats', new Zend_Db_Expr('SUM(views)'));
        $select->setColumn('interaction_stats', new Zend_Db_Expr('SUM(clicks)'));
        $select->setColumn('fail_stats', new Zend_Db_Expr('SUM(bounces) + SUM(optouts)'));


        $updateSql = "UPDATE `{$this->_getTable('campaign')}` AS `campaign`\n";
        $updateSql.= "LEFT JOIN ($select) AS `report` ON `report`.`campaign_id` = `campaign`.`campaign_id`\n";
        $updateSql.= "SET\n";
        $updateSql.= "`campaign`.`sending_stats` = IFNULL(`report`.`sending_stats`, 0),\n";
        $updateSql.= "`campaign`.`view_stats` = IFNULL(`report`.`view_stats`, 0),\n";
        $updateSql.= "`campaign`.`interaction_stats` = IFNULL(`report`.`interaction_stats`, 0),\n";
        $updateSql.= "`campaign`.`fail_stats` = IFNULL(`report`.`fail_stats`, 0)";

        $this->_getWriteAdapter()->query($updateSql);
    }

    /**
     * Retrieve conversion and revenue stats from conversion table
     * that was previously aggregated by the trackers
     *
     * We will use the campaigns default tracker for that
     *
     * @return void
     */
    protected function aggregateReportConversionTable()
    {
        $defaultTracker = $this->getDefaultTrackerId();

        $select = $this->_select('report_conversion');
        $select->group('campaign_id', 'tracker_id');
        $select->setColumn('campaign_id', 'campaign_id');
        $select->setColumn('tracker_id', 'tracker_id');
        $select->setColumn('conversion_stats', new Zend_Db_Expr('SUM(hits)'));
        $select->setColumn('revenue_stats', new Zend_Db_Expr('SUM(hit_revenue)'));


        $updateSql = "UPDATE `{$this->_getTable('campaign')}` AS `campaign`\n";
        $updateSql.= "LEFT JOIN ($select) AS `report` ON (`report`.`campaign_id` = `campaign`.`campaign_id` AND `report`.`tracker_id` = IFNULL(`campaign`.`default_tracker_id`, $defaultTracker))\n";
        $updateSql.= "SET\n";
        $updateSql.= "`campaign`.`conversion_stats` = IFNULL(`report`.`conversion_stats`, 0),\n";
        $updateSql.= "`campaign`.`revenue_stats` = IFNULL(`report`.`revenue_stats`, 0)";

        $this->_getWriteAdapter()->query($updateSql);
    }

    /**
     * Retrieve default tracker id
     *
     * @return number
     */
    public function getDefaultTrackerId()
    {
        return (int)$this->_getWriteAdapter()->fetchOne(
            $this->_select('conversion_tracker', null, 'tracker_id')->where('`is_default` = 1')
        );
    }
}
