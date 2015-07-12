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

class Mzax_Emarketing_Admin_CampaignController extends Mage_Adminhtml_Controller_Action
{
	
	
    protected $_publicActions = array('filterPreview');
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        
        $this->_addContent(
            $this->getLayout()->createBlock('mzax_emarketing/campaign_view', 'mzax_emarketing')
        );
        
        $this->renderLayout();
    }
    
    
    
    
    



    /**
     * Create new campaign action
     */
    public function newAction()
    {
        $campaign = $this->_initCampaign();
        
        if ($values = $this->_getSession()->getCampaignData(true)) {
            if(isset($values['campaign'])) {
                $campaign->addData($values['campaign']);
            }
            if(isset($values['filters']) && is_array($values['filters'])) {
                $campaign->setFilters($values['filters']);
            }
        }
        
        if($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('campaign');
            if(!empty($data)) {
                $campaign->addData($data);
            }
        }
        
        if($campaign->hasData('medium')) {
            return $this->_forward('edit');
        }
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
        
    }
    
    
    

    public function editAction()
    {
        $campaign = $this->_initCampaign();

        if ($values = $this->_getSession()->getCampaignData(true)) {
            if(isset($values['campaign'])) {
                $campaign->addData($values['campaign']);
            }
            if(isset($values['filters']) && is_array($values['filters'])) {
                $campaign->setFilters($values['filters']);
            }
        }
        else if($campaign->getId() && $this->_getSession()->getData('init_default_filters', true) == $campaign->getId()) {
            $campaign->getRecipientProvider()->setDefaultFilters();
        }

        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    }
    


    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
    
            $campaign = $this->_initCampaign('campaign_id');
            $filter = $this->getRequest()->getPost('filter', array());
    
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
    
                if (isset($data['campaign'])) {
                    if(isset($data['campaign']['medium_data'])) {
                        $mediumData = $data['campaign']['medium_data'];
                        unset($data['campaign']['medium_data']);
                        $campaign->getMediumData()->addData($mediumData);
                    }
                    $campaign->addData($data['campaign']);
                }
    
                if(is_array($filter)) {
                    $provider = $campaign->getRecipientProvider();
                    if($provider) {
                        $provider->getFilter()->loadFlatArray($filter);
                    }
                }
    
    
                $variations = $this->getRequest()->getPost('variation');
                if(is_array($variations)) {
                    foreach($variations as $variationId => $variationData) {
                        $variation = $campaign->getVariation($variationId);
                        if( $variation ) {
                            if(isset($variationData['medium_data'])) {
                                $mediumData = $variationData['medium_data'];
                                unset($variationData['medium_data']);
                                $variation->getMediumData()->addData($mediumData);
                            }
                            $variation->addData($variationData);
                        }
                    }
                }
                
                $initDefaultFilters = !$campaign->getId();
                $campaign->save();
                
                if($initDefaultFilters) {
                    $this->_getSession()->setData('init_default_filters', $campaign->getId());
                }
                
                
                Mage::app()->cleanCache(array($campaign::CACHE_TAG));
                
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Campaign was successfully saved'));
                Mage::dispatchEvent('adminhtml_campaign_save_after', array('campaign' => $campaign));
    
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                            'id'    => $campaign->getId(),
                            '_current'=>true
                    ));
                    return;
                }
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCampaignData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id'=>$campaign->getId())));
                return;
            }
    
        }
         
        $this->getResponse()->setRedirect($this->getUrl('*/*'));
    }
    
    
    

    /**
     * Manually aggregate campaign
     *
     * @return void
     */
    public function aggregateAction()
    {
        $campaign = $this->_initCampaign();
        if($campaign->getId()) {
            try {
                $campaign->aggregate();
                $this->_getSession()->addSuccess($this->__('Campaign aggregated successfully'));
            }
            catch(Exception $e) {
                if(Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }
    
        $this->_redirect('*/*/edit', array('_current' => true));
    }
    
    
    
    
    /**
     * Find new recipients that match the current filer
     * at this very moment in time and queue for the campaign
     * medium.
     * 
     * @return void
     */
    public function queueAction()
    {
        $campaign = $this->_initCampaign();
        if ($campaign->getId()) {
            $count = $campaign->findRecipients(true);
            if($count) {
                $this->_getSession()->addSuccess($this->__("%s recipients were found.", $count));
            }
            else {
                $this->_getSession()->addNotice($this->__("No new recipients found"));
            }
        }
        
        $this->_redirect('*/*/edit', array('_current' => true, 'tab' => 'tasks'));
    }
    
    
    
    
    /**
     * Start campaign
     * 
     * @return void
     */
    public function startAction()
    {
        $campaign = $this->_initCampaign();
        if ($campaign->getId()) {
            $campaign->start()->save();
            $this->_getSession()->addSuccess($this->__("Campaign is now running."));
        }
        
        $this->_redirect('*/*/edit', array('_current' => true));
    }
    
    
    
    /**
     * Stop campaign immediately and remove all recipients
     * Usefull as an emergency stop if something is wrong.
     *
     * @return void
     */
    public function stopAction()
    {
        $campaign = $this->_initCampaign();
        if ($campaign->getId()) {
            $campaign->stop()->save();
            $this->_getSession()->addNotice($this->__("Campaign has been stopped"));
        }
    
        $this->_redirect('*/*/edit', array('_current' => true));
    }
    
    
    
    /**
     * Archive campaign
     * 
     * @return void
     */
    public function archiveAction()
    {
        $campaign = $this->_initCampaign();
        if ($campaign->getId()) {
            if($campaign->isRunning()) {
                $this->_getSession()->addWarning($this->__("You can not archive a running campaign."));
            }
            else {
                $campaign->isArchived(!$campaign->isArchived());
                $campaign->save();
                if($campaign->isArchived()) {
                    $this->_getSession()->addSuccess($this->__("Campaign moved to archive."));
                }
                else {
                    $this->_getSession()->addSuccess($this->__("Campaign sucessfully unarchived."));
                }
            }
        }
        $this->_redirect('*/*/edit', array('_current' => true, 'tab' => 'tasks'));
    }
    
    
    
    
    
    
    /**
     * Send the campaign message to all recipients
     * 
     * @return void
     */
    public function prepareAction()
    {
        $campaign = $this->_initCampaign();
        if ($campaign->getId()) {
            $count = $campaign->sendRecipients(array('timeout' => 60));
            $this->_getSession()->addSuccess($this->__("%s emails have been prepared and moved to outbox.", $count));
        }
    
        $this->_redirect('*/*/edit', array('_current' => true, 'tab' => 'tasks'));
    }
    
    
    
    
    
    
    public function addVariationAction()
    {
        $campaign = $this->_initCampaign();
        
        $variation = $campaign->createVariation();
        $variation->save();
        
        $this->_getSession()->addSuccess("New Variation '{$variation->getName()}' has been created.");
        
        $this->_redirect('*/*/edit', array('variation' => $variation->getId(), '_current' => true));
    }
    
    
    
    
    
    /**
     * Delete campaign content variation
     * 
     * @return void
     */
    public function deleteVariationAction()
    {
        $campaign = $this->_initCampaign();
        
        $variationId = $this->getRequest()->getParam('variation');
        if($variationId == 'all') {
            /* @var $variation Mzax_Emarketing_Model_Campaign_Variation */
            foreach($campaign->getVariations() as $variation) {
                $variation->isRemoved(true);
            }
            $campaign->setDataChanges(true);
            $campaign->save();
        } 
        else if($variationId) {
            $variation = $campaign->getVariation($variationId);
            if($variation) {
                $variation->isRemoved(true);
                $variation->save();
                $this->_getSession()->addSuccess("Variation '{$variation->getName()}' has been removed.");
            }
        }
        $this->_redirect('*/*/edit', array('_current' => true));
        
    }
    
    
    
   /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('mzax_emarketing/campaign_grid')->toHtml());
    }
    
    
    /**
     * Recieve campaign recipients grid
     * 
     * @return Mzax_Emarketing_Block_Campaign_Edit_Tab_Recipients_Grid
     */
    public function getRecipientsGridBlock()
    {
    	return $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_recipients_grid');
    }
    
    
    
    /**
     * 
     */
    public function errorGridAction()
    {
        $this->_renderTab('errors');
    }
    
    
    
    
    public function queryReportAction()
    {
        $this->_initCampaign();
        
        
        $jsonParams = $this->getRequest()->getRawBody();
        $params = Zend_Json::decode($jsonParams);
        
        
        /* @var $query Mzax_Emarketing_Model_Report_Query */
        $query = Mage::getModel('mzax_emarketing/report_query');
        $query->setParams($params);
        
        $table = $query->getDataTable();
        
        /* @var $report Mzax_Emarketing_Block_Campaign_Edit_Tab_Report */
        $report = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_tab_report');
        $report->prepareTable($table);
        
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($table->asJson());
    }
    
    
    
    
    
    public function filterGridAction()
    {
        $filterId = $this->getRequest()->getParam('filter_id');
        
        $campagin = $this->getCurrentCampaign();
        $filter = $campagin->getRecipientProvider()->getFilterById($filterId);
        
        if($filter) {
            
            /* @var $test Mzax_Emarketing_Block_Campaign_Test */
            
            $this->getLayout()->createBlock('mzax_emarketing/campaign_test_emulate')->prepareEmulation($filter);
            $grid = $this->getLayout()->createBlock('mzax_emarketing/campaign_test')->getFilterGrid($filter);
            
            $this->getResponse()->setBody($grid->getHtml());
        }
        else {
            $this->getResponse()->setBody($this->__("Filter not found"));
        }
    }
    
    
    
    public function filterPreviewAction()
    {
        $filterId = $this->getRequest()->getParam('filter_id');
        
        $campagin = $this->getCurrentCampaign();
        $filter = $campagin->getFilterById($filterId);
        
        $this->loadLayout('mzax_popup');
        if($filter) {
            $block = $this->getLayout()->getBlock('filter_test')->setFilter($filter);
            $block->setDefaultLimit(20);
        }
        $this->renderLayout();
    }
    
    
    
    

    /**
     * Test Filter Tab Action
     *
     * @return void
     */
    public function testFiltersAction()
    {
        $camoaign  = $this->getCurrentCampaign();
        
        $this->loadLayout('mzax_popup');
        
        if($this->getRequest()->getParam('isAjax')) {
            $block = $this->getLayout()->getBlock('filter.test');
            $this->getResponse()->setBody($block->toHtml());
        }
        else {
            $this->renderLayout();
        }
        
        
        
        
    }
    
    
    
    /**
     * Recipients Tab Action
     * 
     * @return void
     */
    public function recipientsAction()
    {
        $this->_renderTab();
    }
    
    
    
    /**
     * Bounce Tab Action
     * 
     * @return void
     */
    public function inboxAction()
    {
        $this->_renderTab();
    }
    
    

    /**
     * Report Tab Action
     *
     * @return void
     */
    public function reportAction()
    {
        $this->_renderTab();
    }
    
    
    
    
    /**
     * Renders a given tab
     * 
     * @param string $tab
     */
    protected function _renderTab($tab = null)
    {
        if(!$tab) {
            $tab = $this->getRequest()->getActionName();
        }
        
        $campaign = $this->_initCampaign();
        $block = 'mzax_emarketing/campaign_edit_tab_' . $tab;
        
        if($this->getRequest()->getParam('isAjax')) {
            $block = $this->getLayout()->createBlock($block);
            $this->getResponse()->setBody($block->toHtml());
        }
        else {
            $this->loadLayout(array('mzax_popup', 'mzax_emarketing_campaign_tab'));
            $block = $this->getLayout()->createBlock($block);
            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
        }
    }
    
    
    
    
    
    
    
    
    public function exportRecipientsAction()
    {
        $campaign = $this->_initCampaign();
        
        $grid = $this->getRecipientsGridBlock();
        
        $fileName = 'campaign-data.csv';
        $content = $grid->getCsv();
        $contentLength = strlen($content);
        //$this->_prepareDownloadResponse('campaign-data.csv', $grid->getCsvFile());
        
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'text/csv', true)
            ->setHeader('Content-Length', $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->setHeader('Last-Modified', date('r'))
            ->setBody($grid->getCsv());
            
            
    }
    
    
    
    
    
    
    public function templateHtmlAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        
        $templateId = $this->getRequest()->getParam('template');
        /* @var $template Mzax_Emarketing_Model_Template */
        $template = Mage::getModel('mzax_emarketing/template')->load($templateId);
        
        $response->setHtml($template->getBody());
        
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($response->toJson());
    }
    
    
    
    
    /**
     * Allow quick saving email content
     * 
     * @throws Mage_Exception
     */
    public function quicksaveAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        
        try {
            $campaign = $this->_initCampaign();
            $content = $campaign;
            
            $variationId = $this->getRequest()->getParam('variation');
            if($variationId) {
                $content = $campaign->getVariation($variationId);
            }
            
            if($content && $content->getId()) {
                $data = $this->getRequest()->getPost('data');
                if(!empty($data)) {
                    $content->getMediumData()->addData($data);
                    $content->save();
                }
            }
            else {
                throw new Mage_Exception("Campaign or variation not found");
            }
            
            Mage::app()->cleanCache(array($campaign::CACHE_TAG));
        }
        catch(Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        }
        
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($response->toJson());
    }
    
    
    
    /**
     * Preview Newsletter template
     *
     */
    public function previewAction()
    {
        $campaign = $this->_initCampaign();
        
        $this->loadLayout('mzax_popup');
        $this->_addContent(
            $this->getLayout()->createBlock('mzax_emarketing/campaign_preview')->setCampaign($campaign));
        $this->renderLayout();
    }
    
    
    
    
    public function updateLinksAction()
    {
        $request = $this->getRequest();
        
        if($request->isPost() && $request->getPost('update_links')) {
            $optout = $request->getParam('optout');
            if(is_array($optout)) {
                Mage::getResourceSingleton('mzax_emarketing/link')->updateOptoutFlag($optout);
            }
        }
        
        $this->_redirect('*/*/preview', array('_current' => true));
    }
    
    
    
    
    
    
    
    
    public function sendTestMailAction()
    {
        $campaign = $this->_initCampaign('id');
        
        $recipientId = $this->getRequest()->getParam('recipient');
        
        $recipient = $campaign->createMockRecipient($recipientId);
        $recipient->prepare();
        
        Mage::register('current_recipient', $recipient);
        

        $this->loadLayout('mzax_popup');
        $this->renderLayout();
    }
    
    
    public function sendTestMailPostAction()
    {
        try {
            $campaign = $this->_initCampaign('id');
            
            $objectId = $this->getRequest()->getParam('object_id');
            $recipient = $campaign->createMockRecipient($objectId);
            
            Mage::register('current_recipient', $recipient);
            $recipient->prepare();
            $recipient->setForceAddress($this->getRequest()->getParam('recipient_email'));
            $recipient->setAddress($this->getRequest()->getParam('recipient_email'));
            $recipient->setName($this->getRequest()->getParam('recipient_name'));
            
            if($variationId = (int) $this->getRequest()->getPost('variation')) {
                $recipient->setVariationId($variationId);
            }
            
            $recipient->isPrepared(true);
            $recipient->save();
            
            $campaign->getMedium()->sendRecipient($recipient);
            
            $this->_getSession()->addSuccess($this->__("Test mail send to '%s'", $recipient->getAddress()));
        }
        catch(Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            if(Mage::getIsDeveloperMode()) {
                throw $e;
            }
        }
        $this->_redirect('*/*/sendTestMail', array('_current' => true, 'variation' => $variationId));
    }
    
    
    
    
    
    
    
    public function validateTestMailAction()
    {
        $response = new Varien_Object();
        $response->setError(0);

        $this->getResponse()->setBody($response->toJson());
    }
    
    
    
    
    
    
    
    
    /**
     * Delete campaign action
     */
    public function deleteAction()
    {
        $campaign = $this->_initCampaign();
        if ($campaign->getId()) {
            try {
                $campaign->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mzax_emarketing')->__('Campaign was deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }
    
    
    
    
    
    public function newFilterHtmlAction()
    {
        $campaign = $this->_initCampaign('campaign');
        
        $type = $this->getRequest()->getPost('type');
        
        $filter = $campaign->getRecipientProvider()->createFilterFromTypePath($type);
        $filter->setId($this->getRequest()->getPost('id'));
        
        $this->getResponse()->setBody($filter->asHtml());
    }
    
    
    public function loadTemplateAction()
    {
        $campaign = $this->_initCampaign('campaign_id');
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['campaign']['template_id'])) {
                $templateId = (int) $data['campaign']['template_id'];
                
                $template = Mage::getModel('core/email_template')->load($templateId);
                if($template->getId()) {
                    $data['campaign']['email_subject'] = $template->getTemplateSubject();
                    $data['campaign']['email_text'] = $template->getTemplateText();
                }
                else {
                    $this->_getSession()->addError($this->__('Template not found'));
                }
            }
            Mage::getSingleton('adminhtml/session')->setCampaignData($data);
        } 
        $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id'=>$campaign->getId())));
    }
    
    
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);

        $this->getResponse()->setBody($response->toJson());
    }
    
    
    
    
    
    
    
    /**
     * init campaign
     * 
     * @param string $idFieldName
     * @return Mzax_Emarketing_Model_Campaign
     */
    protected function _initCampaign($idFieldName = 'id')
    {
        $campaign = Mage::registry('current_campaign');
        if(!$campaign) {
            $campaign = Mage::getModel('mzax_emarketing/campaign');
            if($campaignId = (int) $this->getRequest()->getParam($idFieldName)) {
                $campaign->load($campaignId);
            }
            
            Mage::register('current_campaign', $campaign);
        }
        return $campaign;
    }



    

    /**
     * Retrieve current campaign with all post
     * or current session data applied
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCurrentCampaign($idFieldName = 'id')
    {
        $campaign  = $this->_initCampaign($idFieldName);
    
        $cacheKey = 'mzax_campaign_test_' . $campaign->getId();
    
        // if we have post data, apply those changes first
        // and store them to the session
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['campaign']) && isset($data['filter'])) {
                $this->_getSession()->setData($cacheKey, $data);
            }
        }
        $data = $this->_getSession()->getData($cacheKey);
    
        if(is_array($data)) {
            if (isset($data['campaign'])) {
                $campaign->addData($data['campaign']);
            }
    
            if(isset($data['filter'])) {
                $provider = $campaign->getRecipientProvider();
                if( $provider && is_array($data['filter']) ) {
                    $provider->loadFlatArray($data['filter']);
                }
            }
        }
    
        return $campaign;
    }
    
    
}