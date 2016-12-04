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

class Mzax_Emarketing_Emarketing_Newsletter_ListController extends Mage_Adminhtml_Controller_Action
{
	
	
 
    
    public function indexAction()
    {
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Manage Newsletter Lists'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        
        $this->_addContent(
            $this->getLayout()->createBlock('mzax_emarketing/newsletter_list_view', 'mzax_emarketing_newsletter_list_view')
        );
        
        $this->renderLayout();
    }
    
    
    
    
    public function editAction()
    {
        $template = $this->_initList();

        if ($values = $this->_getSession()->getNewsletterListData(true)) {
            if (isset($values['list'])) {
                $template->addData($values['list']);
            }
        }
        
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Edit Newsletter List'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }
    

    
    
    
    
    
    
   /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('mzax_emarketing/newsletter_list_grid')->toHtml());
    }
    
    
    
    
    
    /**
     * Create new template action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
    



    public function subscribersAction()
    {
        $this->_initList();

        $this->loadLayout();
        $block = $this->getLayout()->createBlock('mzax_emarketing/newsletter_list_edit_tab_subscribers');
        $this->getResponse()->setBody($block->toHtml());
    }




    public function massAddAction()
    {
        $request = $this->getRequest();
        if ($data = $request->getPost()) {

            $list = $this->_initList();
            $subscribers = $request->getPost('subscriber');

            try {
                $list->addSubscribers($subscribers);
                $this->_getSession()->addSuccess($this->__('Selected subscribers added to list'));
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());

                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
            }

            if ($request->getParam('src') === 'newsletter') {
                return $this->_redirect('*/newsletter_subscriber');
            }
            return $this->_redirect('*/*/edit', array('_current'=>true, 'tab' => 'subscribers'));
        }
        $this->_redirect('*/*');
    }




    public function massRemoveAction()
    {
        $request = $this->getRequest();
        if ($data = $request->getPost()) {

            $list = $this->_initList();
            $subscribers = $request->getPost('subscriber');

            try {
                $list->removeSubscribers($subscribers);
                $this->_getSession()->addSuccess($this->__('Selected subscribers removed from list'));
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
            }
            if ($request->getParam('src') === 'newsletter') {
                return $this->_redirect('*/newsletter_subscriber');
            }
            return $this->_redirect('*/*/edit', array('_current'=>true, 'tab' => 'subscribers'));
        }
        $this->_redirect('*/*');
    }







    
    /**
     * Delete template action
     */
    public function deleteAction()
    {
        $list = $this->_initList();
        if ($list->getId()) {
            try {
                $list->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mzax_emarketing')->__('Newsletter List has been deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }
    
    
    
    
    
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            
            $list = $this->_initList('list_id');
            
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
                if (isset($data['list'])) {
                    $list->addData($data['list']);
                }

                $list->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Newsletter list was successfully saved'));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'       => $list->getId(),
                        '_current' => true
                    ));
                    return;
                }
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setNewsletterListData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id'=>$list->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*'));
    }





    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);

        $this->getResponse()->setBody($response->toJson());
    }
    
    
    
    
    
    /**
     * init newsletter list
     * 
     * @param string $idFieldName
     * @return Mzax_Emarketing_Model_Newsletter_List
     */
    protected function _initList($idFieldName = 'id')
    {
        $listId = (int) $this->getRequest()->getParam($idFieldName);
        $list = Mage::getModel('mzax_emarketing/newsletter_list');
        if ($listId) {
            $list->load($listId);
        }
        
        Mage::register('current_list', $list);
        return $list;
    }
    


    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('promo/emarketing/newsletter_list');
    }
}
