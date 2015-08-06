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
 * 
 * @method Zend_Db_Adapter_Abstract getAdapter()
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Db_Select extends Varien_Db_Select
{
    
    
    /**
     * The main table alias
     * 
     * @var string
     */
    protected $_tableAlias = 'main_table';
    
    
    /**
     * Flag if selection is locked
     * If locked now more columns can be added
     * 
     * @var boolean
     */
    protected $_lock = false;
    
    
    /**
     * Public bindings that can be
     * used to join tables or query by
     * 
     * @var array
     */
    protected $_binding = array();
    
    
    
    protected $_joins = array();
    
    
    
    /**
     * List of seeked columns
     * 
     * @var array
     */
    protected $_seeks = array();
    
    
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Select::from()
     */
    public function from($name, $cols = null, $schema = null)
    {
        if(is_array($name)) {
            foreach($name as $correlationName => $tableName) {
                $this->_tableAlias = $correlationName;
                break;
            }
        }
        else {
            $name = array($this->_tableAlias => $name);
        }
        return parent::from($name,$cols,$schema);
    }
    
    
    
    /**
     * Lock/unlock select to prevent further changes
     * to columns
     * 
     * @param string $flag
     * @return Mzax_Db_Select
     */
    public function lock($flag = true)
    {
        $this->_lock = (bool) $flag;
        return $this;
    }
    
    
    
    /**
     * Is locked
     * 
     * @return boolean
     */
    public function locked()
    {
        return $this->_lock;
    }
    
    
    /**
     * Retrieve column fields of this select
     * 
     * @return array
     */
    public function getFields()
    {
        if($union = $this->getPart(self::UNION)) {
            if($union[0][0] instanceof Mzax_Db_Select) {
                return $union[0][0]->getFields();
            }
            return array();
        }
        $fields = array();
        foreach($this->getPart(self::COLUMNS) as $column) {
            $fields[] = empty($column[2]) ? $column[1] : $column[2];
        }
        return $fields;
    }
    


    /**
     * Add binding expression to query
     * 
     * One should use the available bindings to join tables etc
     * A binding name is global unique, means the customer_id will
     * allways point to a column that holds the customer ID
     * e.g.
     *  - e.entity_id
     *  - e.customer_id
     *
     * @param string $fieldName
     * @param string|Zend_Db_Expr $expr
     * @return Mzax_Db_Select
     */
    public function addBinding($name, $expr, $correlationName = null)
    {
        if(!$expr instanceof Zend_Db_Expr) {
            
            if (preg_match('/\(.*\)/', (string) $expr)) {
                $expr = new Zend_Db_Expr($expr);
            }
            else {
                if(!preg_match('/.+\..+/', $expr)) {
                    if(!$correlationName) {
                        $correlationName = $this->_tableAlias;
                    }
                    $expr = array($correlationName, $expr);
                }
                $expr = $this->getAdapter()->quoteIdentifier($expr);
                $expr = new Zend_Db_Expr($expr);
            }
        }
        $this->_binding[$name] = $expr;
        return $this;
    }
    
    
    /**
     * Remove existing bindings
     * 
     * @param string $name
     * @return Mzax_Db_Select
     */
    public function removeBinding($name)
    {
        unset($this->_binding[$name]);
        return $this;
    }
    
    
    
    /**
     * Remove all bindings
     * 
     * @return Mzax_Db_Select
     */
    public function removeAllBindings()
    {
        $this->_binding = array();
        return $this;
    }
    
    
    
    
    
    /**
     * Retrieve binding expression if exists
     * 
     * @param string $fieldName
     * @return Zend_Db_Expr
     */
    public function getBinding($name)
    {
        if(isset($this->_binding[$name])) {
            return $this->_binding[$name];
        }
        return null;
    }
    
    
    
    /**
     * Retrieve all bindings
     * 
     * @return array
     */
    public function getBindings()
    {
        return $this->_binding;
    }
    
    
    
    /**
     * Check if object provides any of the given bindings
     *
     * @param string $interface,...
     * @return boolean
     */
    public function hasBinding()
    {
        foreach(func_get_args() as $name) {
            if(isset($this->_binding[$name])) {
                return true;
            }
        }
        return false;
    }
    
    
    
    /**
     * Check if object provides all of the given bindings
     *
     * @param string $interface,...
     * @return boolean
     */
    public function hasAnyBindings()
    {
        foreach(func_get_args() as $name) {
            if(isset($this->_binding[$name])) {
                return true;
            }
        }
        return true;
    }

    
    /**
     * Check if object provides all of the given bindings
     *
     * @param string $interface,...
     * @return boolean
     */
    public function hasAllBindings()
    {
        foreach(func_get_args() as $name) {
            if(!isset($this->_binding[$name])) {
                return false;
            }
        }
        return true;
    }
    
    
    
    
    
    /**
     * 
     * 
     * @param string $key
     * @return mixed
     */
    public function getJoin($key)
    {
        if(isset($this->_joins[$key])) {
            return $this->_joins[$key];
        }
        return null;
    }
    
    
    
    

    /**
     * Join select statement to query
     * 
     * @param string $bind
     * @param Zend_Db_Select $select
     * @param string $alias
     * @param string $selectColumn
     * @return Mzax_Db_Select
     */
    public function joinSelect($bind, Zend_Db_Select $select, $alias)
    {
        if($this->getJoin($alias)) {
            return $this;
        }
        
        $cond = $this->_getJoinCondition($alias, $bind);
    
        $this->joinInner(array($alias => $select), $cond, null);
        $this->_joins[$alias] = $select;
        return $this;
    }
    
    
    
    /**
     * Join select statement to query
     *
     * @param string $bind
     * @param Zend_Db_Select $select
     * @param string $alias
     * @param string $selectColumn
     * @return Mzax_Db_Select
     */
    public function joinSelectLeft($bind, Zend_Db_Select $select, $alias)
    {
        if($this->getJoin($alias)) {
            return $this;
        }
    
        $cond = $this->_getJoinCondition($alias, $bind);
    
        $this->joinLeft(array($alias => $select), $cond, null);
        $this->_joins[$alias] = $select;
        return $this;
    }
    
    
    
    
    
    /**
     * Create simple join condition from $bind
     * 
     * e.g. 
     * 'customer_id'
     *     {customer_id} = 'alias'.'customer_id'
     *     
     * array('object_id' => '{customer_id}', 'object_type' => 1)
     *     {customer_id} = 'alias'.'object_id' AND 'alias'.'object_type' = 1
     * 
     * 
     * @param string $tableAlias
     * @param string|array $bind
     * @param string $tableColumn
     * @throws Exception
     * @return string
     */
    protected function _getJoinCondition($tableAlias, $bind, $tableColumn = null)
    {
        if(is_array($bind)) {
            $result = array();
            foreach($bind as $value => $cond) {
                if(is_int($value)) {
                    if($cond instanceof Zend_Db_Expr) {
                        $result[] = $cond;
                    }
                    else {
                        $result[] = $this->_getJoinCondition($tableAlias, $cond, $tableColumn);
                    }
                }
                else {
                    if(is_array($cond)) {
                        foreach($cond as $c) {
                            $result[] = $this->_getJoinCondition($tableAlias, $c, $value);
                        }
                    }
                    else {
                        $result[] = $this->_getJoinCondition($tableAlias, $cond, $value);
                    }
                    
                }
            }
            
            return implode(' '.self::SQL_AND.' ', $result);
        }
        if(!$tableColumn) {
            $tableColumn = trim($bind, '{}');
        }
        
        if(is_string($bind)) {
            $bind = $this->_getBindExpr($bind);
            if(is_string($bind)) {
                if(!$this->hasBinding($bind)) {
                    $this->addBinding($bind, "{$this->_tableAlias}.{$bind}");
                }
                $bind = '{' . $bind . '}';
            }
        }
        
        return "(`$tableAlias`.`$tableColumn` = $bind)";
    }
    
    
    
    
    /**
     * Check expression for binders and convert them
     * to valid placeholders
     * 
     * @param mixed $expr
     * @return mixed
     */
    protected function _getBindExpr($expr)
    {
        if(is_string($expr)) {
            if($this->hasBinding($expr)) {
                return new Zend_Db_Expr('{' . $expr . '}');
            }
            else if(preg_match('/\{.+\}/', $expr)) {
                return new Zend_Db_Expr($expr);
            }
            else if(preg_match('/.+\..+/', $expr)) {
                return new Zend_Db_Expr($expr);
            }
        }
        if(is_array($expr)) {
            foreach($expr as $i => $v) {
                $expr[$i] = $this->_getBindExpr($v);
            }
        }
        return $expr;
    }
    
    
    
    /**
     * Simple optional filter
     * 
     * @param string $field
     * @param mixed $value
     * @return Mzax_Db_Select
     */
    public function filter($field, $value) 
    {
        if($value !== null) {
            $field = $this->getAdapter()->quoteIdentifier($field, true);
            if(is_array($value)) {
                $this->where("$field IN(?)", $value);
            }
            else {
                $this->where("$field = ?", $value);
            }
        }
        return $this;
    }
    
    
    
    
    /**
     * 
     * @param string $spec Default id field
     * @return Mzax_Db_Select
     */
    public function group($spec = 1, $clear = false)
    {
        if(is_int($spec)) {
            $spec = new Zend_Db_Expr("$spec");
        }
        if($clear) {
            $this->reset(self::GROUP);
        }
        parent::group($this->_getBindExpr($spec));
        return $this;
    }
    
    
    
    
    
    
    /**
     * Add column to the select statment result
     *
     * @param string $alias
     * @param string $expr
     * @throws Exception
     * @return Mzax_Db_Select
     */
    public function setColumn($alias, $expr = null)
    {
        if(!$expr) {
            $expr = new Zend_Db_Expr('{' . $alias . '}');
        }
        
        $expr = $this->_getBindExpr($expr);
        
        // replace column if it does exist already
        foreach($this->_parts[self::COLUMNS] as &$column) {
            if(isset($column[2]) && $column[2] === $alias) {
                $column[1] = $expr;
                return $this;
            }
        }
        
        if($this->_lock) {
            throw new Zend_Db_Exception("Select is locked, unable to add column '$alias'");
        }
        
        $this->columns(array($alias => $expr));
        return $this;
    }
    
    
    
    /**
     * Seeks are optional columns that are initally set to NULL
     * but can be added 
     * 
     * @param string $what
     * @return Mzax_Db_Select
     */
    public function seek($what, $defaultExpr = 'NULL')
    {
        $expr = new Zend_Db_Expr($defaultExpr);
        $this->_seeks[$what] = $expr;
        $this->setColumn($what, $expr);
        return $this;
    }
    
    
    
    /**
     * Provide optional data which might be asked by a seek call
     * 
     * @param string $what
     * @param Zend_Db_Expr $expr
     */
    public function provide($what, Zend_Db_Expr $expr)
    {
        if(isset($this->_seeks[$what])) {
            $this->_seeks[$what] = $expr;
            $this->setColumn($what, $expr);
        }
        return $this;
    }
    
    
    
    
    /**
     * (non-PHPdoc)
     * @see Zend_Db_Select::assemble()
     */
    public function assemble()
    {
        $sql = parent::assemble();
        $bindings = $this->_binding;
        $select   = $this;
        $regex    = '/{([a-z_]+)}/i';
        
        $cb = function($match) use (&$cb, $regex, $bindings, $sql, $select) {
            if(isset($bindings[$match[1]])) {
                return preg_replace_callback($regex, $cb, $bindings[$match[1]]);
            }
            
            $exception = new Mzax_Db_Select_Exception("Binding '{$match[1]}' does not exist", 1001, $select);
            $exception->sql = $sql;
            
            throw $exception;
        };
        
        // replace all binding placeholders
        return preg_replace_callback($regex, $cb, $sql);
    }
    
    
    
    
    
    
    

    /**
     * Get insert from Select object query
     * 
     * Derived from Varien_Db_Adapter_Pdo_Mysql::insertFromSelect()
     * however the insert select should be in parenthèse or CROSS JOINTS
     * fail
     *
     * @todo Varien_Adapters need fix
     *
     * @param string $table     insert into table
     * @param array $fields
     * @param int $mode
     * @return string
     */
    public function insertFromSelect($table, $fields = array(), $mode = true)
    {
        $adapter = $this->getAdapter();
        
        if(!$fields) {
            $fields = $this->getFields();
        }
        if($mode === true) {
            $mode = $adapter::INSERT_ON_DUPLICATE;
        }
        
        $query = 'INSERT';
        if ($mode == $adapter::INSERT_IGNORE) {
            $query .= ' IGNORE';
        }
        $query = sprintf('%s INTO %s', $query, $adapter->quoteIdentifier($table));
        if ($fields) {
            $columns = array_map(array($adapter, 'quoteIdentifier'), $fields);
            $query = sprintf('%s (%s)', $query, join(', ', $columns));
        }
    
        $query = sprintf('%s (%s)', $query, $this->assemble());
    
        if ($mode == $adapter::INSERT_ON_DUPLICATE) {
            $update = array();
            foreach ($fields as $field) {
                $update[] = sprintf('%1$s = VALUES(%1$s)', $adapter->quoteIdentifier($field));
            }
    
            if ($update) {
                $query = sprintf('%s ON DUPLICATE KEY UPDATE %s', $query, join(', ', $update));
            }
        }
    
        return $query;
    }
    
    
}