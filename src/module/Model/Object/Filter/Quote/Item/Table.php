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
 * Setup filters for the quote item table
 * 
 * 
 * Missing something?
 * use event "mzax_emarketing_email_filter_prepare_table_sales_quote_item"
 * to register any custom columns
 * 
 * 
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Object_Filter_Quote_Item_Table
    extends Mzax_Emarketing_Model_Object_Filter_Table
{
    
    /**
     *
     * @var string
     */
    protected $_table = 'sales/quote_item';
    
    
    protected $_tableAlias = 'sales_quote_item';
    
    

    protected $_tableIdFieldName = 'item_id';

    /**
     *
     * @var string
     */
    protected $_requireBinding = 'quote_item_id';
    
    

    protected $_formText = 'Quote Item `%s` %s %s.';
    

    protected $_boolFormText = 'Quote Item %s `%s`.';

    
    
    /**
     * Group title
     * 
     * @return string
     */
    public function getTitle()
    {
        return "Quote Item";
    }
    
    
    
    /**
     * Register column filters
     * 
     * Use mzax_emarketing_email_filter_prepare_table_sales_order_item event to register more
     * 
     * @return void
     */
    protected function _registerColumns()
    {
        $this->addColumn('product_type', 'Product Type', 'multiselect', Mage::getSingleton('catalog/product_type')->getOptionArray());
        
        $this->addColumn('sku', 'SKU',   'string');
        
        $this->addColumn('base_price',          'Price',              'price');
        $this->addColumn('custom_price',        'Custom Price',   'price');
        
        $this->addColumn('base_row_total',       'Row Total',   'price');
        $this->addColumn('base_discount_amount', 'Discount Amount',   'price');
        $this->addColumn('base_tax_amount',      'Tax Amount',        'price');
        
        $this->addColumn('qty',   'Quantity',     'numeric');
        
        $this->addColumn('row_weight',       'Row Weight',   'numeric');
        
        $this->addColumn('is_virtual',       'Virtual',        'boolean');
        $this->addColumn('free_shipping',    'Free Shipping',  'boolean');
    }
    
    
    
    
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        
        // add currency code for price columns
        $collection->getQuery()->joinTable(array('entity_id' => '{quote_id}'), 'sales/quote', 'quote');
        $collection->addField('currency_code', "`quote`.`base_currency_code`");
    }
    
    
    

}
