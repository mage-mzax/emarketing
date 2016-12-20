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
 * Class Mzax_Emarketing_Model_Object_Quote
 */
class Mzax_Emarketing_Model_Object_Quote extends Mzax_Emarketing_Model_Object_Abstract
{
    /**
     * Model Constructor.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sales/quote');
    }

    /**
     * Retrieve object name
     *
     * @return string
     */
    public function getName()
    {
        return $this->__('Quote');
    }

    /**
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->where('`main_table`.`is_active` = 1');
        $query->where('`main_table`.`items_count` > 0');
        // @todo Should be medium ?
        $query->where('`main_table`.`customer_email` > ""');
        $query->addBinding('store_id', 'store_id');
        $query->addBinding('quote_id', 'entity_id');
        $query->addBinding('customer_id', 'customer_id');
        $query->addBinding('email', 'customer_email');

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

        $collection->addField('store_id');
        $collection->addField('created_at');
        $collection->addField('updated_at');
        $collection->addField('customer_id');
        $collection->addField('email');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {

        if (!Mage::app()->isSingleStoreMode()) {
            $grid->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> false,
                'display_deleted' => true,
                'width' => '20%',
            ));
        }
        $grid->addColumn('customer_id', array(
            'header'      => $this->__('Customer ID'),
            'id_field'    => 'customer_id',
            'label_field' => 'customer_id',
            'is_system'   => true,
            'width'       => '50px',
            'renderer'    => 'mzax_emarketing/recipients_column_renderer_object',
            'object'      => Mage::getSingleton('mzax_emarketing/object_customer')
        ));

        $grid->addColumn('email', array(
            'header'     => $this->__('Email'),
            'index'      => 'email'
        ));

        $grid->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '120px',
        ));

        $grid->addColumn('updated_at', array(
            'header' => Mage::helper('sales')->__('Last Touch'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '120px',
        ));
    }
}
