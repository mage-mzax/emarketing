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
 * Class Mzax_Emarketing_Emarketing_Newsletter_ListController
 */
class Mzax_Emarketing_Emarketing_Newsletter_ListController extends Mzax_Emarketing_Controller_Admin_Action
{
    /**
     * Manage newsletter lists
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Manage Newsletter Lists'));

        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');

        /** @var Mzax_Emarketing_Block_Newsletter_List_View $block */
        $block = $this->getLayout()->createBlock(
            'mzax_emarketing/newsletter_list_view',
            'mzax_emarketing_newsletter_list_view'
        );

        $this->_addContent($block);
        $this->renderLayout();
    }

    /**
     * Edit newsletter lists
     *
     * @return void
     */
    public function editAction()
    {
        $template = $this->_initList();

        if ($values = $this->_getSession()->getData('newsletter_list_data', true)) {
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
     *
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout();

        /** @var Mzax_Emarketing_Block_Newsletter_List_Grid $block */
        $block = $this->getLayout()->createBlock('mzax_emarketing/newsletter_list_grid');

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Create new template action
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit subscriber tab
     *
     * @return void
     */
    public function subscribersAction()
    {
        $this->_initList();
        $this->loadLayout();

        /** @var Mzax_Emarketing_Block_Newsletter_List_Edit_Tab_Subscribers $block */
        $block = $this->getLayout()->createBlock('mzax_emarketing/newsletter_list_edit_tab_subscribers');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function massAddAction()
    {
        $session = $this->_getSession();
        $request = $this->getRequest();
        $data = $request->getPost();
        if (!$data) {
            $this->_redirect('*/*');
            return;
        }

        $list = $this->_initList();
        $subscribers = $request->getPost('subscriber');

        try {
            $list->addSubscribers($subscribers);
            $session->addSuccess($this->__('Selected subscribers added to list'));
        } catch (Exception $e) {
            $session->addError($e->getMessage());

            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
        }

        if ($request->getParam('src') === 'newsletter') {
            $this->_redirect('*/newsletter_subscriber');
            return;
        }
        $this->_redirect('*/*/edit', array('_current'=>true, 'tab' => 'subscribers'));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function massRemoveAction()
    {
        $session = $this->_getSession();
        $request = $this->getRequest();
        $data = $request->getPost();

        if (!$data) {
            $this->_redirect('*/*');
        }

        $list = $this->_initList();
        $subscribers = $request->getPost('subscriber');

        try {
            $list->removeSubscribers($subscribers);
            $session->addSuccess($this->__('Selected subscribers removed from list'));
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
        }
        if ($request->getParam('src') === 'newsletter') {
            $this->_redirect('*/newsletter_subscriber');
            return;
        }
        $this->_redirect('*/*/edit', array('_current'=>true, 'tab' => 'subscribers'));
    }

    /**
     * Delete template action
     *
     * @return void
     */
    public function deleteAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();
        $list = $this->_initList();
        if ($list->getId()) {
            try {
                $list->delete();
                $session->addSuccess(Mage::helper('mzax_emarketing')->__('Newsletter List has been deleted'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }

    /**
     * Save newsletter
     *
     * @return void
     */
    public function saveAction()
    {
        $session = $this->_sessionManager->getAdminhtmlSession();

        if ($data = $this->getRequest()->getPost()) {
            $list = $this->_initList('list_id');

            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
                if (isset($data['list'])) {
                    $list->addData($data['list']);
                }

                $list->save();
                $session->addSuccess($this->__('Newsletter list was successfully saved'));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'       => $list->getId(),
                        '_current' => true
                    ));
                    return;
                }
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setData('newsletter_list_data', $data);

                $this->getResponse()->setRedirect(
                    $this->getUrl('*/*/edit', array('id'=>$list->getId()))
                );
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*'));
    }

    /**
     * @return void
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setData('error', 0);

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Init newsletter list
     *
     * @param string $idFieldName
     *
     * @return Mzax_Emarketing_Model_Newsletter_List
     */
    protected function _initList($idFieldName = 'id')
    {
        $listId = (int) $this->getRequest()->getParam($idFieldName);

        $list = $this->_factory->createNewsletterList();
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
        $session = $this->_sessionManager->getAdminSession();

        return $session->isAllowed('promo/emarketing/newsletter_list');
    }
}
