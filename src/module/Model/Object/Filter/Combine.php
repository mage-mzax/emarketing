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
 * @version     {{version}}
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mzax_Emarketing_Model_Object_Filter_Combine
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    
    const DEFAULT_AGGREGATOR = 'all';
    
    const DEFAULT_EXPECTATION = 'true';
    
    
    protected $_type = 'combine';
    
    
    protected $_allowChildren = true;
    
    
    
    /**
     * This filter does not provide its own object
     * instead it always uses its parents one
     * 
     * @return boolean
     */
    public function hasOwnObject()
    {
        return false;
    }
    
    
    

    /**
     * Works with all parents
     * 
     * @see Mzax_Emarketing_Model_Object_Filter_Component::acceptParent()
     * @return boolean Always true
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return true;
    }
    
    
    
    
    /**
     * Retrieve final filter query that unites all id filter queries
     * and retrieve only those id's that exist in all filters
     *
     * @return string
     */    
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator',  self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);
        
        $select = $this->_combineConditions($conditions, $aggregator, $expectation);
        $select->useTemporaryTable($this->getTempTableName());
        
        $query->joinSelect('id', $select, 'filter');
        $query->group();
    }
    
    

    
    
    /**
     * Prepare recipient collection
     *
     * @return void
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('matches', 'filter.matches');
    }
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
        $grid->addColumn('matches', array(
            'header'    => $this->__('Matches'),
            'width'     => '100px',
            'index'     => 'matches'
        ));
    }
    
    
    
    public function getAvailableFilters()
    {
        return $this->_parent->getAvailableFilters();
    }
    

    
    
    public function getTitle()
    {
        return "Combinations of conditions";
    }
    

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $aggregatorElment  = $this->getSelectElement('aggregator',  self::DEFAULT_AGGREGATOR);
        $expectationElment = $this->getSelectElement('expectation', self::DEFAULT_EXPECTATION);
        
        return $this->__('If %s of these conditions are %s:',
                $aggregatorElment->toHtml(), $expectationElment->toHtml());
    }
    
    
    
    
    
    
    
    

}
