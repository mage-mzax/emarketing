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



/**
 * 
 * 
 * 
 * @method Mzax_Emarketing_Model_Object_Filter_Abstract getFilter()
 * @method boolean getError()
 * @method string getSelect()
 * @method Mzax_Emarketing_Block_Filter_Object_Grid getGrid()
 * @method string getGridHtml()
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Block_Filter_Test_Single extends Mage_Adminhtml_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mzax/emarketing/filter/test/single.phtml');
    }
    
    
    protected function _toHtml()
    {
        $filter = $this->getFilter();
        if(!$filter instanceof Mzax_Emarketing_Model_Object_Filter_Abstract) {
            return 'NO FILTER :(';
        }
        return parent::_toHtml();
    }
    
    
    protected function _beforeToHtml()
    {
        $filter = $this->getFilter();
        if(!$filter instanceof Mzax_Emarketing_Model_Object_Filter_Abstract) {
            return;
        }
        
        $error  = false;
        $select = false;
        $grid   = null;
        
        $time = microtime(true);
        
        try {
            $select   = $filter->getSelect()->assembleAll();
            $grid     = $this->getFilterGrid($filter);
            $total    = $grid->getCollection()->getSize();
            $gridHtml = $grid->getHtml();
            if($this->isDebugMode()) {
                $queryTime = microtime(true);
                $filter->runFilterQuery();
                $this->setQueryTime(microtime(true) - $queryTime);
            }
            $this->setGridSelect($grid->getCollection()->getSelect());
            $this->setTotal($total);
            
            $result = $filter->checkIndexes(true);
            if(is_string($result)) {
                $this->setIndexCheckResult($result);
            }
        }
        catch(Exception $e) {
            $gridHtml = "<p>{$e->getMessage()}</p>";
            $gridHtml.= "<pre>{$e->getTraceAsString()}</pre>";
            $error = true;
        }
        
        $time = microtime(true) - $time;
        
        $this->setError($error);
        $this->setGrid($grid);
        $this->setGridHtml($gridHtml);
        $this->setSelect($select);
        $this->setTime($time);
    }
    
    
    
    
    
    
    /**
     * Is debug mode?
     * 
     * @return boolean
     */
    public function isDebugMode()
    {
        if(Mage::getIsDeveloperMode()) {
            return true;
        }
        return true;
    }
    
    
    public function getObjectName()
    {
        return $this->getFilter()->getParentObject()->getName();
    }
    
    

    public function getStatusMessage()
    {
        $name  = $this->getObjectName();
        $total = $this->getTotal();
        
        if($this->getError()) {
            return $this->__("Error retrieving {$name}s");
        }
        if($total == 0) {
            return $this->__("No {$name}s found");
        }
        
        return $this->__("%s {$name}s found", $total);
    }
    
    
    
    public function getStatusIcon()
    {
        $total  = $this->getTotal();
        
        if($this->getError()) {
            return $this->getSkinUrl('images/error_msg_icon.gif');
        }
        if($total == 0) {
            return $this->getSkinUrl('images/warning_msg_icon.gif');
        }
        
        return $this->getSkinUrl('images/success_msg_icon.gif');
    }
    
    
    
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/filterPreview', array('_current' => true, '_filter' => $this->getFilter()));
    }
    
    
    
    
    /**
     * 
     * 
     * @param Mzax_Emarketing_Model_Object_Filter_Abstract $filter
     * @return Mzax_Emarketing_Block_Filter_Object_Grid
     */
    public function getFilterGrid(Mzax_Emarketing_Model_Object_Filter_Abstract $filter)
    {
        /* @var $grid Mzax_Emarketing_Block_Filter_Object_Grid */
        $grid = $this->getLayout()->createBlock('mzax_emarketing/filter_object_grid', '', array(
            'filter' => $filter,
             'id'    => 'filterGrid_' . str_replace('--', '_', $filter->getId())
        ));
        $grid->setFilter($filter);
        $grid->setDefaultLimit(5);
        $grid->setGridUrl($this->getUrl('*/*/filterGrid', array('_filter' => $filter, '_current' => true)));
        $grid->setUseAjax(true);
        
        return $grid;
    }
    
    
    


    /**
     *
     * @return Mzax_Emarketing_Helper_SqlFormatter
     */
    public function getSqlFormatter()
    {
        return $this->helper('mzax_emarketing/sqlFormatter');
    }
    
    
    
    

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = array())
    {
        if(isset($params['_filter'])) {
            $params['filter_id'] = $params['_filter']->getId();
            unset($params['_filter']);
        }
        return parent::getUrl($route, $params);
    }
    
    
    
    
}
