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


abstract class Mzax_Emarketing_Model_Object_Filter_Main extends Mzax_Emarketing_Model_Object_Filter_Combine
{
    
    
    public function getId()
    {
        return '1';
    }
    

    
    /**
     * A main filter object should always provide
     * its own object
     *
     * @return boolean
     */
    public function hasOwnObject()
    {
        return true;
    }
    
    
    /**
     * Has any filters been added?
     * 
     * @return boolean
     */
    public function hasFilters()
    {
        return !empty($this->_filters);
    }
    
    
    
    
    
    
    
    public function getAvailableFilters()
    {
        return Mzax_Emarketing_Model_Object_Filter_Component::getAvailableFilters();
    }
    
    
    
    
    /**
     * Retrieve filter using type path
     *
     * Filters depend on their parent and ancestor, therefor we need
     * to retrieve a new filter using its full type path.
     *
     * @param array $path
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract
     */
    public function createFilterFromTypePath($path)
    {
        $params = array();
        
        if(is_string($path)) {
            if(strpos($path, '?')) {
                $parts = explode('?', $path);
                $path = array_shift($parts);
                foreach($parts as $part) {
                    $var = explode('=', $part, 2);
                    if(count($var) === 2) {
                        $params[$var[0]] = urldecode($var[1]);
                    }
                }
            }
            $path = explode('-', $path);
        }
        // ignore self
        array_shift($path);
        
        $filter = null;
        $parent = $this;
        while(count($path)) {
            $filterName = array_shift($path);
            $filter = $this->getFilterFactory()->factory($filterName);
            if(!$filter) {
                throw new Exception("No filter found by name: $filterName");
            }
            $filter->setParent($parent);
            $parent = $filter;
        }
        $filter->addData($params);
        return $filter;
    }
    
    
    
    
    /**
     * Retrieve filter by id
     *
     * @param string $id e.g. 1--1--2--3--1--2
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract
     */
    public function getFilterById($id)
    {
        $path = explode('--', $id);
        array_shift($path);
        $filter = $this->getFilter();
    
        /* @var $filter Mzax_Emarketing_Model_Object_Filter_Abstract */
        while($filter && $i = (int) array_shift($path)) {
            $filter = $filter->getFilterByIndex($i-1);
        }
        return $filter;
    }
    
    
    
    
    
    /**
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Component::getQuery()
     */
    public function getQuery()
    {
        $query = $this->getObject()->getQuery();
        return $query;
    }
    
    
    
    
    /**
     * The default filter instance
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Combine
     */
    public function getFilter()
    {
        return $this;
        if(!$this->_filter) {
            $this->_filter = Mage::getModel('mzax_emarketing/object_filter_combine')
                ->load($this->getFilterData())
                ->setId('1')
                ->setParent($this);
        }
        return $this->_filter;
    }
    
    
    
}
