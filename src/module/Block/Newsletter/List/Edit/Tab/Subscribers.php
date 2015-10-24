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


class Mzax_Emarketing_Block_Newsletter_List_Edit_Tab_Subscribers extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('list_subscriber_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('subscriber_id');
        $this->setDefaultDir('desc');
        $this->setDefaultFilter(array('list_status' => Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED));

    }


    
    protected function _prepareCollection()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Subscriber_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_subscriber_collection');
        $collection->setList(Mage::registry('current_list'));
        $collection->addSubscriberTypeField();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }




    protected function _prepareColumns()
    {
        $this->addColumn('subscriber_id', array(
            'header'    => $this->__('ID'),
            'index'     => 'subscriber_id',
            'width'     => 50
        ));

        $this->addColumn('subscriber_email', array(
            'header'    => $this->__('Email'),
            'index'     => 'subscriber_email',
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('newsletter')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                1  => Mage::helper('newsletter')->__('Guest'),
                2  => Mage::helper('newsletter')->__('Customer')
            ),
            'width'     => 90
        ));


        $this->addColumn('subscriber_status', array(
            'header'    => Mage::helper('newsletter')->__('Newsletter Status'),
            'index'     => 'subscriber_status',
            'type'      => 'options',
            'options'   => array(
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => Mage::helper('newsletter')->__('Not Activated'),
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => Mage::helper('newsletter')->__('Subscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('newsletter')->__('Unsubscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED  => Mage::helper('newsletter')->__('Unconfirmed')
            )
        ));

        $this->addColumn('list_status', array(
            'header'    => Mage::helper('newsletter')->__('On List'),
            'index'     => 'list_status',
            'type'      => 'options',
            'options'   => array(
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => $this->__('No'),
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => $this->__('Yes'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => $this->__('Removed')
            ),
            'width'     => 90
        ));

        $this->addColumn('list_changed_at', array(
            'header'    => $this->__('Changed At'),
            'index'     => 'list_changed_at',
            'gmtoffset' => true,
            'type'      =>'datetime'
        ));


        return parent::_prepareColumns();
    }




    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscriber_id');
        $this->getMassactionBlock()->setFormFieldName('subscriber');

        $this->getMassactionBlock()->addItem('subscribe', array(
            'label'      => $this->__('Add to list'),
            'url'        => $this->getUrl('*/*/massAdd', array('_current' => true))
        ));

        $this->getMassactionBlock()->addItem('unsubscribe', array(
            'label'   => $this->__('Remove from list'),
            'url'     => $this->getUrl('*/*/massRemove', array('_current' => true)),
            'confirm' => $this->__('Are you sure you want to unsubscribe all selected subscribers from this list?')
        ));

        return $this;
    }



    public function getAdditionalJavaScript()
    {
        $object = $this->getMassactionBlock()->getJsObjectName();
        return "window.{$object} = {$object};";
    }






    public function getGridUrl()
    {
        return $this->getUrl('*/*/subscribers', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return null;
    }
    
}
