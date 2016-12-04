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
 * Class Mzax_Emarketing_Model_Resource_Helper
 */
class Mzax_Emarketing_Model_Resource_Helper
{
    /**
     * @var boolean
     */
    protected $_temporaryTablePrivilege;

    /**
     * Loaded Entities
     *
     * @var array
     */
    protected $_entities = array();

    /**
     * Check if db user has privilege to create temporary tables
     *
     * @return boolean
     */
    public function hasTemporaryTablePrivilege()
    {
        if ($this->_temporaryTablePrivilege === null) {
            try {
                $this->getAdapter()->query('CREATE TEMPORARY TABLE `mzax_tmp_test` SELECT 1;');
                $this->_temporaryTablePrivilege = true;
            } catch (Exception $e) {
                $this->_temporaryTablePrivilege = false;
            }
        }
        return $this->_temporaryTablePrivilege;
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     */
    public function getTable($table)
    {
        if ($table instanceof Zend_Db_Select) {
            return $table;
        }
        if ($table instanceof Zend_Db_Expr) {
            return $table;
        }
        if (strpos($table, '/') === false) {
            $table = 'mzax_emarketing/' . $table;
        }
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('mzax_emarketing_read');
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('mzax_emarketing_write');
    }

    /**
     * Retrieve connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->getReadAdapter();
    }

    /**
     * Retrieve new emarketing select object
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function select()
    {
        return new Mzax_Emarketing_Db_Select($this->getAdapter());
    }

    /**
     * Retrieve ID field from entity collection
     *
     * @param Zend_Db_Select $select
     *
     * @return string
     */
    public function getTableAlias(Zend_Db_Select $select)
    {
        foreach ($select->getPart(Zend_Db_Select::FROM) as $alias => $table) {
            if ($table['joinType'] === Zend_Db_Select::FROM) {
                return $alias;
            }
        }

        return null;
    }

    /**
     * Join an attribute value to a collection
     *
     * joinAttribute($select, 'weddingdate', 'customer/weddingdate', 'customer_id')
     *
     * @param Zend_Db_Select $select
     * @param string $attributeCode The name of the attribute
     * @param string $bind The collection column used to bind the attribute
     * @param string $alias Alias of the appended column
     * @param boolean $joinStatic Join attribute even if static
     *
     * @return Zend_Db_Expr
     * @throws Exception
     */
    public function joinAttribute(Zend_Db_Select $select, $attributeCode, $bind, $alias = null, $joinStatic = false)
    {
        $attribute = $this->getAttribute($attributeCode);
        if (!$attribute) {
            throw new Exception("Attribute does not exist '$attributeCode'");
        }

        if (!$alias) {
            $alias = $attribute->getAttributeCode();
        }

        $tableAlias = '_table_'.$alias;

        if (!$attribute->isStatic()) {
            $select->joinLeft(
                array($tableAlias => $attribute->getBackendTable()),
                "(`{$tableAlias}`.`entity_id`={$bind}) AND (`{$tableAlias}`.`attribute_id`={$attribute->getId()})",
                null
            );

            return new Zend_Db_Expr("`{$tableAlias}`.`value`");
        } elseif ($joinStatic) {
            $select->joinLeft(
                array($tableAlias => $attribute->getBackendTable()),
                "(`{$tableAlias}`.`entity_id`={$bind})",
                null
            );

            return new Zend_Db_Expr("`{$tableAlias}`.`{$attribute->getName()}`");
        }

        return new Zend_Db_Expr("`{$attribute->getName()}`");
    }

    /**
     * Retrieve an entity object by name
     *
     * @param string $name
     * @return Mage_Eav_Model_Entity
     */
    public function getEntity($name)
    {
        if (isset($this->_entities[$name])) {
            return $this->_entities[$name];
        }

        /** @var Mage_Eav_Model_Entity $entity */
        $entity = Mage::getModel('eav/entity');
        $entity->setType($name);

        $this->_entities[$name] = $entity;

        return $entity;
    }

    /**
     * Retrieve an attribute by entityname/attributename
     *
     * getAttribute(entity/attribute);
     * getAttribute(customer/signupdate);
     *
     * @param string $attribute
     * @throws Exception
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute($attribute)
    {
        if ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            return $attribute;
        }
        $attrArr = explode('/', $attribute);
        if (count($attrArr) !== 2) {
            throw new Exception("Invalid attribute defined '{$attribute}'");
        }

        $entity = $this->getEntity($attrArr[0]);

        return $entity->getAttribute($attrArr[1]);
    }
}
