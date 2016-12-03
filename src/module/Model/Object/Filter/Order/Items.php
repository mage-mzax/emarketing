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
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Object_Filter_Order_Items
    extends Mzax_Emarketing_Model_Object_Filter_Order_Abstract
{
    
    const DEFAULT_AGGREGATOR = 'all';
    
    const DEFAULT_EXPECTATION = 'true';
    
    const DEFAULT_SUM = 'qty_ordered';
    


    protected $_allowChildren = true;
    
    
    
    
    public function getTitle()
    {
        return "Order | Items subselection matches...";
    }
    
    
    
    /**
     * Use order item object
     * 
     * @return Mzax_Emarketing_Model_Object_OrderItem
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_orderItem');
    }
    
    
    
    /**
     * Setup query for child filters
     * we need the order id and the sum_field
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $sumField = $this->getDataSetDefault('sum');
        
        $query = $this->getObject()->getQuery();
        $query->addBinding('sum_field', $sumField);
        $query->setColumn('order_id');
        $query->setColumn('sum_field');
        
        return $query;
    }
    
    

    
    
    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator',  self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);
        
        $select = $this->_combineConditions($conditions, $aggregator, $expectation);
        
        // if value can match zero include all records
        if ($this->checkIfMatchZero('value')) {
        
            $zeroRecords = $this->getQuery();
            // assume all orders have items, no right join required
            $zeroRecords->setColumn('sum_field', new Zend_Db_Expr('0'));
            $zeroRecords->setColumn('matches', new Zend_Db_Expr('0'));
        
            $select = $this->_select()->union(array($zeroRecords, $select));
        }
        
        $query->useTemporaryTable($this->getTempTableName());
        $query->joinSelect('order_id', $select, 'filter');
        $query->addBinding('value', new Zend_Db_Expr('SUM(`filter`.`sum_field`)'));
        $query->having($this->getWhereSql('value', '{value}'));
        $query->group();
    }
    
    
    

    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('value');
    }
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
    
        $sumOptions = $this->getSumOptions();
        if (isset($sumOptions[$this->getSum()])) {
            $title = ucwords($sumOptions[$this->getSum()]);
        }
        else {
            $title = $this->__('Total');
        }
    
        $grid->addColumn('value', array(
            'header'    => $title,
            'index'     => 'value',
            'type'      => 'number'
        ));
        
        $grid->setDefaultSort('increment_id');
        $grid->setDefaultDir('DESC');
    
    }
    
    
    
    
    
    

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return $this->__('If total %s %s for subselection of items matching %s of these conditions:',
            $this->getSelectElement('sum')->toHtml(),
            $this->getInputHtml('value', 'numeric'),
            $this->getSelectElement('aggregator',  'all')->toHtml()
         );
    }
    
    
    
    /**
     * List of fields to sum up and check against
     * 
     * @return return array
     */
    protected function getSumOptions()
    {
        return array(
            'qty_ordered'           => $this->__('quanity ordered'),
            'qty_invoiced'          => $this->__('quanity invoiced'),
            'qty_shipped'           => $this->__('quanity shipped'),
            'qty_canceled'          => $this->__('quanity canceled'),
            'base_row_total'        => $this->__('amount'),
            'base_discount_amount'  => $this->__('discount amount'),
            'base_row_invoiced'     => $this->__('invoice amount'),
            'base_amount_refunded'  => $this->__('refund amount'),
            'base_tax_amount'       => $this->__('tax amount'),
            'row_weight'            => $this->__('weight'),
            'base_cost'             => $this->__('cost'),
        );
    }
    
}
