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
 * Class Mzax_Emarketing_Model_Report_Query
 */
class Mzax_Emarketing_Model_Report_Query
{
    const DIMENSION = 'dimension';
    const VARIATION = 'variations';
    const CAMPAIGN  = 'campaign';
    const METRICS   = 'metrics';
    const ORDER     = 'order';

    const DEFAULT_DIMENSION = 'date';
    const PIVOT_SEPARATOR = '|';

    const TRACKER_PATTERN = '/^#([0-9]+)(?:_(rate|revenue|revenue_rate|revenue_sum))?$/i';

    const UNIT_DAYS   = 'days';
    const UNIT_MONTHS = 'months';
    const UNIT_WEEKS  = 'weeks';

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var array
     */
    protected $_data;

    /**
     * Select statment used to retrieve the data
     *
     * @var Varien_Db_Select
     */
    protected $_select;

    /**
     * @var Mzax_Chart_Table
     */
    protected $_dataTable;

    /**
     * @var string
     */
    protected $_timeUnit = self::UNIT_DAYS;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct($params = null)
    {
        if (is_array($params)) {
            $this->setParams($params);
        }
    }

    /**
     * Retrieve query params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Set multiple parameters
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->setParam($name, $value);
        }
        return $this;
    }

    /**
     * Set parameter
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->_params[strtolower($name)] = $value;
        return $this;
    }

    /**
     * Retrieve parameter
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParam($name, $default = false)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        return $default;
    }

    /**
     * Retrieve all variation IDs for a given campaign.
     *
     * @param integer|array $campaignId
     *
     * @return array
     */
    public function getAllVariations($campaignId)
    {
        $select = $this->select('report', null, 'variation_id')
            ->where('campaign_id IN (?)', $campaignId)
            ->where('variation_id >= 0')
            ->distinct();

        return $this->getReadAdapter()->fetchCol($select);
    }

    /**
     * Load query data
     *
     * @return $this
     */
    public function load()
    {
        if (!$this->isLoaded()) {
            $this->_prepareParams($this->_params);

            switch ($this->getParam(self::DIMENSION, self::DEFAULT_DIMENSION)) {
                case 'date':
                    $this->_select = $this->_loadDateReport('date');
                    break;

                case 'campaign':
                    $this->_select = $this->_loadDateReport('campaign_id');
                    break;

                default:
                    $this->_select = $this->_loadDimensionReport();
                    break;
            }

            if ($this->_select) {
                $this->_data = $this->getReadAdapter()->fetchAll($this->_select);
            }
        }
        return $this;
    }

