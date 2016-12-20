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
 * Class Mzax_Emarketing_Model_Object_Filter_Combine
 */
class Mzax_Emarketing_Model_Object_Filter_Combine
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    const DEFAULT_AGGREGATOR = 'all';
    const DEFAULT_EXPECTATION = 'true';

    /**
     * @var string
     */
    protected $_type = 'combine';

    /**
     * @var bool
     */
    protected $_allowChildren = true;

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Combinations of conditions";
    }

    /**
     * Works with all parents
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $parent
     *
     * @return bool Always true
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return true;
    }

    /**
     * Check if accepted by own parent as well
     *
     * @param Mzax_Emarketing_Model_Object_Filter_Component $child
     *
     * @return bool
     */
    public function acceptChild(Mzax_Emarketing_Model_Object_Filter_Component $child)
    {
        if ($this->_parent) {
            return $this->_parent->acceptChild($child);
        }

        return true;
    }

    /**
     * Retrieve final filter query that unites all id filter queries
     * and retrieve only those id's that exist in all filters
     *
     * @param Mzax_Emarketing_Db_Select $query
     *
     * @return void
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $conditions = $this->_getConditions();
        $aggregator = $this->getDataSetDefault('aggregator', self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);

        $select = $this->_combineConditions($conditions, $aggregator, $expectation);
        $select->useTemporaryTable($this->getTempTableName());

        $query->joinSelect('id', $select, 'filter');
        $query->group();
    }

    /**
     * Prepare recipient collection
     *
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     *
     * @return void
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('matches', 'filter.matches');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
        $grid->addColumn('matches', array(
            'header'    => $this->__('Matches'),
            'width'     => '100px',
            'index'     => 'matches'
        ));
    }

    /**
     * Retrieve available sub filters
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract[]
     */
    public function getAvailableFilters()
    {
        return $this->_parent->getAvailableFilters();
    }

    /**
     * Prepare form HTML
     *
     * @return string
     */
    protected function prepareForm()
    {
        $aggregatorElement  = $this->getSelectElement('aggregator', self::DEFAULT_AGGREGATOR);
        $expectationElement = $this->getSelectElement('expectation', self::DEFAULT_EXPECTATION);

        return $this->__(
            'If %s of these conditions are %s:',
            $aggregatorElement->toHtml(),
            $expectationElement->toHtml()
        );
    }
}
