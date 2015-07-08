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
 * Object Model for customer address
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Object_Customer_Address extends Mzax_Emarketing_Model_Object_Abstract
{
    
    
    /**
     * (non-PHPdoc)
     * @see Varien_Object::_construct()
     */
    public function _construct()
    {
        $this->_init('customer/address');
    }
    
    
    
    /**
     * The title of this object
     * 
     * @return string
     */
    public function getName()
    {
        return $this->__('Customer Address');
    }
    
    
    
    /**
     * Retrieve query for customer address
     * 
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('customer_address_id', 'entity_id');
        
        return $query;
    }
    
    
    
    /**
     * Add name, city and postcode to collection as
     * we use them to show in every grid
     * 
     * @see Mzax_Emarketing_Model_Object_Customer_Address::prepareGridColumns()
     */
    public function prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::prepareCollection($collection);
        
        $firstname = $collection->getQuery()->joinAttribute('{id}', 'customer_address/firstname');
        $lastname  = $collection->getQuery()->joinAttribute('{id}', 'customer_address/lastname');
        $postcode  = $collection->getQuery()->joinAttribute('{id}', 'customer_address/city');
        $city      = $collection->getQuery()->joinAttribute('{id}', 'customer_address/postcode');
        
        $adapter = $this->getResourceHelper()->getAdapter();
        
        $nameExpr[] = "LTRIM(RTRIM({$firstname}))";
        $nameExpr[] = "LTRIM(RTRIM({$lastname}))";
        $nameExpr = $adapter->getConcatSql($nameExpr, ' ');
        
        $collection->addField('name', $nameExpr);
        $collection->addField('city', $city);
        $collection->addField('postcode', $postcode);
    }
    
    
    
    /**
     * Add name, city and postcode to grid
     * 
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid 
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        $grid->addColumn('name', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Name'),
            'index'     => 'name'
        ));
        $grid->addColumn('city', array(
            'header'    => Mage::helper('mzax_emarketing')->__('City'),
            'index'     => 'city',
        ));
        $grid->addColumn('postcode', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Postcode'),
            'index'     => 'postcode',
        ));
    }
    
    
    
}
