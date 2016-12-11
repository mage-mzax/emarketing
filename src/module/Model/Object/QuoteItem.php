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
 * Class Mzax_Emarketing_Model_Object_QuoteItem
 */
class Mzax_Emarketing_Model_Object_QuoteItem extends Mzax_Emarketing_Model_Object_Abstract
{
    /**
     * Model Constructor.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sales/quote_item');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->__('Quote Item');
    }

    /**
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('quote_id', 'quote_id');
        $query->addBinding('product_id', 'product_id');
        $query->addBinding('quote_item_id', 'item_id');

        return $query;
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     *
     * @return void
     */
    public function prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::prepareCollection($collection);

        $collection->addField('quote_item_id');
        $collection->addField('product_type');
        $collection->addField('product_id');
        $collection->addField('sku');
        $collection->addField('name');
        $collection->addField('qty');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        /** @var Mage_Catalog_Model_Product_Type $productType */
        $productType = Mage::getSingleton('catalog/product_type');

        $productTypes = [];
        foreach ($productType->getOptions() as $option) {
            $productTypes[$option['value']] = $option['label'];
        }

        $grid->addColumn('product_type', array(
            'header'    => $this->__('Product Type'),
            'index'     => 'product_type',
            'type'      => 'options',
            'options'   => $productTypes
        ));

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
