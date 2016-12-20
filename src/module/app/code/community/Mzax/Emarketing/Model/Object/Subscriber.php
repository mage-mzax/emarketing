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
 * Class Mzax_Emarketing_Model_Object_Subscriber
 */
class Mzax_Emarketing_Model_Object_Subscriber extends Mzax_Emarketing_Model_Object_Abstract
{
    /**
     * Model Constructor.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('newsletter/subscriber');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->__('Subscriber');
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function getAdminUrl($id)
    {
        // @todo maybe try setting filter
        return $this->getUrl('adminhtml/newsletter_subscriber/index');
    }

    /**
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('subscriber_id', 'subscriber_id');
        $query->addBinding('customer_id', 'customer_id');
        $query->addBinding('email', 'subscriber_email');
        $query->addBinding('subscriber_status', 'subscriber_status');
        $query->addBinding('subscriber_store', 'store_id');

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
     *
     * @return void
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
        $collection->addField('newsletter_status', 'subscriber_status');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
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

        $grid->addColumn('newsletter_status', array(
            'header'    => $this->__('Newsletter'),
            'width'     => '80px',
            'index'     => 'newsletter_status',
            'align'     => 'center',
            'type'      => 'options',
            'options'   => array_map('ucwords', array(
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED    => $this->__('subscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED  => $this->__('unsubscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE    => $this->__('not activated'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED   => $this->__('unconfirmed')
            ))
        ));
    }

    /**
     * @param Mzax_Emarketing_Model_Medium_Email_Snippets $snippets
     *
     * @return void
     */
    public function prepareSnippets(Mzax_Emarketing_Model_Medium_Email_Snippets $snippets)
    {
        $snippets->addVar('customer.firstname', $this->__("Customers Firstname"));
        $snippets->addVar('customer.lastname', $this->__("Customers Lastname"));
        $snippets->addVar('subscriber.status', $this->__("Subscriber Status"));
    }
}
