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
 * @author Jacob Siefer
 *
 */
abstract class Mzax_Emarketing_Model_Object_Filter_Column
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{

    const VALUE_KEY = 'value';



    protected $_formText = '%s %s %s.';


    /**
     *
     * @var string
     */
    protected $_requireBinding;

    protected $_label;


    protected $_inputType = 'string';




    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->hasBinding($this->_requireBinding);
    }




    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        /* @var $adapter Zend_Db_Adapter_Abstract */
        $adapter = $query->getAdapter();

        $operator  = $this->getDataSetDefault('operator', $this->helper()->getDefaultOperatorByType($this->_inputType));
        $value     = $this->getData(self::VALUE_KEY);

        if ($this->_inputType === 'boolean') {
            $value = '1';
        }

        switch($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':   return $query->where("{{$this->_requireBinding}} {$operator} ?", $value); break;
            case '{}':  return $query->where("{{$this->_requireBinding}} LIKE ?", "%$value%"); break;
            case '!{}': return $query->where("{{$this->_requireBinding}} NOT LIKE ?", "%$value%"); break;
            case '()':  return $query->where("{{$this->_requireBinding}} IN (?)", $this->_explode($value)); break;
            case '!()': return $query->where("{{$this->_requireBinding}} NOT IN (?)", $this->_explode($value)); break;
            default:    return $query->where("{{$this->_requireBinding}} = ?", $value); break;
        }
    }




    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField($this->_requireBinding);
    }


    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);


        if (!$grid->getColumn($this->_requireBinding))
        {
            $grid->addColumn($this->_requireBinding, array(
                'header'   => $this->_label,
                'type'     => $this->getColumnType(),
                'options'  => $this->getGridValueOptions(),
                'index'    => $this->_requireBinding
            ));
        }
    }






    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $operatorElement = $this->getOperatorElement();
        $valueElement = $this->getValueElement();

        switch($this->_inputType) {
            case 'boolean':
                return $html . $this->__($this->_formText,
                    $operatorElement->toHtml(),
                    $this->_label
                );
        }

        return $this->__($this->_formText,
            $this->_label,
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
        $default = $this->helper()->getDefaultOperatorByType($this->_inputType);
        $options = $this->helper()->getOperatorOptionsByType($this->_inputType);

        return $this->getSelectElement('operator', $default, $options);
    }



    /**
     * Retroeve value form element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        switch($this->_inputType) {

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

        if ($this->getChooserUrl()) {
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
        switch($this->_inputType) {
            case 'boolean':
                return array(
                    '1' => $this->__('is'),
                    '0' => $this->__('is not')
                );
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
        switch($this->_inputType) {
            case 'boolean':
                return array(
                    '1' => $this->__('Yes'),
                    '0' => $this->__('No')
                );
        }
        return $this->getValueOptions();
    }








    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getColumnType()
    {
        switch($this->_inputType) {
            case 'multiselect':
            case 'select':
            case 'boolean':
                return 'options';
        }
        return 'text';
    }





}
