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

class Mzax_Emarketing_Emarketing_OutboxController extends Mage_Adminhtml_Controller_Action
{
	
	
    
    public function indexAction()
    {
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Outbox'));
        
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        
        $this->_addContent(
            $this->getLayout()->createBlock('mzax_emarketing/outbox_view', 'mzax_emarketing')
        );
        
        $this->renderLayout();
    }
    
    
    

    public function emailAction()
    {
        $message = $this->_initEmail();
        
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Outbox Email'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }
    
    
    
    /**
     * Init email
     * 
     * @param string $idFieldName
     * @return Mzax_Emarketing_Model_Outbox_Email
     */
    protected function _initEmail($idFieldName = 'id')
    {
        $id = (int) $this->getRequest()->getParam($idFieldName);
        $email = Mage::getModel('mzax_emarketing/outbox_email');
        if ($id) {
            $email->load($id);
        }
        
        Mage::register('current_email', $email);
        return $email;
    }
    
    
    
    
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('mzax_emarketing/outbox_grid')->toHtml());
    }
    
    
    
    public function campaignGridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('mzax_emarketing/campaign_edit_medium_email_tab_outbox')->toHtml());
    }
    
    
    
    public function renderAction()
    {
        $email = $this->_initEmail();
        if ($email->getId()) {
            $email->render()->save();
            $this->_redirect('*/*/email', array('_current' => true));
            $this->_getSession()->addSuccess(
                    $this->__('The email has been re-rendered')
            );
        }
        else {
            $this->_redirect('*/*/index');
        }
    }
    
    
    public function downloadAction()
    {
        $email = $this->_initEmail();
        if ($email->getId()) {
            $source = $email->getSource();
            
            $filename = "{$email->getCampaign()->getName()}-{$email->getTo()}";
            $filename = str_replace('@', 'AT', strtolower($filename));
            $filename = preg_replace('/[^a-z0-9_-]+/i', '_', $filename);
            
            $response = $this->getResponse();
            $response->setHeader('Content-Description', 'File Transfer');
            $response->setHeader('Content-Type', 'application/octet-stream');
            $response->setHeader('Content-disposition', "attachment; filename=\"{$filename}.eml\"");
            $response->setHeader('Content-Transfer-Encoding', 'binary');
            $response->setHeader('Expires', 'Fiansfer');
            $response->setHeader('Cache-Control', 'must-revalidate');
            $response->setHeader('Pragma', 'public');
            $response->setHeader('Content-Length', $source->getSize());
            $response->setBody($source->getRawData());
            
            
        }
        else {
    	    $this->getResponse()->setHttpResponseCode(404);
	        $this->getResponse()->setBody("Page not found");
        }
    }
    
    
    
    
    
    
    public function massDeleteAction()
    {
        $messages = $this->getRequest()->getPost('messages');
        if (!empty($messages)) {
            $rows = $this->getOutbox()
                ->getResource()
                    ->massDelete($messages);
            if ($rows) {
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d emails(s) have been deleted.', $rows)   
                );
            }
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    public function massDiscardAction()
    {
        $messages = $this->getRequest()->getPost('messages');
        if (!empty($messages)) {
            $rows = $this->getOutbox()
                ->getResource()
                    ->massTypeChange($messages, Mzax_Emarketing_Model_Outbox_Email::STATUS_DISCARDED);
            if ($rows) {
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d email(s) in outbox have been updated.', $rows)   
                );
            }
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
    public function massSendAction()
    {
        $messages = $this->getRequest()->getPost('messages');
        if (!empty($messages)) {
            if ($count = $this->getOutbox()->sendEmails(array('ids' => $messages, 'force' => true))) {
                $this->_getSession()->addSuccess(
                    $this->__('%s emails sent.', $count)
                );
            }
            else {
                $this->_getSession()->addError(
                    $this->__('No emails sent.')
                );
            }
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    
    /**
     * Mass re-render
     * 
     * Allow messages in outbox to rerender if possible
     * 
     * @throws Exception
     * @return void
     */
    public function massRenderAction()
    {
        $messages = $this->getRequest()->getPost('messages');
        if (!empty($messages)) {
            $emails = $this->getOutbox()->getEmails($ids);
            $emails->addFieldToFilter('status', Mzax_Emarketing_Model_Outbox_Email::STATUS_NOT_SEND);
            
            /* @var $email Mzax_Emarketing_Model_Outbox_Email */
            foreach ($emails as $email) {
                try {
                    $email->render()->save();
                }
                catch(Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                    if (Mage::getIsDeveloperMode()) {
                        throw $e;
                    }
                }
            }
            
            if ($rows) {
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d email(s) in outbox have been re-rendered.', $emails->count())
                );
            }
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
    public function sendAction()
    {
        $this->getOutbox()->sendEmails();
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    
    /**
     * Retreive outbox
     * 
     * @return Mzax_Emarketing_Model_Outbox
     */
    protected function getOutbox()
    {
        return Mage::getSingleton('mzax_emarketing/outbox');
    }
    
    

    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('promo/emarketing/email');
    }
}
