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
 * Class Mzax_Emarketing_Model_Object_Filter_Component
 */
abstract class Mzax_Emarketing_Model_Object_Filter_Component extends Varien_Object
{
    const ID_ALIAS    = 'object_id';

    /**
     * @var string
     */
    protected $_id;

    /**
     * Unique component type id
     *
     * @var string
     */
    protected $_type;

    /**
     * Parent Filter
     *
     * @var Mzax_Emarketing_Model_Object_Filter_Component
     */
    protected $_parent;

    /**
     * @var Mzax_Emarketing_Model_Object_Collection
     */
    protected $_collection;

    /**
     * Can have children
     *
     * @var boolean
     */
    protected $_allowChildren = false;

    /**
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_config;

    /**
     * @var Mzax_Emarketing_Model_SessionManager
     */
    protected $_sessionManager;

    /**
     * Mzax_Emarketing_Model_Outbox constructor.
     */
    public function _construct()
    {
        parent::_construct();

        $this->_config = Mage::getSingleton('mzax_emarketing/config');
        $this->_sessionManager = Mage::getSingleton('mzax_emarketing/sessionManager');
    }

    /**
     * The subject provides generic information for the email provider/filter object.
     * So for instance, the email provider customer
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    abstract public function getObject();

    /**
     * Retrieve parent object
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getParentObject()
    {
        return $this->getParentOrSelf()->getObject();
    }

    /**
     * Retrieve Expression used for current time
     *
     * By default the function should return the
     * mysql expression NOW() but we want to be able
     * to change the current time in order to test
     * filters etc for different times
     *
     * Also some filters may require the local time
     * not the gmt time
     *
     * @param boolean $gmt Get as gmt time
     *
     * @return string[]|Zend_Db_Expr[]
     */
    public function getCurrentTime($gmt = true)
    {
        $adapter = $this->_getReadAdapter();

        // is current specified time is the local time or not
        $isLocal = (bool)$this->getParam('is_local_time');

        // check if we emulate current itme
        $now = (array)$this->getParam('current_time');
        $now = array_filter($now);
        foreach ($now as &$time) {
            if (!$time instanceof Zend_Db_Expr) {
                $time = $adapter->quote($time);
            }
        }
        if (empty($now)) {
            $now[] = $adapter->quote(now());
        }

        $result = array();
        foreach ($now as $date) {
            if ($gmt && $isLocal) {
                $result[] = $this->toGmtTime($date);
            } elseif (!$gmt && !$isLocal) {
                $result[] = $this->toLocalTime($date);
            } else {
                $result[] = $date;
            }
        }

        return $result;
    }

    /**
     * Retrieve filter select
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getSelect()
    {
        return $this->getPreparedQuery();
    }

    /**
     * Retrieve prepared query
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getPreparedQuery()
    {
        $query = $this->getParentOrSelf()->getQuery();
        $query->lock();

        $this->_prepareQuery($query);

        $query->comment('FILTER: ' . get_class($this));
        $query->lock(false);

        return $query;
    }

    /**
     * Takes the query provided by the parent and modifies it
     * to match the filter settings
     *
     * @param Mzax_Emarketing_Db_Select $query
     *
     * @return void
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
    }

    /**
     * Retrieve objects id field name
     *
     * @return string
     * @throws Exception
     * @deprecated
     */
    public function getIdFieldName()
    {
        throw new Exception("getid field???");
    }

    /**
     * Get filter select that this object will
     * pass down to all sub filter
     *
     * By default it is the parents or the objects filter
     * This method can be overwritten
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        if (!$this->_allowChildren && $this->_parent) {
            return $this->_parent->getQuery();
        }

        return $this->getObject()->getQuery();
    }

    /**
     * Retrieve collection instance from object model
     *
     * @return Mzax_Emarketing_Model_Object_Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('mzax_emarketing/object_collection');
            $this->_collection->setObject($this->getParentObject());
            $this->_collection->setQuery($this->getPreparedQuery());

            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Prepare recipient collection
     *
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     *
     * @return void
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        $this->getParentObject()->prepareCollection($collection);
    }

    /**
     * Set ID
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * Retrieve id
     *
     * @return string
     */
    public function getId()
    {
        if (!$this->_id) {
            $this->_id = '1';
        }

        return $this->_id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;

        return $this;
    }


