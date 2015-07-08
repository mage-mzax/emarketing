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
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Address
    extends Mzax_Emarketing_Model_Object_Filter_Customer_Abstract
{
    const DEFAULT_AGGREGATOR = 'all';
    
    const DEFAULT_EXPECTATION = 'true';
    
    const TYPE_BILLING = 'billing';
    const TYPE_SHIPPING = 'shipping';
    
    

    protected $_allowChildren = true;
    
    

    /**
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::getTitle()
     */
    public function getTitle()
    {
        return "Customer | Default Billing/Shippin Address...";
    }
    
    
    
    
    /**
     *
     * @return Mzax_Emarketing_Model_Object_Customer_Address
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_customer_address');
    }
    
    
    
    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $this->checkIndexes(true);
        
        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator',  self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);
        
        $select = $this->_combineConditions($conditions, $aggregator, $expectation);
        $select->useTemporaryTable($this->getTempTableName());
        
        $addressId = $query->joinAttribute('customer_id', 'customer/default_billing');
        
        $query->joinSelect(array('id' => $addressId), $select, 'filter');
        $query->group();
    }
    
    

    

    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
    }
    
    
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
        
    
    }
    
    
    
    
    
    
    
    
    
    /**
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::prepareForm()
     */
    protected function prepareForm()
    {
        $aggregatorElement  = $this->getSelectElement('aggregator',  self::DEFAULT_AGGREGATOR);
        
        return $this->__('If default billing address matches %s of these conditions:',
            $aggregatorElement->toHtml()
         );
    }
    
    
    
    
    /**
     * The customer etnity int table does not have an index for value
     * which is sort of required by this index
     * 
     */
    public function checkIndexes($create = false)
    {
        $adapter = $this->_getWriteAdapter();
        
        $table = $this->_getAttribute('customer/default_billing')->getBackendTable();
        
        $indexList = $adapter->getIndexList($table);
        
        // check if we already created an index
        if(isset($indexList['MZAX_IDX_VALUE_ID'])) {
            return true;
        }
        
        // check for other indexes that can work
        foreach($indexList as $index) {
            switch(count($index['fields'])) {
                case 1:
                    if($index['fields'][0] === 'value') {
                        return true;
                    }
                    break;
                case 2:
                    if($index['fields'][0] === 'attribute_id' && $index['fields'][1] === 'value') {
                        return true;
                    }
                    break;
            }
        }
        
        
        if($create && $this->canCreateIndex()) {
            try {
                $adapter->addIndex($table, 'MZAX_IDX_VALUE_ID', array('attribute_id', 'value'));
                return true;
            }
            catch(Exception $e) {
                if(Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
                return $this->__('Failed to create an index for the table "%s". Please check logs.', $table);
            }
        }
        else if($this->canCreateIndex()) {
            return true;
        }
        
        return $this->__('It is recommended to set an index on "value" for the table "%s" before using this filter.', $table);
    }
    
    
    

}
