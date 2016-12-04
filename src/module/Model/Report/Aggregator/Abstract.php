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
 * Class Mzax_Emarketing_Model_Report_Aggregator_Abstract
 */
abstract class Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    /**
     * Default table for insert queries
     *
     * @var string
     */
    protected $_reportTable;

    /**
     *
     * @see getLastRecordTime()
     * @var string
     */
    protected $_lastRecordTime;

    /**
     * Aggregation Options
     *
     * @var Varien_Object
     */
    protected $_options;

    public function aggregate(Varien_Object $options)
    {
        $this->_options = $options;
        $this->_lastRecordTime = null;
        $this->_aggregate();
    }

    /**
     * @return void
     */
    abstract protected function _aggregate();

    /**
     * Truncate specified table
     *
     * @param string $table
     *
     * @return $this
     */
    protected function truncateTable($table = null)
    {
        if (!$table) {
            $table = $this->_reportTable;
        }

        $this->log("Truncate table: %s", $table);
        $this->_getWriteAdapter()->truncateTable($this->_getTable($table));

        return $this;
    }

    /**
     * Delete certain elements from table
     *
     * @param array $where
     * @param string $table
     *
     * @return $this
     */
    protected function delete($where, $table = null)
    {
        if (!$table) {
            $table = $this->_reportTable;
        }

        $this->log("Delete from table `%s` where: %s", $table, var_export($where, true));
        $this->_getWriteAdapter()->delete($this->_getTable($table), $where);

        return $this;
    }

    /**
     * Retrieve option value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getOption($key, $default = null)
    {
        return $this->_options->getDataSetDefault($key, $default);
    }

    /**
     * Print log message
     *
     * @param string $message
     *
     * @return $this
     */
    protected function log($message)
    {
        if (func_num_args() > 1) {
            $message = call_user_func_array('sprintf', func_get_args());
        }
        if ($this->_options->getVerbose()) {
            echo $message . "\n";
            flush();
        }

        return $this;
    }

    /**
     * Update stats table from select
     *
     * @param Mzax_Emarketing_Db_Select $select
     * @param string $table
     *
     * @return Zend_Db_Statement_Interface
     * @throws Exception
     */
    protected function insertSelect(Mzax_Emarketing_Db_Select $select, $table = null)
    {
        $sql = $select->insertFromSelect($this->_getTable($table ? $table : $this->_reportTable));

        $startTime = microtime(true);
        try {
            $this->log("\n\n\n\n$sql\n");
            $this->_getWriteAdapter()->query($sql);
            $duration = microtime(true)-$startTime;
            $this->log("QueryTime [%s]: %01.4fsec\n\n", get_class($this), microtime(true)-$startTime);
            return $duration;
        } catch (Exception $e) {
            $this->log($e->getMessage());
            $this->log($e->getTraceAsString());
            $this->log($sql);

            throw $e;
        }
    }

    /**
     * Retrieve session model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mzax_emarketing/session');
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return $this->getResourceHelper()->getWriteAdapter();
    }

    /**
     * Retrieve new select object
     *
     * @param string $table
     * @param string $alias
     * @param string $cols
     *
     * @return Mzax_Emarketing_Db_Select
     */
    protected function _select($table = null, $alias = null, $cols = null)
    {
        $select = new Mzax_Emarketing_Db_Select($this->_getWriteAdapter());
        if ($table) {
            $select->from($this->_getTable($table, $alias), $cols);
        }

        return $select;
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @param string $alias
     *
     * @return string
     */
    protected function _getTable($table, $alias = null)
    {
        if ($table instanceof Zend_Db_Select) {
            return array($alias => $table);
        }
        $table = $this->getResourceHelper()->getTable($table);
        if ($alias) {
            return array($alias => $table);
        }
        return $table;
    }

    /**
     * Retrieve an attribute by entityname/attributename
     *
     * getAttribute(entity/attribute);
     * getAttribute(customer/signupdate);
     *
     * @param string $attribute
     * @throws Exception
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getAttribute($attribute)
    {
        return $this->getResourceHelper()->getAttribute($attribute);
    }

    /**
     * Retrieve form helper
     *
     * @return Mzax_Emarketing_Helper_Data
     */
    protected function helper()
    {
        return Mage::helper('mzax_emarketing');
    }

    /**
     * Translate
     *
     * @param string $message
     * @param string $args,...
     *
     * @return string
     */
    protected function __($message, $args = null)
    {
        return call_user_func_array(array($this->helper(), '__'), func_get_args());
    }

    /**
     *
     * @param string $type
     * @return Mzax_Emarketing_Model_Report_Aggregator_Abstract
     */
    public function getAggregator($type)
    {
        return Mage::getSingleton('mzax_emarketing/report_aggregator')->getAggregator($type);
    }

    /**
     * Retrieve field list for insert query
     *
     * @param array $fields
     * @return string
     */
    protected function quoteIdentifiers(array $fields)
    {
        $adapter = $this->_getWriteAdapter();
        $fieldList = array();
        foreach ($fields as $field) {
            $fieldList[] = $adapter->quoteIdentifier($field);
        }
        return implode(", ", $fieldList);
    }

    /**
     * Retrieve update list for insert query
     *
     * @param array $fields
     * @return string
     */
    protected function getUpdateOn(array $fields)
    {
        $write = $this->_getWriteAdapter();
        $updateList = array();

        foreach ($fields as $field) {
            $field = $write->quoteIdentifier($field);
            $updateList[] = "$field = VALUES($field)";
        }

        return implode(",\n\t", $updateList);
    }

    /**
     * Get incremental sql expressions
     *
     * $field '?' will usally get replaced by getLastRecordTime()
     *
     * @see applyDateFilter()
     * @param string $field
     * @return string
     */
    public function getIncrementalSql($field = '?')
    {
        $incremental = abs((int) $this->getOption('incremental'));

        return "DATE_SUB($field, INTERVAL $incremental DAY)";
    }

    /**
     * Apply the incremental day look back on the date_filter expression
     *
     * @param Mzax_Emarketing_Db_Select $select
     * @param string $lastRecord
     */
    protected function applyDateFilter(Mzax_Emarketing_Db_Select $select, $lastRecord = null)
    {
        $incremental = $this->getOption('incremental');
        if ($incremental && !$this->getOption('full')) {
            if ($lastRecord === null) {
                $lastRecord = $this->getLastRecordTime();
            }
            if ($lastRecord && $select->hasBinding('date_filter')) {
                $select->where("{date_filter} >= {$this->getIncrementalSql()}", $lastRecord);
            }
        }
    }

    /**
     * Retrieve the datetime value of the last record
     * that has been insert by this aggregator
     *
     * This is value is used to do incremental aggregation
     *
     * @see applyDateFilter()
     * @return string|null
     */
    public function getLastRecordTime()
    {
        if (!$this->_lastRecordTime && $this->_options) {
            $this->_lastRecordTime = $this->_getLastRecordTime();
        }
        return $this->_lastRecordTime;
    }

    /**
     * @return string
     */
    protected function _getLastRecordTime()
    {
        $adapter = $this->_getWriteAdapter();
        $select = $this->_select($this->_reportTable, 'report', 'MAX(`date`)');
        $select->filter('report.campagin_id', $this->_options->getCampaignId());

        return $adapter->fetchOne($select);
    }

    /**
     * Retrieve default gmt offset in miuntes
     * by each store
     *
     * @return Zend_Db_Expr
     */
    protected function getDefaultGmtOffset($store = true)
    {
        $gmtOffset = (int) Mage::app()->getLocale()->storeDate(Mage_Core_Model_App::ADMIN_STORE_ID)->getGmtOffset()/60;

        if ($store) {
            $adapter = $this->_getWriteAdapter();

            /* @var $store Mage_Core_Model_Store */
            foreach (Mage::app()->getStores() as $store) {
                $storeGmtOffset = Mage::app()->getLocale()->storeDate($store)->getGmtOffset()/60;
                $gmtOffset = $adapter->getCheckSql("{store_id} = {$store->getId()}", $storeGmtOffset, $gmtOffset);
            }
        }

        return $gmtOffset;
    }

    /**
     * Retreive sql local time
     *
     * @param string $field
     * @param string $gmtOffset
     * @param boolean $dateOnly
     *
     * @return Zend_Db_Expr
     */
    protected function getLocalTimeSql($field, $gmtOffset = null)
    {
        if ($gmtOffset !== null) {
            $gmtOffset = $this->_getWriteAdapter()->getIfNullSql($gmtOffset, $this->getDefaultGmtOffset());
        } else {
            $gmtOffset = $this->getDefaultGmtOffset();
        }
        return new Zend_Db_Expr("DATE_SUB($field, INTERVAL $gmtOffset MINUTE)");
    }
}
