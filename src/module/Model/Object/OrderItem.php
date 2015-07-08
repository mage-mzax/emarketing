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
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Object_OrderItem extends Mzax_Emarketing_Model_Object_Abstract
{
    
    public function _construct()
    {
        $this->_init('sales/order_item');
    }
    
    
    
    public function getName()
    {
        return $this->__('Order Item');
    }
    
    
    public function getAdminUrl($id)
    {
        return $this->getUrl('admin_mzax_emarketing/admin_shortcut/orderItem', array('id' => $id));
    }
    
    
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('order_id',      'order_id');
        $query->addBinding('product_id',    'product_id');
        $query->addBinding('order_item_id', 'item_id');
    
        return $query;
    }
    
    
    
    public function prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::prepareCollection($collection);
        
        $collection->addField('order_item_id');
        $collection->addField('product_type');
        $collection->addField('product_id');
        $collection->addField('sku');
        $collection->addField('name');
        $collection->addField('qty_invoiced');
    }
    
    
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        foreach(Mage::getSingleton('catalog/product_type')->getOptions() as $option) {
            $productTypes[$option['value']] = $option['label'];
        }
        
        $grid->addColumn('product_type', array(
            'header'    => $this->__('Product Type'),
            'index'     => 'product_type',
            'type'      => 'options',
            'options'   => $productTypes
        ));
        
        
        /*
        $grid->addColumn('product', array(
            'header'      => $this->__('Product SKU'),
            'is_system'   => true,
            'width'	      => '25%',
            'id_field'    => 'product_id',
            'label_field' => 'sku',
            'renderer'    => 'mzax_emarketing/recipients_column_renderer_subject',
            'object'      => Mage::getSingleton('mzax_emarketing/object_product'),
        ));
        */
        
        $grid->addColumn('sku', array(
            'header' => Mage::helper('sales')->__('SKU'),
            'index' => 'sku',
            'width' => '30%'
        ));
        
        $grid->addColumn('name', array(
            'header' => Mage::helper('sales')->__('Name'),
            'index' => 'name',
        ));
    }
    
}
