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
 * Class Mzax_Emarketing_Emarketing_TemplateController
 */
class Mzax_Emarketing_Emarketing_TemplateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Mzax_Emarketing_Model_SessionManager
     */
    protected $_sessionManager;

    /**
     * Controller Constructor.
     * Load dependencies.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_sessionManager = Mage::getSingleton('mzax_emarketing/sessionManager');
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function editAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();
        $template = $this->_initTemplate();

        if ($values = $session->getData('template_data', true)) {
            if (isset($values['template'])) {
                $template->addData($values['template']);
            }
        }

        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Edit Template'));

        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function uploadAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function uploadPostAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();

        try {
            if (!isset($_FILES['template'])) {
                throw new Mage_Exception($this->__("No template file selected"));
            }
            $file = $_FILES['template'];
            if ($file['error']['file'] !== UPLOAD_ERR_OK) {
                throw new Mage_Exception($this->__("Error when uploading template (#%s)", $file['error']['file']));
            }

            $template = $this->_initTemplate();
            $template->loadFromFile($file['tmp_name']['file']);
            $template->save();

            if (version_compare($template->getVersion(), Mage::helper('mzax_emarketing')->getVersion()) < 0) {
                $session->addWarning($this->__("The template you just uploaded was made with version %s, you only have version %s of Mzax Emarketing. This might cause an issue."));
            }
            $session->addSuccess($this->__("Template successfully uploaded."));
            $this->_redirect('*/*/edit', array('id' => $template->getId()));
            return;
        } catch (Mage_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
            Mage::logException($e);
            $session->addError($this->__("There was an error uploading the template."));
        }

        $this->_redirect('*/*/index');
    }

   /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->loadLayout();
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
        $session = $this->_sessionManager->getAdminhtmlSession();

        $template = $this->_initTemplate();
        if ($template->getId()) {
            try {
                $template->delete();
                $session->addSuccess(Mage::helper('mzax_emarketing')->__('Template was deleted'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();

        if ($data = $this->getRequest()->getPost()) {
            $template = $this->_initTemplate('template_id');

            try {
                $redirectBack = $this->getRequest()->getParam('back', false);

                if (isset($data['template'])) {
                    $template->addData($data['template']);
                }

                $template->save();

                Mage::app()->cleanCache(array(Mzax_Emarketing_Model_Campaign::CACHE_TAG));
                $session->addSuccess($this->__('Template was successfully saved'));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'    => $template->getId(),
                        '_current'=>true
                    ));
                    return;
                }
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setData('template_data', $data);

                $this->getResponse()->setRedirect(
                    $this->getUrl('*/*/edit', array('id' => $template->getId()))
                );
                return;
            }
        }

        $this->getResponse()->setRedirect($this->getUrl('*/*'));
    }

    /**
     * Download template
     *
     * @return void
     */
    public function downloadAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();
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
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }

    /**
     * @return void
     */
    public function validateAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();

        $response = new Varien_Object();
        $response->setError(0);

        if ($data = $this->getRequest()->getPost()) {
            $template = $this->_initTemplate('template_id');
            try {
                if (isset($data['template'])) {
                    $template->addData($data['template']);
                }
                $template->parse($template->getBody());
            } catch (Mzax_Emarketing_Model_Template_Exception $e) {
                foreach ($e->getErrors() as $error) {
                    switch ($error->level) {
                        case LIBXML_ERR_WARNING:
                            $session->addWarning("Line {$error->line}/{$error->column}: $error->message (#{$error->code})");
                            break;
                        case LIBXML_ERR_ERROR:
                            $session->addError("Line {$error->line}/{$error->column}: $error->message (#{$error->code})");
                            break;
                        case LIBXML_ERR_FATAL:
                            $session->addError("Line {$error->line}/{$error->column}: $error->message (#{$error->code})");
                            break;
                    }
                }
                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setHtmlTemplateErrors($e->getErrors());
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            } catch (Exception $e) {
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

        /** @var Mzax_Emarketing_Model_Template $template */
        $template = Mage::getModel('mzax_emarketing/template');
        if ($templateId) {
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
        $session = $this->_sessionManager->getAdminSession();

        return $session->isAllowed('promo/emarketing/templates');
    }
}
