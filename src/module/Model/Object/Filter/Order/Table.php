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
 * Class Mzax_Emarketing_Model_Object_Filter_Order_Table
 */
class Mzax_Emarketing_Model_Object_Filter_Order_Table
    extends Mzax_Emarketing_Model_Object_Filter_Table
{
    /**
     * @var string
     */
    protected $_table = 'sales/order';

    /**
     * @var string
     */
    protected $_tableAlias = 'sales_order';

    /**
     * @var string
     */
    protected $_tableIdFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_requireBinding = 'order_id';

    /**
     * @var string
     */
    protected $_formText = 'Order `%s` %s %s.';

    /**
     * @var string
     */
    protected $_boolFormText = 'Order %s `%s`.';

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Order";
    }

    /**
     * @return void
     */
    protected function _registerColumns()
    {
        $column = $this->addColumn('created_at', 'Order Date', 'date');
        $column->allowFuture = false;

        /** @var Mage_Sales_Model_Order_Config $orderConfig */
        $orderConfig = Mage::getSingleton('sales/order_config');

        $this->addColumn('store_id', 'Store', 'multiselect', 'adminhtml/system_config_source_store');
        $this->addColumn('base_grand_total', 'Grand Total', 'currency');
        $this->addColumn('base_subtotal', 'Subtotal', 'currency');
        $this->addColumn('base_shipping_amount', 'Shipping Amount', 'currency');
        $this->addColumn('base_tax_amount', 'Tax Total', 'currency');
        $this->addColumn('base_discount_amount', 'Discount Amount', 'currency');
        $this->addColumn('base_total_refunded', 'Total Refunded', 'currency');
        $this->addColumn('weight', 'Total Weight', 'numeric');
        $this->addColumn('is_virtual', 'Virtual', 'boolean');
        $this->addColumn('customer_is_guest', 'Guest Checkout', 'boolean');
        $this->addColumn('status', 'Status', 'multiselect', $orderConfig->getStatuses());
        $this->addColumn('customer_email', 'Customer Email', 'string');
        $this->addColumn('customer_firstname', 'Customer Firstname', 'string');
        $this->addColumn('customer_lastname', 'Customer Lastname', 'string');
        $this->addColumn('shipping_description', 'Shipping Description', 'string');
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     *
     * @return void
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);

        // add currency code for price columns
        $collection->addField('currency_code', "`$this->_tableAlias`.`base_currency_code`");
    }
}
