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
 * Class Mzax_Emarketing_Block_Filter_Object_Grid
 */
class Mzax_Emarketing_Block_Filter_Object_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Mzax_Emarketing_Model_Object_Filter_Component
     */
    protected $_filter;

    /**
     * Mzax_Emarketing_Block_Filter_Object_Grid constructor.
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        if (isset($attributes['filter'])) {
            $this->setFilter($attributes['filter']);
            unset($attributes['filter']);
        }
        parent::__construct($attributes);
    }

    /**
     * Prepare Layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'hide_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'     => Mage::helper('adminhtml')->__('&times;'),
                        'class'   => 'close-grid'
                    )
                )
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html.= $this->getChildHtml('hide_button');

        return $html;
    }

    /**
     *
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Component
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Filter_Component $filter
     *
     * @return $this
     */
    public function setFilter(Mzax_Emarketing_Model_Object_Filter_Component $filter)
    {
        $this->_filter = $filter;

        return $this;
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getObject()
    {
        return $this->getFilter()->getParentObject();
    }

    /**
     * @return Mzax_Emarketing_Model_Object_Collection
     */
    public function getCollection()
    {
        return $this->getFilter()->getCollection();
    }

    /**
     * Allow provider and filters to alter the grid
     *
     * @deprecated
     */
    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();

        $this->getFilter()->afterGridLoadCollection($this);

        return $this;
    }

    /**
     * Prepare grid columns
     *
     * This is done by the email provider. The grid
     * does not know what type of objects it is loading
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $object = $this->getObject();

        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);

        $this->addColumn('main_object_id', array(
            'header'    => $this->__($object->getName() .' ID'),
            'index'     => 'id',
            'is_system' => true,
            'filter'    => false,
            'width'     => '50px',
            'renderer'  => 'mzax_emarketing/recipients_column_renderer_object',
            'object'    => $object,
        ));

        $this->setDefaultSort('main_object_id');
        $this->setDefaultDir('DESC');

        $this->getFilter()->prepareGridColumns($this);
        parent::_prepareColumns();

        return $this;
    }

    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    /**
     * No row url used
     *
     * @param $row
     *
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }
}
