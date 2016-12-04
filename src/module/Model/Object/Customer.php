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
 * Class Mzax_Emarketing_Model_Object_Customer
 */
class Mzax_Emarketing_Model_Object_Customer extends Mzax_Emarketing_Model_Object_Abstract
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('customer/customer');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->__('Customer');
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function getAdminUrl($id)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $id));
    }

    /**
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('customer_id', 'entity_id');
        $query->addBinding('email', 'email');

        return $query;
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Object_Collection
     */
    public function getCollection()
    {
        $collection = parent::getCollection();

        $firstname = $collection->getQuery()->joinAttribute('customer_id', 'customer/firstname');
        $lastname  = $collection->getQuery()->joinAttribute('customer_id', 'customer/lastname');

        $adapter = $this->getResourceHelper()->getAdapter();

        $nameExpr[] = "LTRIM(RTRIM($firstname))";
        $nameExpr[] = "LTRIM(RTRIM($lastname))";
        $nameExpr = $adapter->getConcatSql($nameExpr, ' ');

        $collection->addField('name', $nameExpr);
        $collection->addField('email', 'email');

        return $collection;
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     */
    public function prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::prepareCollection($collection);

        $firstname = $collection->getQuery()->joinAttribute('customer_id', 'customer/firstname');
        $lastname  = $collection->getQuery()->joinAttribute('customer_id', 'customer/lastname');

        $adapter = $this->getResourceHelper()->getAdapter();

        $nameExpr[] = "LTRIM(RTRIM($firstname))";
        $nameExpr[] = "LTRIM(RTRIM($lastname))";
        $nameExpr = $adapter->getConcatSql($nameExpr, ' ');

        $collection->addField('name', $nameExpr);
        $collection->addField('email', 'email');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        $grid->addColumn('name', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Name'),
            'index'     => 'name'
        ));

        $grid->addColumn('email', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Email'),
            'index'     => 'email',
        ));
    }

    /**
     * @param Mzax_Emarketing_Model_Medium_Email_Snippets $snippets
     */
    public function prepareSnippets(Mzax_Emarketing_Model_Medium_Email_Snippets $snippets)
    {
        $snippets->addVar('customer.firstname', $this->__("Customers Firstname"));
        $snippets->addVar('customer.lastname', $this->__("Customers Lastname"));

    }

    /**
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Abstract::prepareRecipient()
     */
    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        parent::prepareRecipient($recipient);

        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $recipient->getObject();
        $recipient->setCustomer($customer);
        $recipient->setEmail($customer->getEmail());
        $recipient->setName($customer->getName());
    }
}
