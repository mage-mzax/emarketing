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
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Observer_Subscriber
    extends Mzax_Emarketing_Model_Observer_Abstract
{


    /**
     *
     * @event newsletter_subscriber_save_after
     * @param $observer
     *
     */
    public function afterSave(Varien_Event_Observer $observer)
    {
        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();

        if ($subscriber->isObjectNew()) {
            $this->getResouce()->subscribeToAutoLists($subscriber);
        }

    }


    /**
     *
     * @event adminhtml_block_html_before
     * @param Varien_Event_Observer $observer
     */
    public function girdHtmlBefore(Varien_Event_Observer $observer)
    {
        /* @var $grid Mage_Core_Block_Abstract */
        $grid = $observer->getEvent()->getBlock();

        if ($grid instanceof Mage_Adminhtml_Block_Newsletter_Subscriber_Grid)
        {
            /* @see Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
            $options = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection')->toOptionHash();

            if (!empty($options)) {
                $grid->getMassactionBlock()->addItem('list_add', array(
                    'label'        => Mage::helper('mzax_emarketing')->__('Add to list'),
                    'url'          => $grid->getUrl('*/emarketing_newsletter_list/massAdd', array('src' => 'newsletter')),
                    'additional'   => array(
                        'list'     => array(
                            'name'     => 'id',
                            'type'     => 'select',
                            'class'    => 'required-entry',
                            'label'    => Mage::helper('mzax_emarketing')->__('List'),
                            'values'   => $options
                        )
                    )
                ));

                $grid->getMassactionBlock()->addItem('list_remove', array(
                    'label'        => Mage::helper('mzax_emarketing')->__('Remove from list'),
                    'url'          => $grid->getUrl('*/emarketing_newsletter_list/massRemove', array('src' => 'newsletter')),
                    'additional'   => array(
                        'list'     => array(
                            'name'     => 'id',
                            'type'     => 'select',
                            'class'    => 'required-entry',
                            'label'    => Mage::helper('mzax_emarketing')->__('List'),
                            'values'   => $options
                        )
                    )
                ));

            }
        }
    }


    /**
     *
     * @event controller_action_predispatch_newsletter_manage_save
     * @param Varien_Event_Observer $observer
     */
    public function beforeManageSave(Varien_Event_Observer $observer)
    {
        /* @var $controller Mage_Newsletter_ManageController */
        $controller = $observer->getEvent()->getControllerAction();

        $request = $controller->getRequest();
        $lists   = (array) $request->getPost('lists', array());

        if (!$this->_validateFormKey($request)) {
            return;
        }

        try {
            /* @var $subscriber Mage_Newsletter_Model_Subscriber */
            $subscriber = Mage::getModel('newsletter/subscriber');
            $subscriber->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer());

            /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
            $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
            $collection->addSubscriberToFilter($subscriber);


            /* @var $list Mzax_Emarketing_Model_Newsletter_List */
            foreach ($collection as $list) {
                if (in_array($list->getId(), $lists)) {
                    $list->addSubscribers($subscriber->getId());
                } else {
                    $list->removeSubscribers($subscriber->getId());
                }
            }
        }
        catch(Exception $e) {
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
            Mage::logException($e);
        }

    }



    /**
     * Validate Form Key
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    protected function _validateFormKey(Zend_Controller_Request_Http $request)
    {
        if (!($formKey = $request->getParam('form_key', null))
            || $formKey != Mage::getSingleton('core/session')->getFormKey()) {
            return false;
        }
        return true;
    }




    /**
     * Retrieve list resource model
     *
     * @return Mzax_Emarketing_Model_Resource_Newsletter_List
     */
    protected function getResouce()
    {
        return Mage::getResourceSingleton('mzax_emarketing/newsletter_list');
    }

}
