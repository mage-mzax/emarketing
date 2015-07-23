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
class Mzax_Emarketing_Model_Object_Filter_Customer_Order
    extends Mzax_Emarketing_Model_Object_Filter_Customer_Abstract
{
    
    
    const DEFAULT_AGGREGATOR = 'all';
    
    const DEFAULT_EXPECTATION = 'true';
    
    const DEFAULT_SUM = '';
    

    protected $_allowChildren = true;
    
    

    /**
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::getTitle()
     */
    public function getTitle()
    {
        return "Customer | If number/grand-total of orders,...";
    }
    
    
    
    public function getQuery()
    {
        $query = $this->getObject()->getQuery();
        $query->setColumn('customer_id');
        $query->setColumn('order_id');
        
        if($sumField = $this->getDataSetDefault('sum', self::DEFAULT_SUM)) {
            $query->addBinding('sum_field', $sumField);
            $query->setColumn('sum_field');
        }
        
        return $query;
    }
    
    
    /**
     *
     * @return Mzax_Emarketing_Model_Object_Order
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_order');
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
        $sumField    = $this->getDataSetDefault('sum', self::DEFAULT_SUM);
        
        
        
        $select = $this->_combineConditions($conditions, $aggregator, $expectation);
        
        // Check if we are looking for customer with no orders
        if($this->checkIfMatchZero('orders')) {
            
            $customerId = new Zend_Db_Expr('`customer`.`entity_id`');
            
            // Query with all orders + customers with out any orders
            $zeroOrderQuery = $this->getQuery();
            $zeroOrderQuery->joinTableRight(array('entity_id' => '{customer_id}'), 'customer/entity', 'customer');
            $zeroOrderQuery->setColumn('matches', new Zend_Db_Expr('0'));
            $zeroOrderQuery->setColumn('customer_id', $customerId);
            $zeroOrderQuery->group($customerId, true);
            if($sumField) {
                $zeroOrderQuery->setColumn('sum_field', new Zend_Db_Expr('0'));
            }
            
            $select = $this->_select()->union(array($zeroOrderQuery, $select));
            
            
            if($sumField) {
                $query->having($this->getWhereSql('orders', 'SUM(`sum_field`)'));
            }
            else {
                // count customer_id AS order_id maybe NULL
                // reduce by 1 as we added zero order results as well.
                $query->having($this->getWhereSql('orders', 'COUNT({customer_id})-1'));
            }
        }        
        else {
            if($sumField) {
                $query->having($this->getWhereSql('orders', 'SUM(`sum_field`)'));
            }
            else {
                $query->having($this->getWhereSql('orders', 'COUNT(`filter`.`order_id`)'));
            }
            
        }
        $select->useTemporaryTable($this->getTempTableName());
        
        $query->joinSelect('customer_id', $select, 'filter', 'customer_id');
        $query->group();
    }
    
    

    

    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('customer_id');
        $collection->addField('orders', new Zend_Db_Expr('COUNT(DISTINCT `order_id`)'));
    }
    
    
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
        
        $grid->addColumn('orders', array(
            'header' => $this->__('Matching Orders'),
            'index'  => 'orders',
        ));
    
        $grid->setDefaultSort('count_orders');
        $grid->setDefaultDir('DESC');
    
    }
    
    
    
    
    
    
    
    
    
    /**
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::prepareForm()
     */
    protected function prepareForm()
    {
        $aggregatorElement  = $this->getSelectElement('aggregator',  self::DEFAULT_AGGREGATOR);
        $expectationElement = $this->getSelectElement('expectation', self::DEFAULT_EXPECTATION);
        
        return $this->__('If %s of orders, with %s of these conditions %s, %s:',
            $this->getSelectElement('sum')->toHtml(),
            $aggregatorElement->toHtml(),
            $expectationElement->toHtml(),
            $this->getInputHtml('orders', 'numeric')
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
            ''                         => $this->__('number'),
            'base_grand_total'         => $this->__('summed grand total'),
            'base_discount_invoiced'   => $this->__('summed discount amount'),
            'base_shipping_invoiced'   => $this->__('summed shipping amount'),
            'base_subtotal'            => $this->__('summed subtotal'),
            'base_tax_invoiced'        => $this->__('summed tax amount'),
            'base_total_invoiced'      => $this->__('summed invoiced amount'),
            'base_total_paid'          => $this->__('summed paid amount'),
            'base_total_due'           => $this->__('summed due amount'),
            'weight'                   => $this->__('summed weight'),
        );
    }

}
