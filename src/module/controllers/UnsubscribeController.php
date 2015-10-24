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




class Mzax_Emarketing_UnsubscribeController extends Mage_Core_Controller_Front_Action
{
    
    
    /**
     * Unsubscribe action, only works when user came from an email
     * 
     * @return mixed
     */
    public function indexAction()
    {
        $session = $this->getSession();
        
        $email = $session->getLastAddress();
        if(!Zend_Validate::is($email, 'EmailAddress') || $this->getSession()->getIsUnsubscribed()) {
            return $this->_redirectUrl('/');
        }
        
        $this->getSession()->setFormKey(Mage::helper('core')->getRandomString(32));
        
        $this->loadLayout();
        $this->renderLayout();
    }


    /**
     * Update preference for given subscriber
     *
     * @return void
     */
    public function updateAction()
    {
        $session = $this->getSession();
        $request = $this->getRequest();

        if($request->isPost()) {
            do {
                if($request->getPost('form_key') !== $session->getFormKey()) {
                    break;
                }
                $email = $request->getPost('email');
                if($email !== $session->getLastAddress()) {
                    break;
                }

                /* @var $subscriber Mage_Newsletter_Model_Subscriber */
                $subscriber = Mage::getModel('newsletter/subscriber');
                $subscriber->loadByEmail($email);

                if(!$subscriber->getId()) {
                    break;
                }

                $lists = $request->getPost('lists');

                /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
                $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
                $collection->addSubscriberToFilter($subscriber);

                /* @var $list Mzax_Emarketing_Model_Newsletter_List */
                foreach($collection as $list) {
                    if(in_array($list->getId(), $lists)) {
                        $list->addSubscribers($subscriber->getId());
                    }
                    else {
                        $list->removeSubscribers($subscriber->getId());
                    }
                }

                Mage::getSingleton('core/session')->addSuccess($this->__("Your email preference have been updated."));

                $this->_redirect('*/*/index');
                return;

            } while(false);
        }
        $this->_redirectUrl('/');
    }




    /**
     * Unsubscribe user from newsletter
     *
     * @return void
     */
    public function doAction()
    {
        $session = $this->getSession();
        $request = $this->getRequest();
        
        if($request->isPost()) {
            do {
                if($request->getPost('form_key') !== $session->getFormKey()) {
                    break;
                }
                $email = $request->getPost('email');
                if($email !== $session->getLastAddress()) {
                    break;
                }

                $recipient = $session->getLastRecipient();
                if($recipient) {
                    $recipient->setSkipUnsubscribe(false);

                    // allow custom unsubscribe implementation
                    Mage::dispatchEvent('mzax_emarketing_user_unsubscribe', array(
                        'email'     => $email,
                        'recipient' => $recipient
                    ));

                    if(!$recipient->getSkipUnsubscribe()) {
                        /* @see $subscriber Mzax_Emarketing_Helper_Newsletter */
                        Mage::helper('mzax_emarketing/newsletter')->unsubscribe($email, null, true);
                    }

                    $session->setIsUnsubscribed(true);
                    $session->setFormKey(null);

                    return $this->_redirect('*/*/done');
                }

            } while(false);
        }
        $this->_redirectUrl('/');
    }
    
    
    
    public function listAction()
    {
        $hash = $this->getRequest()->getParam('id');
        if(!$hash) {
            die('Invalid');
        }
        
        /* @var $recipient Mzax_Emarketing_Model_Recipient */
        $recipient = Mage::getModel('mzax_emarketing/recipient')->loadByBeacon($hash);
        if(!$recipient->getId()) {
            die('Invalid');
        }
        
        $recipient->prepare();
        $email = $recipient->getAddress();
        if($email) {
            /* @see $subscriber Mzax_Emarketing_Helper_Newsletter */
            Mage::helper('mzax_emarketing/newsletter')->unsubscribe($email, $recipient->getStoreId(), false);
        }
        
        die('OK');
    }
    
    
    
    
    
    
    public function doneAction()
    {
        if(!$this->getSession()->getIsUnsubscribed()) {
            return $this->_redirectUrl('/');
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    



    /**
     * Retrieve session model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mzax_emarketing/session');
    }
    
    
}