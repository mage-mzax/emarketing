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



class Mzax_Emarketing_Db_Select extends Mzax_Db_Select
{
    
    
    static protected $_assambleAll = false;
    
    static protected $_tempTables = array();
    
    protected $_temporaryTable = false;
    
    
    /**
     * If select uses temproary table use table join instead
     * 
     * @param string $bind
     * @param Zend_Db_Select $select
     * @param string $alias
     * @param string $selectColumn
     * @return Mzax_Emarketing_Db_Select
     */
    public function joinSelect($bind, Zend_Db_Select $select, $alias)
    {
        if($select instanceof Mzax_Emarketing_Db_Select && ($table = $select->getTemporaryTable())) {
            if($select->createTemporaryTable()) {
                return $this->joinTable($bind, new Zend_Db_Expr($table), $alias);
            }
        }
        return parent::joinSelect($bind, $select, $alias);
    }
    
    
    /**
     * If select uses temproary table use table join instead
     *
     * @param string $bind
     * @param Zend_Db_Select $select
     * @param string $alias
     * @param string $selectColumn
     * @return Mzax_Emarketing_Db_Select
     */
    public function joinSelectLeft($bind, Zend_Db_Select $select, $alias)
    {
        if($select instanceof Mzax_Emarketing_Db_Select && ($table = $select->getTemporaryTable())) {
            if($select->createTemporaryTable()) {
                return $this->joinTableLeft($bind, new Zend_Db_Expr($table), $alias);
            }
        }
        return parent::joinSelectLeft($bind, $select, $alias);
    }
    
    
    /**
     * Use temporary table for this select object
     * 
     * @param string $tableName
     * @return Mzax_Emarketing_Db_Select
     */
    public function useTemporaryTable($tableName)
    {
        $this->_temporaryTable = $tableName ? $tableName : false;
        return $this;
    }
    
    
    
    /**
     * Retrieve temporary table if available
     * 
     * @return boolean|string
     */
    public function getTemporaryTable()
    {
        if($this->_temporaryTable) {
            return $this->_temporaryTable;
        }
        return false;
    }
    
    
    
    /**
     * Create temporary table with this select object
     * 
     * @return boolean|multitype:|string
     */
    public function createTemporaryTable()
    {
        $name = $this->getTemporaryTable();
        if(!$name) {
            return false;
        }
        
        $adapter = $this->getResourceHelper()->getAdapter();
        
        if(isset(self::$_tempTables[$name])) {
            return self::$_tempTables[$name];
        }
        
    
        $sql[] = "CREATE TEMPORARY TABLE IF NOT EXISTS `$name`";
        $sql[] = "ENGINE = MEMORY";
        $sql[] =  parent::assemble();
        
        try {
            $adapter->query(implode("\n", $sql));
        }
        catch(Exception $e) {
            Mage::logException($e);
            if(Mage::getIsDeveloperMode()) {
                die($e->getMessage() . "<br />\n<br />\n<br />\n" . implode("\n", $sql));
            }
            return self::$_tempTables[$name] = false;
        }
        
        return self::$_tempTables[$name] = "SELECT * FROM `$name`";
    }
    
    
    
    /**
     * Assamble the select object without using temporary
     * tables
     * 
     * @return string
     */
    public function assembleAll()
    {
        self::$_assambleAll = true;
        try {
            $sql =  parent::assemble();
            self::$_assambleAll = false;
            return $sql;
        } 
        catch(Exception $e) {
            self::$_assambleAll = false;
            throw $e;
        }
    }
    
    
    
    /**
     * Allow to use temporary tabes for this filter
     * result
     * 
     * @return string
     */
    public function assemble()
    {
        if(!self::$_assambleAll && $sql = $this->createTemporaryTable()) {
            return $sql;
        }
        else {
            return parent::assemble();
        }
    }
    
    
    
    
    
    
    /**
     * Join attribute to current query
     * 
     * Will return an expression that points to the value column
     * 
     * @param string $attribute
     * @param string $binding Optional binding column
     * @param boolean $joinStatic Join attribute even if static
     * @return Zend_Db_Expr Expression that points to the value column
     */
    public function joinAttribute($bind, $attribute, $joinStatic = false)
    {
        if($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $joinKey = "{$attribute->getEntityType()->getEntityTypeCode()}/{$attribute->getAttributeCode()}";
        } 
        else {
            $joinKey = $attribute;
        }
        
        if($this->getJoin($joinKey)) {
            return $this->getJoin($joinKey);
        }
        
        $this->_joins[$joinKey] = $this->getResourceHelper()->joinAttribute($this, $attribute, $this->_getBindExpr($bind), null, $joinStatic);
        return $this->_joins[$joinKey];
    }
    
    
    
    
    
    /**
     * Preform a right table join using the binder
     * 
     * @param string|array $bind
     * @param string $table
     * @param string $tableAlias
     * @return Mzax_Emarketing_Db_Select
     */
    public function joinTableRight($bind, $table, $tableAlias = null)
    {
        return $this->_joinTable('Right', $bind, $table, $tableAlias);
    }
    
    
    
    /**
     * Preform a left table join using the binder
     * 
     * @param string|array $binding
     * @param string $table
     * @param string $tableAlias
     * @return Mzax_Emarketing_Db_Select
     */
    public function joinTableLeft($bind, $table, $tableAlias = null)
    {
        return $this->_joinTable('Left', $bind, $table, $tableAlias);
    }
    
    
    
    /**
     * Preform a inner table join using the binder
     * 
     * @param string|array $binding
     * @param string $table
     * @param string $tableAlias
     * @return Mzax_Emarketing_Db_Select
     */
    public function joinTable($bind, $table, $tableAlias = null)
    {
        return $this->_joinTable('Inner', $bind, $table, $tableAlias);
    }
    
    
    
    /**
     * Preform a table join using the binder
     * 
     * @param string $joinType
     * @param string|array $binding
     * @param string $table
     * @param string $tableAlias
     * @return Mzax_Emarketing_Db_Select
     */
    protected function _joinTable($joinType, $bind, $table, $tableAlias = null)
    {
        if(!$tableAlias) {
            $tableAlias = $table;
        }
        if(isset($this->_joins[$tableAlias])) {
            return $this;
        }
                
        $joinFunc = "join{$joinType}";
        
        // assume that same name for binding and column
        if(is_string($bind)) {
            //$bind = array($bind => '{' . $bind . '}');
        }
        
        $tableName = $this->getTable($table);
        $cond = $this->_getJoinCondition($tableAlias, $bind);
        $this->$joinFunc(array($tableAlias => $tableName), $cond, null);
        $this->_joins[$tableAlias] = $tableName;
        return $this;
    }
    
    
    
    
    
    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     */
    public function getTable($table)
    {
        return $this->getResourceHelper()->getTable($table);
    }
    
    
    
    
    
    /**
     * Retrieve resource helper
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }
    
    
    
}