    /**
     * Is query loaded
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->_data !== null;
    }

    /**
     * Retrieve data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->isLoaded()) {
            $this->load();
        }
        return $this->_data;
    }

    /**
     * Retrieve select object
     * Should be used for debugging purpose only
     *
     * @return Varien_Db_Select
     */
    public function getSelect()
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        return $this->_select;
    }

    /**
     * Retrieve data table object used by google charts
     *
     * @return Mzax_Chart_Table
     */
    public function getDataTable()
    {
        if (!$this->_dataTable) {
            $this->_dataTable = $this->convertArrayToTable($this->getData());
        }
        return $this->_dataTable;
    }

    /**
     * Prepare and validate parameters
     *
     * @param array $params
     * @throws Exception
     *
     * @return void
     */
    protected function _prepareParams(array &$params)
    {
        if (!isset($params[self::CAMPAIGN])) {
            throw new Exception("No campaign defined for query");
        }
        if (!isset($params[self::METRICS])) {
            throw new Exception("No metric defined for query");
        }
        if (!isset($params[self::VARIATION])) {
            $params[self::VARIATION] = false;
        }

        // make sure we have an array
        $params[self::METRICS] = (array) $params[self::METRICS];

        // retrieve all available variations if true
        if ($params[self::VARIATION] === true) {
            $params[self::VARIATION] = $this->getAllVariations($params[self::CAMPAIGN]);
        } elseif ($params[self::VARIATION]) {
            $params[self::VARIATION] = (array) $params[self::VARIATION];
        }
    }

    /**
     * Load data using report table which aggragates all
     * data by date
     *
     * @return Varien_Db_Select
     */
    protected function _loadDateReport($groupBy = 'date')
    {
        $select = $this->select('report');
        $this->_prepareFilters($select);

        $columns = array();

        if ($groupBy === 'date') {
            $timeUnit = $this->getParam('time_unit', self::UNIT_DAYS);

            if ($timeUnit === 'auto') {
                $results = $this->countResults($select);
                if ($results >= 400) {
                    $timeUnit = self::UNIT_MONTHS;
                } else {
                    $timeUnit = self::UNIT_DAYS;
                }
            }

            $this->_timeUnit = $timeUnit;
            $labelExpr = null;
            if ($timeUnit === self::UNIT_MONTHS) {
                //$groupExpr = new Zend_Db_Expr("CONCAT_WS('-', YEAR(report.date), LPAD(MONTH(report.date), 2, '0'), '01')");
                $groupExpr = new Zend_Db_Expr("CONCAT_WS('-', YEAR(report.date), MONTH(report.date))");
                $labelExpr = new Zend_Db_Expr("MIN(report.date)");
            } elseif ($timeUnit === self::UNIT_WEEKS) {
                $groupExpr = new Zend_Db_Expr("YEARWEEK(report.date, 1)");
                $labelExpr = new Zend_Db_Expr("MIN(report.date)");
            } else {
                $this->_timeUnit = self::UNIT_DAYS;
                $groupExpr = 'report.date';
            }

            if (!$labelExpr) {
                $labelExpr = $groupExpr;
            }

            $select->group($groupExpr);
            $columns['date'] = $labelExpr;
        } else {
            $columns[$groupBy] = 'report.'.$groupBy;
            $select->group('report.'.$groupBy);
        }

        $this->_prepareColumns($select, $columns, 'report_conversion');
        $select->columns($columns);

        $order = $this->getParam(self::ORDER, 0);
        if ($order === 0) {
            $select->order("$groupBy ASC");
        } elseif (is_int($order)) {
            $metrics = $this->getParam(self::METRICS);
            if (isset($metrics[$order-1])) {
                $select->order("{$metrics[$order-1]} DESC");
            }
        }

        return $select;
    }

    /**
     * @param Zend_Db_Select $select
     *
     * @return int
     */
    protected function countResults(Zend_Db_Select $select)
    {
        $select = clone $select;
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::GROUP);
        $select->columns(array('count' => 'COUNT(*)'));

        return (int) $this->getReadAdapter()->fetchOne($select);
    }

    /**
     * Load data using dimension table wich aggragetes all
     * data by different dimensions
     *
     * @throws Exception
     * @return Varien_Db_Select
     */
    protected function _loadDimensionReport()
    {
        $dimension = $this->getParam(self::DIMENSION);
        $dimensionId = $this->getDimensionId($dimension);

        $select = $this->select('report_dimension', 'report');
        $select->where('`report`.`dimension_id` = ?', $dimensionId);
        $this->_prepareFilters($select);

        // join label value
        $select->join(
            array('label' => $this->getTable('report_enum')),
            '`label`.`value_id` = `report`.`value_id`',
            null
        );

        // underscore dimension
        $dimension = strtolower(preg_replace('/(.)(?:\s+|([A-Z]))/', "$1_$2", $dimension));

        $columns = array($dimension => 'label.value');
        $this->_prepareColumns($select, $columns, 'report_dimension_conversion');
        $select->columns($columns);
        $select->group('report.value_id');

        $order = $this->getParam(self::ORDER);
        if ($order === 0) {
            $select->order("$dimension ASC");
        } elseif (is_int($order)) {
            $metrics = $this->getParam(self::METRICS);
            if (isset($metrics[$order-1])) {
                $select->order("{$metrics[$order-1]} DESC");
            }
        }

        return $select;
    }

    /**
     * Retreive dimension id from dimension label
     *
     * @param string $dimension
     * @return number
     */
    public function getDimensionId($dimension)
    {
        $select = $this->select('report_enum', null, 'value_id');
        $select->where('`value` = ?', $dimension);
        $select->limit(1);

        return (int) $this->getReadAdapter()->fetchOne($select);
    }

    /**
     * Prepare columns for the select
     *
     * @param Varien_Db_Select $select
     * @param array $columns
     * @param string $trackerTable
     *
     * @return void
     */
    protected function _prepareColumns(Varien_Db_Select $select, &$columns, $trackerTable)
    {
        $adapter = $this->getReadAdapter();

        $metrics    = $this->getParam(self::METRICS);
        $variations = $this->getParam(self::VARIATION);
        $order      = $this->getParam(self::ORDER);

        if ($variations) {
            foreach ($metrics as $alias => $metric) {
                if (is_numeric($alias)) {
                    $alias = $metric;
                }

                if ($this->matchTracker($metric, $trackerId, $field)) {
                    $tableAlias = $this->_joinTracker($select, $trackerId, $trackerTable);

                    foreach ($variations as $variation) {
                        $columns[$alias . self::PIVOT_SEPARATOR . $variation] = $this->getPivotSql($variation, "$tableAlias.$field", null, $this->getFun($metric));
                    }
                } else {
                    foreach ($variations as $variation) {
                        $columns[$alias . self::PIVOT_SEPARATOR . $variation] = $this->getPivotSql($variation, $metric, null, $this->getFun($metric));
                    }
                }

                if ($order === $alias) {
                    $select->order("$alias DESC");
                }
            }
        } else {
            foreach ($metrics as $alias => $metric) {
                if (is_numeric($alias)) {
                    $alias = $metric;
                }

                $field = $adapter->quoteIdentifier($metric);

                if ($this->matchTracker($metric, $trackerId, $field)) {
                    $tableAlias = $this->_joinTracker($select, $trackerId, $trackerTable);
                    $field = $adapter->quoteIdentifier("$tableAlias.$field");
                }
               // $expr = $adapter->getIfNullSql($field, 0);
                $columns[$alias] = "{$this->getFun($metric)}($field)";

                if ($order === $alias) {
                    $select->order("$alias DESC");
                }
            }
        }
    }

    /**
     * What group function to use for what metric
     *
     * SUM does not work for all metrics
     *
     * @param string $metric
     * @return string
     */
    protected function getFun($metric)
    {
        // rate-metrics should use average
        if (strpos($metric, '_rate')) {
            return 'AVG';
        }
        if (strpos($metric, '_sum')) {
            return 'MAX';
        }
        return 'SUM';
    }

    /**
     * Use IF() expresion to pivot the sql result on
     * the variation column
     *
     * @param integer $variation
     * @param string $column
     *
     * @return Zend_Db_Expr
     */
    protected function getPivotSql($variation, $column, $zero = null, $fun = 'SUM')
    {
        $adapter = $this->getReadAdapter();

        $sql = $adapter->getCheckSql(
            $adapter->quoteInto('`report`.`variation_id` = ?', $variation),
            $adapter->quoteIdentifier($column),
            $zero === null ? 'NULL' : $adapter->quote($zero)
        );

        return new Zend_Db_Expr("$fun($sql)");
    }

    /**
     * Join Tracker table for the given tracker id
     *
     * return table alias
     *
     * @param Varien_Db_Select $select
     * @param integer $trackerId
     * @param string $table
     *
     * @return string
     */
    protected function _joinTracker(Varien_Db_Select $select, $trackerId, $table)
    {
        $alias = 'tracker_'.$trackerId;

        // already joined
        if (array_key_exists($alias, $select->getPart(Varien_Db_Select::FROM))) {
            return $alias;
        }

        $description = $this->getReadAdapter()->describeTable($this->getTable($table));

        $joins = array('campaign_id', 'variation_id', 'dimension_id', 'value_id' ,'date');

        foreach ($joins as $join) {
            if (isset($description[$join])) {
                $cond[] = "`report`.`$join` = `$alias`.`$join`";
            }
        }
        $cond[] = "`$alias`.`tracker_id` = {$trackerId}";

        // seperate join condition is a bit faster
        //$cond[] = "((`report`.`variation_id` = `$alias`.`variation_id`) OR (`report`.`variation_id` IS NULL && `$alias`.`variation_id` IS NULL))";
       /* if ($this->getParam(self::VARIATION)) {
            $cond[] = "`report`.`variation_id` = `$alias`.`variation_id`";
        }
        else {
            $cond[] = "`$alias`.`variation_id` IS NULL";
        }*/

        $cond = implode(' AND ', $cond);

        $select->joinLeft(array($alias => $this->getTable($table)), $cond, null);

        return $alias;
    }

    /**
     * Prepare query filters
     *
     * @param Varien_Db_Select $select
     */
    protected function _prepareFilters(Varien_Db_Select $select)
    {
        $adapter = $this->getReadAdapter();

        $select->where('`report`.`campaign_id` = ?', $this->getParam(self::CAMPAIGN));

        $variation = $this->getParam(self::VARIATION);
        if ($variation === false) {
            $select->where('`report`.`variation_id` = -1');
        } elseif ($variation === true) {
            $select->where('`report`.`variation_id` >= 0', $variation);
        } elseif ($variation) {
            $select->where('`report`.`variation_id` IN(?)', $variation);
        }

        $range = $this->getParam('date_range');
        if (is_array($range)) {
            if (!empty($range[0])) {
                $select->where('`report`.`date` >= ?', $range[0]);
            }
            if (!empty($range[1])) {
                $select->where('`report`.`date` <= ?', $range[1]);
            }
        }
    }

    /**
     * Retrieve value of a given cell
     *
     * @param string $column
     * @param int $row
     *
     * @return mixed
     */
    public function getCell($column, $row = 0)
    {
        $this->load();
        if (isset($this->_data[$row][$column])) {
            return $this->_data[$row][$column];
        }
        return null;
    }

    /**
     * Retrieve the sum of all rows and all specified columns
     *
     * @param string $column,...
     *
     * @return number
     */
    public function getSum($column)
    {
        $columns = func_get_args();
        $sum = 0;
        foreach ($this->getData() as $row) {
            foreach ($columns as $col) {
                if (isset($row[$col])) {
                    $sum += (float) $row[$col];
                }
            }
        }

        return $sum;
    }

    /**
     *
     * @return Mzax_Chart_Table
     */
    public function convertArrayToTable(array $data)
    {
        $table = new Mzax_Chart_Table();

        if (empty($data)) {
            return $table;
        }

        $table->setTableProperty($this->getParams());
        $table->setTableProperty('timeunit', $this->_timeUnit);

        // @todo only durring debug
        $table->setTableProperty('query', $this->getSelect()->assemble());

        $metrics = $this->getParam(self::METRICS);
        $columns = array_keys(reset($data));

        foreach ($columns as $index => $column) {
            if ($column === 'date') {
                $column = $table->addColumn('Date', Mzax_Chart_Table::TYPE_DATE, 'date', array('role' => 'domain', 'unit' => $this->_timeUnit));

                switch ($this->_timeUnit) {
                    case self::UNIT_MONTHS:
                        $column->p->pattern = 'F Y';
                        break;
                    case self::UNIT_WEEKS:
                        $column->p->pattern = 'W o';
                        break;
                }

                continue;
            } elseif (!$index) {
                $table->addColumn($this->getParam(self::DIMENSION), Mzax_Chart_Table::TYPE_STRING, $column, array('role' => 'domain'));
                continue;
            }

            $p = array();
            $alias = $column;
            if (isset($metrics[$alias])) {
                $column = $metrics[$alias];
                $p['alias'] = $alias;
            }
            if (strpos($column, self::PIVOT_SEPARATOR)) {
                list($p['metric'], $p['variation_id']) = explode(self::PIVOT_SEPARATOR, $column);
            } else {
                $p['metric'] = $column;
            }

            if (strpos($column, '_rate')) {
                $p['_f'] = '%01.2f%%';
                $p['default'] = '@previous';
            }
            if (strpos($column, '_revenue_rate')) {
                $p['_f'] = '%01.5f';
                $p['default'] = '@previous';
            }
            if (strpos($column, '_sum')) {
                // $p['_f'] = '%01.2f';
                $p['default'] = '@previous';
            }

            $this->matchTracker($p['metric'], $p['tracker_id']);

            $table->addColumn($alias, Mzax_Chart_Table::TYPE_NUMBER, $column, $p);
        }
        foreach ($data as $row) {
            $table->addRow($row);
        }

        return $table;
    }

    /**
     * Check if metric is a tracker reference, means we
     * have to join the racker conversion table in order
     * to retrieve the data.
     *
     * +-----------+----+----------+
     * | metric    | id | field    |
     * +-----------+----+----------+
     * | #23       | 23 | hits     |
     * | #6_rate   |  6 | hit_rate |
     * +-----------+----+----------+
     *
     * @see TRACKER_PATTERN
     * @param string $metric
     * @param string|bool $trackerId
     * @param string|bool $field
     *
     * @return boolean
     */
    public function matchTracker($metric, &$trackerId = false, &$field = false)
    {
        if (preg_match(self::TRACKER_PATTERN, $metric, $matches)) {
            $trackerId = (int) $matches[1];
            $field = isset($matches[2]) ? "hit_{$matches[2]}" : "hits";
            return true;
        }
        return false;
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
     * Retrieve table name
     *
     * @param string $table
     *
     * @return string
     */
    protected function getTable($table)
    {
        if (!strpos($table, '/')) {
            $table = 'mzax_emarketing/' . $table;
        }
        return $this->getResourceHelper()->getTable($table);
    }

    /**
     * Retrieve new db table select
     *
     * @param string $table
     * @param string $alias
     * @param string $cols
     *
     * @return Varien_Db_Select
     */
    protected function select($table = null, $alias = null, $cols = null)
    {
        $select = $this->getReadAdapter()->select();
        if ($table) {
            if (!$alias) {
                $alias = $table;
            }
            $table = $this->getTable($table);
            $table = array($alias => $table);
            $select->from($table, $cols);
        }
        return $select;
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function getReadAdapter()
    {
        return $this->getResourceHelper()->getReadAdapter();
    }
}
