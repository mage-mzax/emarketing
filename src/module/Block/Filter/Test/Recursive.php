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


class Mzax_Emarketing_Block_Filter_Test_Recursive extends Mzax_Emarketing_Block_Filter_Test_Single
{


    /**
     *
     * @var Mzax_Emarketing_Block_Filter_Test_Single
     */
    protected $_filterBlock;



    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mzax/emarketing/filter/test/recursive.phtml');
    }



    /**
     * Retrieve filter
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract
     */
    public function getFilter()
    {
        return Mage::registry('current_filter');
    }





    protected function _beforeToHtml()
    {
        $time = microtime(true);
        $html = $this->_toHtmlRecursive($this->getFilter());
        $time = microtime(true) - $time;

        $this->setTime($time);
        $this->setFilterHtml($html);
    }




    protected function _toHtmlRecursive(Mzax_Emarketing_Model_Object_Filter_Abstract $filter)
    {
        $block = $this->getFilterBlock();
        $block->setFilter($filter);

        $subfilters = '';
        $filters = $filter->getFilters();
        if (count($filters)) {
            foreach ($filters as $f) {
                $subfilters .= $this->_toHtmlRecursive($f);
            }
        }
        $block->setSubfilters($subfilters);

        return $block->toHtml();
    }



    /**
     *
     * @return Mzax_Emarketing_Block_Filter_Test_Single
     */
    public function getFilterBlock()
    {
        $block = $this->getLayout()->createBlock('mzax_emarketing/filter_test_single');
        $block->setTemplate('mzax/emarketing/filter/test/filter.phtml');

        return $block;
    }





}
