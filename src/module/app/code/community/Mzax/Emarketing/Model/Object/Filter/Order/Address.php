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
 * Class Mzax_Emarketing_Model_Object_Filter_Order_Address
 */
class Mzax_Emarketing_Model_Object_Filter_Order_Address
    extends Mzax_Emarketing_Model_Object_Filter_Order_Abstract
{
    const DEFAULT_AGGREGATOR = 'all';
    const DEFAULT_EXPECTATION = 'true';
    const TYPE_BILLING = 'billing';
    const TYPE_SHIPPING = 'shipping';

    /**
     * @var bool
     */
    protected $_allowChildren = true;

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Order | Billing/Shipping Address matches...";
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Object_OrderItem
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_orderAddress');
    }

    /**
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = $this->getObject()->getQuery();
        $query->setColumn('order_id');

        $query->where('address_type = ?', $this->getDataSetDefault('address_type', self::TYPE_BILLING));

        return $query;
    }

    /**
     *
     * @param Mzax_Emarketing_Db_Select $query
     *
     * @return void
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator', self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);

        $select = $this->_combineConditions($conditions, $aggregator, $expectation);
        $select->useTemporaryTable($this->getTempTableName());

        $query->joinSelect('order_id', $select, 'filter');
        $query->group();
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
    }

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return $this->__(
            'If %s address matches %s of these conditions:',
            $this->getSelectElement('address_type', self::TYPE_BILLING)->toHtml(),
            $this->getSelectElement('aggregator', 'all')->toHtml()
        );
    }

    /**
     * @return string[]
     */
    protected function getAddressTypeOptions()
    {
        return array(
            self::TYPE_BILLING  => $this->__('billing'),
            self::TYPE_SHIPPING => $this->__('shipping'),
        );
    }
}
