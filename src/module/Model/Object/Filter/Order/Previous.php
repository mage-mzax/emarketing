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
class Mzax_Emarketing_Model_Object_Filter_Order_Previous
    extends Mzax_Emarketing_Model_Object_Filter_Order_Abstract
{
    
    const DEFAULT_AGGREGATOR = 'all';
    
    const DEFAULT_EXPECTATION = 'true';
    
    const DEFAULT_DIRECTION = 'preceded';
    


    /**
     * Can have children
     *
     * @var boolean
     */
    protected $_allowChildren = true;
    
    
    
    /**
     * Only works if parent object is the order object
     * 
     * @return boolean
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->getObject() === Mage::getSingleton('mzax_emarketing/object_order');
    }
    
    
    public function acceptChild(Mzax_Emarketing_Model_Object_Filter_Component $child)
    {
        return true;
    }
    
    
    
    
    public function getTitle()
    {
        return "Order | Has preceded or followed orders matching...";
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
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Component::getQuery()
     */
    public function getQuery()
    {
        $query = $this->getObject()->getQuery();
        $query->where('{customer_id} IS NOT NULL');
        $query->addBinding('order_increment_id', 'increment_id');
        $query->setColumn('customer_id');
        $query->setColumn('ordered_at');
        $query->setColumn('order_increment_id');
        return $query;
    }
    
    

    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator');
        $expectation = $this->getDataSetDefault('expectation');
        
        $subFilterSelect = $this->_combineConditions($conditions, $aggregator, $expectation);
        $subFilterSelect->useTemporaryTable($this->getTempTableName());
        
        $type = $this->getDataSetDefault('direction');
        $cond['customer_id'] = '{customer_id}';
        
        
        if ($type === 'preceded') {
            $cond[] = new Zend_Db_Expr('`prev_orders`.`ordered_at` < ' . '{ordered_at}');
            $cond[] = new Zend_Db_Expr('`prev_orders`.`ordered_at` > ' . $this->getTimeExpr('offset', '{ordered_at}', true));
        }
        else {
            $cond[] = new Zend_Db_Expr('`prev_orders`.`ordered_at` > ' . '{ordered_at}');
            $cond[] = new Zend_Db_Expr('`prev_orders`.`ordered_at` < ' . $this->getTimeExpr('offset', '{ordered_at}', true));
        }
        
        
        
        $query->joinSelect($cond, $subFilterSelect, 'prev_orders');
        $query->having($this->getWhereSql('orders', 'COUNT(`order_increment_id`)'));
        $query->group();
    }
    

    
    
    
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('prev_orders', new Zend_Db_Expr('GROUP_CONCAT(`prev_orders`.`order_increment_id` SEPARATOR ", ")'));
    }
    
    
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
    
        $grid->addColumn('prev_orders', array(
            'header'    => $this->__('Previous Orders'),
            'width'     => '180px',
            'index'     => 'prev_orders',
        ));
    }
    
    
    

    
    
    
    

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $aggregatorElement  = $this->getSelectElement('aggregator');
        $expectationElement = $this->getSelectElement('expectation');
        
        return $this->__('If number of %s orders, within %s and with %s of these conditions %s, %s:',
            $this->getSelectElement('direction')->toHtml(),
            $this->getTimeHtml('offset'),
            $aggregatorElement->toHtml(),
            $expectationElement->toHtml(),
            $this->getInputHtml('orders', 'numeric')
        );
    }
    
    
    
    protected function getDirectionOptions()
    {
        return array(
            'preceded' => $this->__('preceded'),
            'followed' => $this->__('followed'),
        );
    }
    
    

}
