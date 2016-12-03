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
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Object_Collection extends Varien_Data_Collection_Db
{

    /**
     *
     * @var Mzax_Emarketing_Db_Select
     */
    protected $_query;


    /**
     *
     * @var Mzax_Emarketing_Model_Object_Abstract
     */
    protected $_object;



    public function __construct()
    {
        parent::__construct();
        $this->setItemObjectClass('mzax_emarketing/object_collection_item');
    }





    /**
     * Set Object
     *
     * @param Mzax_Emarketing_Model_Object_Abstract $object
     * @return Mzax_Emarketing_Model_Object_Collection
     */
    public function setObject($object)
    {
        $this->_object = $object;
        return $this;
    }



    /**
     * Retrieve Object
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getObject()
    {
        return $this->_object;
    }




    /**
     * Set Query
     *
     * @param Mzax_Emarketing_Db_Select $query
     * @return Mzax_Emarketing_Model_Object_Collection
     */
    public function setQuery(Mzax_Emarketing_Db_Select $query)
    {
        $this->_conn   = $query->getAdapter();
        $this->_select = $query;
        return $this;
    }




    /**
     * Retrieve Query
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        return $this->_select;
    }




    /**
     * (non-PHPdoc)
     * @see Varien_Data_Collection_Db::getIdFieldName()
     * @return string
     */
    public function getIdFieldName()
    {
        return 'id';
    }




    /**
     * Retrieve collection empty item
     *
     * @return Varien_Object
     */
    public function getNewEmptyItem()
    {
        /* @var $item Mzax_Emarketing_Model_Object_Collection_Item */
        $item = parent::getNewEmptyItem();
        $item->setObject($this->getObject());

        return $item;
    }




    public function applyFilter(Mzax_Emarketing_Model_Object_Filter_Abstract $filter)
    {
        if ($this->getObject() !== $filter->getObject()) {
            throw new Exception("You can not apply filters for different objects");
        }
        $this->getQuery()->joinSelect('id', $filter->getSelect(), 'filter');
        return $this;
    }




    public function addField($alias, $expr = null)
    {
        if (!$expr) {
            $expr = $alias;
        }
        if (is_string($expr)) {
            if (!strpos($expr, '.')) {
                $query = $this->getQuery();

                // assume field in main table if binding does not exist
                if (!$query->hasBinding($expr)) {
                    $query->addBinding($expr, $expr);
                }
                $expr = $query->getBinding($expr);
            }
            else {
                $expr = new Zend_Db_Expr($expr);
            }
        }

        $this->addFilterToMap($alias, $expr);
        $this->getQuery()->setColumn($alias, $expr);
        return $this;
    }



    public function joinAttribute($alias, $binding, $attribute)
    {
        $expr = $this->getQuery()->joinAttribute($binding, $attribute);
        $this->getQuery()->addBinding($alias, $expr);
        $this->addField($alias, $expr);

        return $expr;
    }



    public function hasBinding($name)
    {
        return $this->getQuery()->hasBinding($name);
    }




    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        if ($countSelect->getPart(Zend_Db_Select::GROUP)) {
            $select = $this->getQuery()->getAdapter()->select();
            $select->from(array('results' => $countSelect), null);
            $select->columns('COUNT(*)');

            return $select;
        }
        else {
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->columns('COUNT(*)');
        }
        return $countSelect;


    }

}
