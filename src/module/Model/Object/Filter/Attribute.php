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
 * Abstract Attribute filter
 * 
 * Define the entity type code and the required binding
 * and it will generate filters for all attributes
 * 
 * use method isAttributeAllowed() for fine tuning
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
abstract class Mzax_Emarketing_Model_Object_Filter_Attribute
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    
    const VALUE_KEY = 'value';
    
    
    
    /**
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_attribute;
    
    
    
    /**
     * 
     * @var string
     */
    protected $_entity;

    
    /**
     * 
     * @var string
     */
    protected $_requireBinding;
    
    
    
    protected $_attributeConfigs = array();
    

    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->hasBinding($this->_requireBinding);
    }
    
    
    
    protected function _prepareFilter(Mage_Core_Model_Config_Element $config)
    {
        if(isset($config->attributes)) {
            /* @var $attrCfg Mage_Core_Model_Config_Element */
            foreach($config->attributes->children() as $code => $cfg) {
                $this->_attributeConfigs[$code] = $cfg->asCanonicalArray();
            }
        }
        parent::_prepareFilter($config);
    }
    
    
    
    /**
     * Retrieve attribute config value
     * 
     * @param string $attribute
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getAttributeConfig($attribute, $key, $default = null)
    {
        if($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $attribute = $attribute->getAttributeCode();
        }
        if(isset($this->_attributeConfigs[$attribute][$key])) {
            return $this->_attributeConfigs[$attribute][$key];
        }
        return $default;
    }
    
    
    
    
    
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $attribute = $this->getAttribute();
        $adapter   = $query->getAdapter();
        $field     = $query->joinAttribute($this->_requireBinding, $attribute, true);
        
        $query->addBinding('attribute_value', $field);
        
        $operator  = $this->getDataSetDefault('operator', $this->helper()->getDefaultOperatorByType($this->getInputType()));
        $value     = $this->getData(self::VALUE_KEY);
        
        
        
        /*
         * Relative data attributes
         */
        if($this->getData('relative')) {
            
            $future = $this->getDirection() == 'future';
            $usesLocalTime = (bool) $this->getAttributeConfig($attribute, 'uses_local_time', false);
            
            if($this->getAnniversary()) {
                $query->where($this->getAnniversaryTimeExpr('{attribute_value}', self::VALUE_KEY, $future, $usesLocalTime));
            }
            else {
                $query->where($this->getTimeRangeExpr('{attribute_value}', self::VALUE_KEY, $future, $usesLocalTime));
            }
            return;
        }
        
        
        
        /*
         * Multi select attributes are saved as list in varchar
         * (e.g. 123,1457,124,21)
         * 
         * @todo can we use an index?
         */
        if($attribute->getFrontendInput() === 'multiselect')
        {
            $value = (array) $value;
            $where = array();
            foreach($value as $v) {
                $where[] = $adapter->quoteInto("FIND_IN_SET(?, {attribute_value})", $v);
            }
            if(strpos($operator, '()') !== false) {
                $where = implode(' OR ', $where);
            }
            else {
                $where = implode(' AND ', $where);
            }
        
            if(strpos($operator, '!') === 0) {
                $where = "!($where)";
            }
        
            $query->where($where);
            return;
        }
        
        switch($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':   return $query->where("{attribute_value} {$operator} ?", $this->_implode($value)); break;
            case '{}':  return $query->where("{attribute_value} LIKE ?", "%$value%"); break;
            case '!{}': return $query->where("{attribute_value} NOT LIKE ?", "%$value%"); break;
            case '()':  return $query->where("{attribute_value} IN (?)", $this->_explode($value)); break;
            case '!()': return $query->where("{attribute_value} NOT IN (?)", $this->_explode($value)); break;
            default:    return $query->where("{attribute_value} = ?", $this->_implode($value)); break;
        }
        
    }
    
    
    /**
     * Add attribute value to collection
     * 
     * @return void
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('attribute_value');
    }
    
    
    /**
     * Add attribute value to grid
     * 
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
                
        $grid->addColumn('attribute_value', array(
            'header'   => $this->getFrontendLabel(),
            'index'    => 'attribute_value',
            'type'     => $this->getColumnType(),
            'options'  => $this->getGridValueOptions(),
        ));
    }
    
    
    

    /**
     * Retrieve grid column type
     *
     * @return string
     */
    public function getColumnType()
    {
        switch($this->getInputType()) {
            case 'multiselect':
            case 'select':
            case 'boolean':
                return 'options';
        }
        return $this->getInputType();
    }
    


    /**
     * Retrieve value options for the grid
     *
     * @return array
     */
    public function getGridValueOptions()
    {
        return $this->getValueOptions();
    }
    
    
    
    
    
    /**
     * Retrieve attribute instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        if(!$this->_attribute) {
            $code = $this->getData('attribute');
            $this->_attribute = $this->_getAttribute($this->_entity . '/' . $code);
        }
        return $this->_attribute;
    }
    
    
    public function getFrontendLabel($attribute = null)
    {
        if(!$attribute) {
            $attribute = $this->getAttribute();
        }
        $label = $this->getAttributeConfig($attribute, 'label');
        if(!$label) {
            return $attribute->getFrontendLabel();
        }
        return$this->__($label);
    }
    
    

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $attribute = $this->getAttribute();
        if(!$attribute) {
            return "**ERROR** NO ATTRIBUTE SELECTED.";
        }
        
        $html = $this->getHiddenField('attribute', $attribute->getAttributeCode())->toHtml();
        
        if(!$this->getData('relative')) {
            $operatorElement = $this->getOperatorElement();
            $valueElement = $this->getValueElement();
            
            return $html. $this->__('%s %s %s.',
                $this->getFrontendLabel(),
                $operatorElement->toHtml(),
                $valueElement->toHtml()
            );
        }
        // relative date
        else {
            $html .= $this->getHiddenField('relative', 1)->toHtml();
            $html .= $this->getHiddenField('anniversary', $this->getAnniversary())->toHtml();
            $html .= $this->getHiddenField('direction', $this->getDirection())->toHtml();
            
            $timeRangeHtml = $this->getTimeRangeHtml(self::VALUE_KEY);
            
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
                $this->getFrontendLabel(),
                $timeRangeHtml
            );
            
        }
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
                if(count($this->getValueOptions()) <= 2) {
                    $element = $this->getSelectElement(self::VALUE_KEY);
                    break;
                }
                
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
        $attribute = $this->getAttribute();
        if($attribute->usesSource()) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            } else {
                $addEmptyOption = true;
            }
            $options = $attribute->getSource()->getAllOptions(false);
            $hash = array();
            foreach ($options as $o) {
                if (is_array($o['value'])) {
                    continue;
                }
                $hash[$o['value']] = $o['label'];
            }
            return $hash;
        }
        return array();
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
        switch ($this->getAttribute()->getFrontendInput()) {
            case 'select':      return 'select';
            case 'multiselect': return 'multiselect';
            case 'date':        return 'date';
            case 'boolean':     return 'boolean';
            case 'price':       return 'numeric';
        }
        return 'string';
    }
    
    
    
    
    
    public function getOptions()
    {
        $title = $this->getTitle();
        $type  = $this->getType();
        $options = array();
        
        $attributes = $this->getResourceHelper()->getEntity($this->_entity)
            ->loadAllAttributes()
            ->getAttributesByCode();
        
        /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        foreach($attributes as $attribute) {
            if($this->isAttributeAllowed($attribute)) {
                
                $label = $this->getFrontendLabel($attribute);
                
                $options[$type.'?attribute='.$attribute->getAttributeCode()] = "{$title} | {$label}";
                
                // dates can be filterd by a fixed date or relative to the time when checked
                if($attribute->getFrontendInput() === 'date') {
                    $options[$type.'?attribute='.$attribute->getAttributeCode().'?relative=1?direction=future'] = $title . ' | ' . $this->__('%s is in...', $label);
                    $options[$type.'?attribute='.$attribute->getAttributeCode().'?relative=1?direction=past']   = $title . ' | ' . $this->__('%s was ... ago', $label);
                    
                    $options[$type.'?attribute='.$attribute->getAttributeCode().'?relative=1?anniversary=1?direction=future'] = $title . ' | ' . $this->__('%s anniversary is in...', $label);
                    $options[$type.'?attribute='.$attribute->getAttributeCode().'?relative=1?anniversary=1?direction=past']   = $title . ' | ' . $this->__('%s anniversary was ... ago', $label);
                }
            }
        }
        
        asort($options);
        return $options;
    }
    
    
    protected function isAttributeAllowed(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if($this->getAttributeConfig($attribute, 'disable', false)) {
            return false;
        }
        if($attribute->getFrontendLabel()) {
            return true;
        }
        
        
        return false;
    }
    

}
