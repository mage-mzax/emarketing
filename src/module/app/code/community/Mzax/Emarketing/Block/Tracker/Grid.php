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
 * Class Mzax_Emarketing_Block_Tracker_Grid
 */
class Mzax_Emarketing_Block_Tracker_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Mzax_Emarketing_Block_Tracker_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('tracker_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('tracker_id');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Conversion_Tracker_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/conversion_tracker_collection');
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('updated_at', array(
            'header'    => $this->__('Last change'),
            'index'     =>'created_at',
            'gmtoffset' => true,
            'type'      =>'datetime'
        ));

        $this->addColumn('title', array(
            'header'    => $this->__('Title'),
            'index'     => 'title',
        ));

        $this->addColumn('description', array(
            'header'    => $this->__('Description'),
            'index'     => 'description',
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('cms')->__('Active'),
            'index'     => 'is_active',
            'filter_index' => 'is_active',
            'type'      => 'options',
            'options'   => array(
                0 => $this->__('Disabled'),
                1 => $this->__('Enabled')
            ),
        ));

        $this->addColumn('is_aggregated', array(
            'header'    => Mage::helper('cms')->__('Is Aggregated'),
            'index'     => 'is_aggregated',
            'filter_index' => 'is_aggregated',
            'type'      => 'options',
            'options'   => array(
                0 => $this->__('No'),
                1 => $this->__('Yes')
            ),
        ));

        parent::_prepareColumns();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('tracker_id');
        $this->getMassactionBlock()->setFormFieldName('trackers');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'   => $this->__('Delete'),
             'url'     => $this->getUrl('*/*/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('is_active', array(
            'label'=> $this->__('Enable/Disable'),
            'url'  => $this->getUrl('*/*/massEnable', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'options'   => array(
                        0 => $this->__('Disable'),
                        1 => $this->__('Enable'),
                    ),
                )
            )
        ));
        $this->getMassactionBlock()->addItem('aggregate', array(
            'label'   => $this->__('Aggregate'),
            'confirm' => $this->__('This may take some time depending on the size of your data, would you like to continue?'),
            'url'     => $this->getUrl('*/*/massAggregate')
        ));

        return $this;
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
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
