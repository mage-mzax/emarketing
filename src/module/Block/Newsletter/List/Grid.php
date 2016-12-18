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
 * Class Mzax_Emarketing_Block_Newsletter_List_Grid
 */
class Mzax_Emarketing_Block_Newsletter_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Mzax_Emarketing_Block_Newsletter_List_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('newsletter_list_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('list_id');
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
        $collection->addSubscriberCount();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header'    => $this->__('Created At'),
            'index'     => 'created_at',
            'gmtoffset' => true,
            'type'      =>'datetime',
            'width'     => 200
        ));

        $this->addColumn('updated_at', array(
            'header'    => $this->__('Updated At'),
            'index'     =>'created_at',
            'gmtoffset' => true,
            'type'      =>'datetime',
            'width'     => 200
        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Name'),
            'index'     => 'name',
        ));


        $this->addColumn('is_private', array(
            'header'    => $this->__('Visibility'),
            'index'     => 'is_private',
            'type'      => 'options',
            'options'   => array(
                '0'   => $this->__('Public'),
                '1'   => $this->__('Private')
            ),
            'width'     => 100
        ));

        $this->addColumn('auto_subscribe', array(
            'header'    => $this->__('Auto Subscribe'),
            'index'     => 'auto_subscribe',
            'type'      => 'options',
            'options'   => array(
                '0'   => $this->__('No'),
                '1'   => $this->__('Yes')
            ),
            'width'     => 100
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('allowed_stores', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'allowed_stores',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'=> array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('subscriber_count', array(
            'header'    => $this->__('Subscribers'),
            'index'     => 'subscriber_count',
            'width'     => 80
        ));

        parent::_prepareColumns();

        return $this;
    }

    /**
     * @param Mzax_Emarketing_Model_Resource_Newsletter_List_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return void
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    /**
     * @param $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
