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



class Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
{
    
    /**
     * 
     * @var Mzax_Emarketing_Db_Select
     */
    protected $_select;
    
    
    /**
     * List of all registered selects
     * 
     * @var array
     */
    protected $_bindings = array();
    
    
    
    
    
    
    /**
     * Set base select
     * 
     * @param Mzax_Emarketing_Db_Select $select
     * @return Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
     */
    public function setSelect(Mzax_Emarketing_Db_Select $select)
    {
        $this->_select = $select;
        return $this;
    }
    
    
    /**
     * Check if binder has the specified binding
     * 
     * @param string $name
     * @return boolean
     */
    public function hasBinding($name)
    {
        if ($this->_select) {
            return $this->_select->hasBinding($name);
        }
        return false;
    }
    
    
    /**
     * Create new binding select
     * 
     * @return Mzax_Emarketing_Db_Select
     */
    public function createBinding()
    {
        if (!$this->_select) {
            throw new Exception("Unable to create binding, no select defined");
        }
        $select = clone $this->_select;
        $this->_bindings[] = $select->lock();
        
        return $select;
    }
    
    
    
    /**
     * Create new binding select
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getSelect()
    {
        $unions = array();
        
        /* @var $select Mzax_Emarketing_Db_Select */
        foreach ($this->_bindings as $select) {
            // skip if not locked
            if (!$select->locked()) {
                continue;
            }
            // skip if assemble fails
            try {
                $select->assemble();
            }
            catch(Mzax_Db_Select_Exception $e) {
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
            }
            catch(Exception $e) {
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                
                $message = "Failed to assamble goal binder select:";
                $message.= $e->getMessage() . "\n";
                $message.= $e->getTraceAsString();
                
                Mage::log($message, Zend_Log::WARN);
                Mage::logException($e);
                continue;
            }
            
            $unions[] = $select;
        }
        
        switch(count($unions)) {
            case 0: return null;
            case 1: return $unions[0];
        }
        
    
        $select = $this->getResourceHelper()->select();
        $select->union($unions, Zend_Db_Select::SQL_UNION);
        
        return $select;
    }
    
    


    /**
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }
    
}








class Mzax_Emarketing_Model_Resource_Recipient_Goal_BinderOld
{
    
    /**
     * 
     * @var Zend_Db_Select
     */
    protected $_direct;
    
    
    protected $_bindings = array();
    
    
    protected $_unionSelects = array();
    
    
    protected $_columns = array('recipient_id');
    
    
    protected $_where = array();
    
    
    
    public function setColumns($column)
    {
        $this->_columns = (array) $column;
        return $this;
    }
    
    
    /**
     * Retrieve select object for current binding
     * 
     * @param boolean $useIndirectBindings
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        $unions = array();
        if ($this->_direct) {
            $unions[] = $this->_direct;
        }
        
        $unions = array_merge($unions, $this->_unionSelects);
        
        if (empty($unions)) {
            return null;
        }
        
        $final = array();
        foreach ($unions as $select) {
            if ($this->_prepareSelect($select)) {
                $final[] = $select;
            }
        }
        
        if (count($final) === 1) {
            $select = $final[0];
        }
        else {
            $select = $this->getResourceHelper()->getWriteAdapter()->select();
            $select->union($final, Zend_Db_Select::SQL_UNION);
        }
        
        return $select;
    }
    
    
    
    
    
    
    /**
     * A direct binding points to recipient directly,
     * means that we have a datafield that points to the recipient
     * directly, not using any customer_id, order_id, email etc to get to it
     * 
     * @param Zend_Db_Select $select
     * @return Zend_Db_Select
     */
    public function addDirectBinding(Zend_Db_Select $select, $recipientIdField)
    {
        $this->_direct = clone $select;
        return $this->_direct;
    }
    
    
    
    /**
     * An indirect binding does not need to point to the recipient directly,
     * we can use the field like customer_id, email, or anthing else to make the
     * link to the recipient.
     * 
     * @param string $name
     * @param Zend_Db_Select $select
     * @param string $field
     * @return Mzax_Emarketing_Model_Conversion_Select
     */
    public function addIndirectBinding($name, Zend_Db_Select $select, $field)
    {
        $this->_bindings[$name] = array(
            'select' => $select, 
            'field'  => $field
        );
        return $this;
    }
    
    
    
    
    
    /**
     * Check if we can bind
     * 
     * @param string $name
     * @return string
     */
    public function getBinding($name)
    {
        if (isset($this->_bindings[$name])) {
            return $this->_bindings[$name]['field'];
        }
        return false;
    }
    
    
    
    /**
     * Bind using binging select
     * 
     * @param string $name
     * @return Zend_Db_Select
     */
    public function bind($name)
    {
        /* @var $select Zend_Db_Select */
        $select = clone $this->_bindings[$name]['select'];
        $this->_unionSelects[] = $select;
        


        
        return $select;
    }
    

    
    /**
     * Add where condition for recipient
     * 
     * @param string $fieldName
     * @param mixed $condition
     * @return Mzax_Emarketing_Model_Resource_Recipient_Binding
     */
    public function addFilter($fieldName, $condition)
    {
        $adapter = $this->getResourceHelper()->getReadAdapter();
        $fieldName = $adapter->quoteIdentifier("recipient.$fieldName");
        $this->_where[] = $adapter->prepareSqlCondition($fieldName, $condition);
        return $this;
    }
    
    
    
    
    
    protected function _prepareSelect(Zend_Db_Select $select)
    {
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        
        // the recipient table is required!
        if (!isset($fromPart['recipient'])) {
            return false;
        }
        
        foreach ($this->_where as $where) {
            $select->where($where);
        }
        
        $select->columns($this->_columns, 'recipient');
        return true;
    }
    
    
    
    
    /**
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }
    
    
    
    
}