    /**
     * Retrieve type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set parent
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $parent
     *
     * @return $this
     */
    public function setParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        $this->_parent = $parent;

        return $this;
    }

    /**
     * Retrieve parent object
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Component
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Retrieve parent object
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Component
     */
    public function getParentOrSelf()
    {
        if (!$this->_parent) {
            return $this;
        }

        return $this->_parent;
    }

    /**
     * Return all ancestors
     *
     * @return array
     */
    public function getAncestors()
    {
        $ancestors = array();
        $parent = $this;
        while ($parent = $parent->getParent()) {
            $ancestors[] = $parent;
        }

        return $ancestors;
    }

    /**
     * Check if we have binding
     *
     * @return boolean
     */
    public function hasBinding()
    {
        $func = array($this->getQuery(), 'hasBinding');

        return call_user_func_array($func, func_get_args());
    }

    /**
     * Retrieve top most object
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Component
     */
    public function getRoot()
    {
        $object = $this;

        while ($object->getParent()) {
            $object = $object->getParent();
        }

        return $object;
    }

    /**
     * Retrieve full type path
     *
     * @param string $delimiter
     *
     * @return string $string
     */
    public function getTypePath($delimiter = '-')
    {
        $paths = array();
        $object = $this;
        do {
            if ($object instanceof Mzax_Emarketing_Model_Object_Filter_Main) {
                break;
            }
            $paths[] = $object->getType();
        } while ($object = $object->getParent());

        $paths = array_reverse($paths);

        $path = implode($delimiter, $paths);

        return $path;
    }

    /**
     * Both parent and child need to accept
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $filter
     *
     * @return boolean
     */
    public function acceptFilter(Mzax_Emarketing_Model_Object_Filter_Component $filter)
    {
        if (!$filter->acceptParent($this)) {
            return false;
        }

        if (!$filter->acceptChild($this)) {
            return false;
        }

        return true;
    }

    /**
     * Accept child
     *
     * By default we can accept all children, however if the parent
     * already accepts this child then skip it to prevent useless nesting.
     *
     * Sometimes this is unwanted, in that case overwrite this method
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $child
     *
     * @return boolean
     */
    public function acceptChild(Mzax_Emarketing_Model_Object_Filter_Component $child)
    {
        if ($this->_parent) {
            return !$child->acceptParent($this->_parent);
        }

        return true;
    }

    /**
     * Accept parent
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $parent
     *
     * @return boolean
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return false;
    }

    /**
     * Retrieve id filter name
     *
     * The name of the id field that we use
     * to filter.
     *
     * Usually it is the same name as the id field name
     * however some filter almost act as an adapter,
     * they allow to retrieve customer_ids, but use
     * the order_id as filter
     *
     * @return string
     * @throws Exception
     * @deprecated
     */
    public function getIdFilterName()
    {
        throw new Exception("getIdFilterName!!!");
    }

    /**
     * Retrieve available child filters
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract[]
     */
    public function getAvailableFilters()
    {
        if (!$this->_allowChildren) {
            return array();
        }

        $filters = self::getFilterFactory()->getFilters();
        $result = array();

        foreach ($filters as $name => $filter) {
            if ($this->acceptFilter($filter)) {
                foreach ($filter->getOptions() as $key => $title) {
                    $result[$key] = $title;
                }
            }
        }

        return $result;
    }

    //--------------------------------------------------------------------------
    //
    //  Recipient Grid Methods
    //
    //--------------------------------------------------------------------------

    /**
     * Prepare grid columns
     *
     * Allow filter to add additional gird columns relevant to it
     *
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        $this->getParentObject()->prepareGridColumns($grid);
    }

    /**
     * After collection load
     *
     * Allow grid to alter the collection after load
     *
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
     * @deprecated
     */
    public function afterGridLoadCollection(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        $this->getParentObject()->afterGridLoadCollection($grid);
    }

    /**
     * After collection load
     *
     * @param $collection
     *
     * @return void
     * @deprecated
     */
    public function afterLoadCollection($collection)
    {
        $this->getParentObject()->afterLoadCollection($collection);
    }

    /**
     * Form prefix
     *
     * @return string
     */
    public function getFormPrefix()
    {
        return 'filter';
    }

    //--------------------------------------------------------------------------
    //
    //  Helper Methods
    //
    //--------------------------------------------------------------------------

    const TEMP_TABLE_PREFIX = 'MZAX_TMP_FILTER_';

    /**
     * Retrieve parameter set on the root object
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $value = $this->getRoot()->getData('param_' . $key);
        if ($value === null) {
            return $default;
        }
        return $value;
    }

    /**
     * Set parameter
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function setParam($key, $value)
    {
        $this->getRoot()->setData('param_' . $key, $value);

        return $this;
    }

    /**
     * Retrieve a unique temporary table name that is valid for the live time of this
     * filter instance.
     *
     * @param string $suffix
     *
     * @return string
     */
    public function getTempTableName($suffix = 'default')
    {
        // can be globally disabled
        if (!$this->_config->flag('mzax_emarketing/general/use_temp_tables')) {
            return false;
        }

        // can be disabled by param
        if ($this->getParam('disable_temp_tables')) {
            return false;
        }

        // only works if we have the rights
        if (!$this->getResourceHelper()->hasTemporaryTablePrivilege()) {
            return false;
        }

        $hash = $this->getParam('unique_filter_hash', microtime().mt_rand(0, 1000));
        $name = strtoupper(self::TEMP_TABLE_PREFIX . md5($hash . '__' . $this->getId()) . '_' . $suffix);

        return $name;
    }

    /**
     * Retrieve id column name
     *
     * @return Zend_Db_Expr
     */
    public function getIdColumn()
    {
        return new Zend_Db_Expr("`e`.`{$this->_idFieldName}`");
    }

    /**
     * Retrieve session model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return $this->_sessionManager->getSession();
    }

    /**
     * Retrieve provider factory
     *
     * @return Mzax_Emarketing_Model_Object_Filter
     */
    public static function getFilterFactory()
    {
        return Mage::getSingleton('mzax_emarketing/object_filter');
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
    protected function _getReadAdapter()
    {
        return $this->getResourceHelper()->getReadAdapter();
    }

    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return $this->getResourceHelper()->getWriteAdapter();
    }

    /**
     * Create db select instance
     *
     * @param string $table
     * @param string $alias
     * @param string $cols
     *
     * @return Mzax_Emarketing_Db_Select
     */
    protected function _select($table = null, $alias = null, $cols = null)
    {
        $select = $this->getResourceHelper()->select();
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
        $table = $this->getResourceHelper()->getTable($table);
        if ($alias) {
            return array($alias => $table);
        }

        return $table;
    }

    /**
     * Retrieve an attribute by entity-name/attribute-name
     *
     * getAttribute(entity/attribute);
     * getAttribute(customer/signupdate);
     *
     * @param string $attribute
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     * @throws Exception
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
     * Converts local date expression to gmt time gmt offset
     *
     * @param string $localDateExpr
     *
     * @return string
     */
    protected function toGmtTime($localDateExpr)
    {
        $gmtOffset = (int)$this->getParam('gmt_offset', 0);
        if ($gmtOffset) {
            $localDateExpr = "DATE_SUB($localDateExpr, INTERVAL $gmtOffset MINUTE)";
        }

        return $localDateExpr;
    }

    /**
     * Convert database field to local time by applying specified gmt offset
     *
     * @param string $gmtDateExpr
     *
     * @return string
     */
    protected function toLocalTime($gmtDateExpr)
    {
        $gmtOffset = (int) $this->getParam('gmt_offset', 0);
        if ($gmtOffset) {
            $gmtDateExpr = "DATE_SUB($gmtDateExpr, INTERVAL $gmtOffset MINUTE)";
        }

        return $gmtDateExpr;
    }
}
