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
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quote table filters
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Model_Object_Filter_Quote_Table
    extends Mzax_Emarketing_Model_Object_Filter_Table
{

    /**
     *
     * @var string
     */
    protected $_table = 'sales/quote';


    protected $_tableAlias = 'sales_quote';



    protected $_tableIdFieldName = 'entity_id';

    /**
     *
     * @var string
     */
    protected $_requireBinding = 'quote_id';



    protected $_formText = 'Quote `%s` %s %s.';


    protected $_boolFormText = 'Quote %s `%s`.';




    public function getTitle()
    {
        return "Shopping Cart / Quote";
    }


    protected function _registerColumns()
    {
        $column = $this->addColumn('created_at', 'Created Date',  'date');
        $column->allowFuture = false;

        $column = $this->addColumn('updated_at', 'Last Update',   'date');
        $column->allowFuture = false;

        $this->addColumn('store_id',             'Store',          'multiselect', 'adminhtml/system_config_source_store');
        $this->addColumn('base_grand_total',     'Grand Total',    'currency');
        $this->addColumn('base_subtotal',        'Subtotal',       'currency');
        $this->addColumn('is_virtual',           'Virtual',        'boolean');
        $this->addColumn('customer_is_guest',    'Guest Checkout', 'boolean');
        $this->addColumn('base_subtotal_with_discount', 'Subtotal with Discount',   'currency');
        $this->addColumn('customer_email',       'Customer Email',       'string');
        $this->addColumn('customer_firstname',   'Customer Firstname',   'string');
        $this->addColumn('customer_lastname',    'Customer Lastname',    'string');
    }




    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);

        // add currency code for price columns
        $collection->addField('currency_code', "`$this->_tableAlias`.`base_currency_code`");
    }




}
