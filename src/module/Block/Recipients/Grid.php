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
 * Class Mzax_Emarketing_Block_Recipients_Grid
 */
class Mzax_Emarketing_Block_Recipients_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Mzax_Emarketing_Model_Object_Filter_Component
     */
    protected $_source;

    /**
     * Mzax_Emarketing_Block_Recipients_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Mzax_Emarketing_Model_Object_Filter_Component
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getObject()
    {
        return $this->getCollection()->getObject();
    }

    /**
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $source
     *
     * @return $this
     */
    public function setSource(Mzax_Emarketing_Model_Object_Filter_Component $source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * @return Mzax_Emarketing_Model_Object_Collection
     */
    public function getCollection()
    {
        return $this->getSource()->getCollection();
    }

    /**
     * Allow provider and filters to alter the grid
     *
     * @return $this
     * @deprecated
     */
    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();
        $this->getSource()->afterGridLoadCollection($this);

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
            'width'     => '50px',
            'renderer'  => 'mzax_emarketing/recipients_column_renderer_object',
            'object'    => $object,
        ));

        $this->setDefaultSort('main_object_id');
        $this->setDefaultDir('DESC');

        $this->getSource()->prepareGridColumns($this);

        parent::_prepareColumns();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url');
    }

    /**
     * @param $row
     *
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }
}
