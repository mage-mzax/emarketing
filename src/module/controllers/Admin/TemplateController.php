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

class Mzax_Emarketing_Admin_TemplateController extends Mage_Adminhtml_Controller_Action
{
	
	
 
    
    public function indexAction()
    {
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Manage Templates'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        
        $this->_addContent(
            $this->getLayout()->createBlock('mzax_emarketing/template_view', 'mzax_emarketing_template_view')
        );
        
        $this->renderLayout();
    }
    
    
    
    
    public function editAction()
    {
        $template = $this->_initTemplate();

        if ($values = $this->_getSession()->getTemplateData(true)) {
            if(isset($values['template'])) {
                $template->addData($values['template']);
            }
        }
        
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Edit Template'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }
    
    
    public function uploadAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }
    
    
    public function uploadPostAction()
    {
        try {
            if(!isset($_FILES['template'])) {
                throw new Mage_Exception($this->__("No template file selected"));
            }
            $file = $_FILES['template'];
            if($file['error']['file'] !== UPLOAD_ERR_OK) {
                throw new Mage_Exception($this->__("Error when uploading template (#%s)", $file['error']['file']));
            }
            
            $template = $this->_initTemplate();
            $template->loadFromFile($file['tmp_name']['file']);
            $template->save();
            
            if(version_compare($template->getVersion(), Mage::helper('mzax_emarketing')->getVersion()) < 0) {
                $this->_getSession()->addWarning($this->__("The template you just uploaded was made with version %s, you only have version %s of Mzax Emarketing. This might cause an issue."));
            }
            $this->_getSession()->addSuccess($this->__("Template successfully uploaded."));
            $this->_redirect('*/*/edit', array('id' => $template->getId()));
            return;
        }
        catch(Mage_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch(Exception $e) {
            if(Mage::getIsDeveloperMode()) {
                throw $e;
            }
            Mage::logException($e);
            $this->_getSession()->addError($this->__("There was an error uploading the template."));
        }
        
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    
    
    
   /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('mzax_emarketing/template_grid')->toHtml());
    }
    
    
    
    
    
    /**
     * Create new template action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    
    
    
    
    
    
    /**
     * Delete template action
     */
    public function deleteAction()
    {
        $template = $this->_initTemplate();
        if ($template->getId()) {
            try {
                $template->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mzax_emarketing')->__('Template was deleted'));
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
            
            $template = $this->_initTemplate('template_id');
            
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
                
                if (isset($data['template'])) {
                    $template->addData($data['template']);
                }
                
                $template->save();
                
                Mage::app()->cleanCache(array(Mzax_Emarketing_Model_Campaign::CACHE_TAG));
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template was successfully saved'));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'    => $template->getId(),
                        '_current'=>true
                    ));
                    return;
                }
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setTemplatenData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id'=>$template->getId())));
                return;
            }
            
        }
       
        $this->getResponse()->setRedirect($this->getUrl('*/*'));
    }
    
    
    
    /**
     * Download template
     * 
     */
    public function downloadAction()
    {
        $template = $this->_initTemplate();
        if ($template->getId()) {
            try {
                $data = $template->export();
                
                $fileName = preg_replace('/[^a-z0-9]+/', '-', strtolower($template->getName()));
                $fileName.= '.mzax.template';
                
                $contentLength = strlen($data);
                
                $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-type', 'text/plain', true)
                    ->setHeader('Content-Length', $contentLength)
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
                    ->setHeader('Last-Modified', date('r'))
                    ->setBody($data);
                
                
                return;
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }
    
    
    
    
    
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);
        
        if ($data = $this->getRequest()->getPost()) {
            $template = $this->_initTemplate('template_id');
            try {
                if (isset($data['template'])) {
                    $template->addData($data['template']);
                }
                $template->parse($template->getBody());
            }
            catch(Mzax_Emarketing_Model_Template_Exception $e) {
                $seesion = $this->_getSession();
                foreach($e->getErrors() as $error) {
                    switch($error->level) {
                        case LIBXML_ERR_WARNING:
                            $seesion->addWarning("Line {$error->line}/{$error->column}: $error->message (#{$error->code})");
                            break;
                        case LIBXML_ERR_ERROR:
                            $seesion->addError("Line {$error->line}/{$error->column}: $error->message (#{$error->code})");
                            break;
                        case LIBXML_ERR_FATAL:
                            $seesion->addError("Line {$error->line}/{$error->column}: $error->message (#{$error->code})");
                            break;
                    }
                }
                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setHtmlTemplateErrors($e->getErrors());
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
            catch(Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
                
                
                
            }
        }

        $this->getResponse()->setBody($response->toJson());
    }
    
    
    
    
    
    /**
     * init campaign
     * 
     * @param string $idFieldName
     * @return Mzax_Emarketing_Model_Template
     */
    protected function _initTemplate($idFieldName = 'id')
    {
        $templateId = (int) $this->getRequest()->getParam($idFieldName);
        $template = Mage::getModel('mzax_emarketing/template');
        if($templateId) {
            $template->load($templateId);
        }
        
        Mage::register('current_template', $template);
        return $template;
    }
    


    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('promo/emarketing/templates');
    }
}