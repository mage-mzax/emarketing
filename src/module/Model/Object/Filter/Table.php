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
 * @method Mzax_Emarketing_Model_Object_Filter_Table setColumn(string $name)
 * @method Mzax_Emarketing_Model_Object_Filter_Table setRelative(boolean $flag)
 * @method Mzax_Emarketing_Model_Object_Filter_Table setValue(mixed $value)
 * @method Mzax_Emarketing_Model_Object_Filter_Table setOperator(string $value)
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
abstract class Mzax_Emarketing_Model_Object_Filter_Table
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    
    const VALUE_KEY = 'value';
    
    
    /**
     * Name of table e.g. sales/order
     * 
     * @var string
     */
    protected $_table;

    
    /**
     * Query alias
     * 
     * @var string
     */
    protected $_tableAlias;
    
    
    /**
     * Id field/column name
     * 
     * @var string
     */
    protected $_tableIdFieldName = 'entity_id';
    
    
    
    /**
     * Form text
     * 
     * 1. Column Name
     * 2. Operator (is, is not, contains,...)
     * 3. Value
     * 
     * @var string
     */
    protected $_formText = '%s %s %s.';
    
    
    
    /**
     * Form text for boolean checks
     *
     * 1. Column Name
     * 2. Value (Yes, No)
     * 
     * e.g 
     * Quote %s %s. =>  Quote [is|is not] "Virtual".
     *
     * @var string
     */
    protected $_boolFormText = '%s %s.';
    
    
    
    /**
     * The binding that is required to show this filter.
     * Usally this is the table id field (order_id, quote_id, customer_id,...)
     * 
     * @var string
     */
    protected $_requireBinding;
    
    
    
    /**
     * Use _registerColumns() and addColumn()
     * 
     * @internal
     * @see Mzax_Emarketing_Model_Object_Filter_Table::_registerColumns()
     * @see Mzax_Emarketing_Model_Object_Filter_Table::addColumn()
     * @var array
     */
    protected $_columnOptions = array();
    
    
    
    /**
     * Reference to the selected table column
     * 
     * @internal
     * @see Mzax_Emarketing_Model_Object_Filter_Table::getTableColumn()
     * @var array
     */
    protected $_tableColumn;
    
    
    
    /**
     * Abstract method to register all columns
     * 
     * @return void
     */
    abstract protected function _registerColumns();
    
    

    /**
     * 
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Component::acceptParent()
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->hasBinding($this->_requireBinding);
    }
    
    
    
    /**
     * 
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::_prepareFilter($config)
     */
    protected function _prepareFilter(Mage_Core_Model_Config_Element $config)
    {
        parent::_prepareFilter($config);
        $this->_registerColumns();
        Mage::dispatchEvent("mzax_emarketing_email_filter_prepare_table_" . $this->_tableAlias, array('filter' => $this));
    }
    
    
    
    
    /**
     * 
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Component::_prepareQuery()
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        /* @var $adapter Zend_Db_Adapter_Abstract */
        $column  = $this->getTableColumn();
        $adapter = $query->getAdapter();
        
        if(!$column) {
            throw new Exception("No valid table column defined");
        }
        
        if($this->_tableIdFieldName) {
            $bind = array($this->_tableIdFieldName => $this->_requireBinding);
        }
        else {
            $bind = $this->_requireBinding;
        }
        
        $query->joinTable($bind, $this->_table, $this->_tableAlias);
        $query->addBinding('column_value', $column->name, $this->_tableAlias);
        
        $operator  = $this->getDataSetDefault('operator', $this->helper()->getDefaultOperatorByType($this->getInputType()));
        $value     = $this->getData(self::VALUE_KEY);
        
        

        // relative date
        if($this->getData('relative')) {
            $future = $this->getDirection() == 'future';
            
            if($this->getAnniversary()) {
                $query->where($this->getAnniversaryTimeExpr('{column_value}', self::VALUE_KEY, $future, $column->applyGmtOffset));
            }
            else {
                $query->where($this->getTimeRangeExpr('{column_value}', self::VALUE_KEY, $future));
            }
            return;
        }
        
        if($column->type === 'boolean') {
            $value = '1';
        }
        
        switch($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':   return $query->where("{column_value} {$operator} ?", $value); break;
            case '{}':  return $query->where("{column_value} LIKE ?", "%$value%"); break;
            case '!{}': return $query->where("{column_value} NOT LIKE ?", "%$value%"); break;
            case '()':  return $query->where("{column_value} IN (?)", $this->_explode($value)); break;
            case '!()': return $query->where("{column_value} NOT IN (?)", $this->_explode($value)); break;
            default:    return $query->where("{column_value} = ?", $value); break;
        }
    }
    
    
    

    /**
     * 
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::_prepareCollection($collection)
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('column_value');
    }
    
    
    
    /**
     * 
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Component::prepareGridColumns()
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
        
        if($column = $this->getTableColumn()) 
        {
            $grid->addColumn('column_value', array(
                'header'    => ucfirst($column->label),
                'type'      => $this->getColumnType(),
                'options'   => $this->getGridValueOptions(),
                'index'     => 'column_value',
                'currency'  => 'currency_code',
                'gmtoffset' => true
            ));
        }
        
    }
    
    
    

    /**
     * Retrieve table column name
     *
     * @return array
     */
    public function getTableColumn()
    {
        if( $this->_tableColumn === null) {
            $column = $this->getData('column');
            if(isset($this->_columnOptions[$column])) {
                $this->_tableColumn = $this->_columnOptions[$column];
            }
            else {
                $this->_tableColumn = false;
            }
            
        }
        return $this->_tableColumn;
    }
    
    
    
    

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $column = $this->getTableColumn();
        if(!$column) {
            return "**ERROR** NO COLUMN SELECTED.";
        }
        
        $html = $this->getHiddenField('column', $column->name)->toHtml();
        
        $operatorElement = $this->getOperatorElement();
        $valueElement = $this->getValueElement();
        
        
        if($this->getData('relative')) {
            
            $timeRangeHtml = $this->getTimeRangeHtml(self::VALUE_KEY);
            
            $html .= $this->getHiddenField('relative', 1)->toHtml();
            $html .= $this->getHiddenField('anniversary', $this->getAnniversary())->toHtml();
            $html .= $this->getHiddenField('direction', $this->getDirection())->toHtml();
            
            $timeRangeHtml = $this->getTimeRangeHtml(self::VALUE_KEY);
            $timeDirHtml   = $this->getTimeDirectionHtml(self::VALUE_KEY);
            
            if($this->getAnniversary()) {
                $text = $this->getDirection() == 'future'
                    ? '%s anniversary is in %s.'
                    : '%s anniversary was %s ago.';
            }
            else {
                $text = $this->getDirection() == 'future'
                    ? '%s is in %s.'
                    : '%s was %s ago.';
            }
            
            return $html. $this->__($text,
                $column->label,
                $timeRangeHtml
            );
        }
        
        
        switch($column->type) {
            case 'boolean':
                return $html . $this->__($this->_boolFormText,
                    $operatorElement->toHtml(),
                    $column->label
                );
        }
        
        return $html . $this->__($this->_formText,
            $column->label,
            $operatorElement->toHtml(),
            $valueElement->toHtml()
        );
    }
    
    
    
    
    
    /**
     * Retrieve operator select element
     * 
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getOperatorElement()
    {
        $type    = $this->getInputType();
        $default = $this->helper()->getDefaultOperatorByType($type);
        $options = $this->helper()->getOperatorOptionsByType($type);
        
        return $this->getSelectElement('operator', $default, $options);
    }
    
    
    
    /**
     * Retroeve value form element
     * 
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $type = $this->getInputType();
        
        switch($type) {
            
            case 'date':
                $element = $this->getDateElement(self::VALUE_KEY);
                break;
            
            case 'select':
            case 'boolean':
                $element = $this->getSelectElement(self::VALUE_KEY);
                break;
                
            case 'multiselect':
                $element = $this->getMultiSelectElement(self::VALUE_KEY);
                break;
                
            default:
                $element = $this->getInputElement(self::VALUE_KEY);
                break;
        }
        
        if($this->getChooserUrl()) {
            $element->setExplicitApply(true);
            $element->setAfterElementHtml($this->getChooserTriggerHtml());
        }
        
        return $element;
    }
    
    
    
    
    
    
    /**
     * Retrieve all value options as hash
     * 
     * array(value => label,...)
     * 
     * @return array
     */
    public function getValueOptions()
    {
        if($column = $this->getTableColumn()) {
            switch($column->type) {
                case 'boolean':
                    return array(
                        '1' => $this->__('is'), 
                        '0' => $this->__('is not')
                    );
            }
            return $column->options;
        }        
        return array();
    }
    
    
    
    /**
     * Retrieve value options for the grid
     *
     * @return array
     */
    public function getGridValueOptions()
    {
        if($column = $this->getTableColumn()) {
            switch($column->type) {
                case 'boolean':
                    return array(
                        '1' => $this->__('Yes'),
                        '0' => $this->__('No')
                    );
            }
            if(isset($column->gridOptions)) {
                return $column->gridOptions;
            }
        }
        return $this->getValueOptions();
    }
    
    
    
    /**
     * Add hidden input field
     * 
     * @param string $name
     * @param string $value
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function getHiddenField($name, $value)
    {
        return $this->getForm()->addField($name, 'hidden', array(
            'name'    => $name,
            'class'   => 'hidden',
            'no_span' => true,
            'is_meta' => true,
            'value'   => $value
        ));
    }
    
    
    
    
    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if($column = $this->getTableColumn()) {
            switch($column->type) {
                case 'price':
                    return 'numeric';
            }
            
            return $column->type;
        }
        return 'string';
    }
    
    
    
    

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getColumnType()
    {
        if($column = $this->getTableColumn()) 
        {
            switch($column->type) {
                case 'multiselect':
                case 'select':
                case 'boolean':                 
                    return 'options';
            }
            return $column->type;
        }
        return 'string';
    }
    
    
    
    
    
    /**
     * Register column to this table filter
     * 
     * @param string $name
     * @param string $label
     * @param string $type
     * @param array $options
     * @return stdClass
     */
    public function addColumn($name, $label, $type = 'numeric', $options = array())
    {
        // assume config source model
        if(is_string($options)) {
            $source  = Mage::getSingleton($options);
            $options = array();
            if(method_exists($source, 'toOptionArray')) {
                // we need a hash, not an array
                foreach($source->toOptionArray() as $option) {
                    $options[$option['value']] = $option['label'];
                }
            }
        }
        
        $column = (object) array(
            'name'             => $name,
            'label'            => $this->__($label),
            'type'             => $type,
            'options'          => $options,
            'allowFuture'      => true,
            'allowPast'        => true,
            'allowAnniversary' => true,
            'applyGmtOffset'   => false
        );
        
        $this->_columnOptions[$name] = $column;
        return $column;
    }
    
    
    
    
    /**
     * Retrieve all column options available
     * 
     * @return array
     */
    public function getColumnOptions()
    {
        return $this->_columnOptions;
    }
    
    
    
    
    
    public function getOptions()
    {
        $title = $this->getTitle();
        $type  = $this->getType();
        $options = array();
        
        foreach($this->getColumnOptions() as $column) 
        {
            $options[$type.'?column=' . $column->name] = "{$title} | {$column->label}";
            if($column->type === 'date') {
                
                if($column->allowFuture) {
                    $options[$type.'?column='.$column->name.'?relative=1?direction=future'] = $title . ' | ' . $this->__('%s is in...', $column->label); 
                }
                if($column->allowPast) {
                    $options[$type.'?column='.$column->name.'?relative=1?direction=past']   = $title . ' | ' . $this->__('%s was ... ago', $column->label);
                }
                if($column->allowAnniversary) {
                    $options[$type.'?column='.$column->name.'?relative=1?anniversary=1?direction=future'] = $title . ' | ' . $this->__('%s anniversary is in...', $column->label);
                    $options[$type.'?column='.$column->name.'?relative=1?anniversary=1?direction=past']   = $title . ' | ' . $this->__('%s anniversary was ... ago', $column->label);
                }
            }
        }
        
        asort($options);
        return $options;
    }
    

}
