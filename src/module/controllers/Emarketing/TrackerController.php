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
 * Admin Tracker Contorller
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Emarketing_TrackerController extends Mage_Adminhtml_Controller_Action
{
	
	

    public function indexAction()
    {
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Manage Trackers'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        
        $this->_addContent(
            $this->getLayout()->createBlock('mzax_emarketing/tracker_view', 'mzax_emarketing_tracker_view')
        );
        
        $this->renderLayout();
    }
    
    
    
    
    public function editAction()
    {
        $tracker = $this->_initTracker();

        if ($values = $this->_getSession()->getTrackerData(true)) {
            if(isset($values['tracker'])) {
                $tracker->addData($values['tracker']);
            }
        }
        
        if($tracker->getId() && $tracker->isActive() && !$tracker->isAggregated()) {
            $this->_getSession()->addWarning($this->__('This tracker has changed and is not yet aggregated. You can do it manually under "Tasks" or wait a while.'));
        }
        
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('Edit Tracker'));

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
        $this->getResponse()->setBody($this->getLayout()->createBlock('mzax_emarketing/tracker_grid')->toHtml());
    }
    
    
    
    


    /**
     * Create new tracker action
     * 
     * @return void
     */
    public function newAction()
    {
        $tracker = $this->_initTracker();
    
        if ($values = $this->_getSession()->getTrackerData(true)) {
            if(isset($values['tracker'])) {
                $tracker->addData($values['tracker']);
            }
        }
        
        if($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('tracker');
            if(!empty($data)) {
                $tracker->addData($data);
            }
        }
    
        if($tracker->getGoalType()) {
            $tracker->getGoal()->setDefaultFilters();
            return $this->_forward('edit');
        }
    
        $this->_title($this->__('eMarketing'))
             ->_title($this->__('New Tracker'));
        
        $this->loadLayout();
        $this->_setActiveMenu('promo/emarketing');
        $this->renderLayout();
    
    }
    
    
    
    
    
    
    
    
    
    /**
     * Delete tracker action
     */
    public function deleteAction()
    {
        $tracker = $this->_initTracker();
        if ($tracker->getId()) {
            try {
                $tracker->delete();
                $this->_getSession()->addSuccess(Mage::helper('mzax_emarketing')->__('Tracker was deleted'));
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
    }
    
    
    

    
    
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            
            $tracker = $this->_initTracker('tracker_id');
            $conditions = $this->getRequest()->getPost('conditions', array());
            try {
                $redirectBack = $this->getRequest()->getParam('back', false);
                $redirectBack = $redirectBack || !$tracker->getId();
                
                if (isset($data['tracker'])) {
                    $tracker->addData($data['tracker']);
                    if(in_array('campaign_ids', (array) $this->getRequest()->getPost('wildcard'))) {
                        $tracker->setCampaignIds('*');
                    }
                }
                                
                if(is_array($conditions)) {
                    $goal = $tracker->getGoal();
                    if($goal) {
                        $goal->getFilter()->loadFlatArray($conditions);
                    }
                }
                
                $tracker->save();
                
                $this->_getSession()->addSuccess($this->__('Tracker was successfully saved'));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id'       => $tracker->getId(),
                        '_current' => true
                    ));
                    return;
                }
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setTrackernData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id'=>$tracker->getId())));
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
    
    



    public function newConditionHtmlAction()
    {
        $tracker = $this->_initTracker('tracker');
    
        $type = $this->getRequest()->getPost('type');
        
        $filter = $tracker->getGoal()->createFilterFromTypePath($type);
        $filter->setId($this->getRequest()->getPost('id'));
    
        $this->getResponse()->setBody($filter->asHtml());
    }
    
    
    
    
    
    
    
    
    
    /**
     * Test Filter Tab Action
     *
     * @return void
     */
    public function testConditionsAction()
    {
        $tracker  = $this->getCurrentTracker();
        
        $this->loadLayout('mzax_popup');
        
        if($this->getRequest()->getParam('isAjax')) {
            $block = $this->getLayout()->getBlock('condition.test');
            $this->getResponse()->setBody($block->toHtml());
        }
        else {
            $this->renderLayout();
        }
    }
    

    
    public function filterGridAction()
    {
        $filterId = $this->getRequest()->getParam('filter_id');
    
        $tracker = $this->getCurrentTracker();
        $filter = $tracker->getGoal()->getFilterById($filterId);
    
        if($filter) {
            /* @var $grid Mzax_Emarketing_Block_Filter_Object_Grid */
            $grid = $this->getLayout()->createBlock('mzax_emarketing/tracker_edit_tab_test')->getFilterGrid($filter);
            $this->getResponse()->setBody($grid->toHtml());
        }
        else {
            $this->getResponse()->setBody($this->__("Condition not found"));
        }
    }
    
    
    
    public function filterPreviewAction()
    {
        $filterId = $this->getRequest()->getParam('filter_id');
    
        $tracker = $this->getCurrentTracker();
        $filter = $tracker->getGoal()->getFilterById($filterId);
        
        
        $this->loadLayout('mzax_popup');
        if($filter) {
            $block = $this->getLayout()->getBlock('filter_test')->setFilter($filter);
            $block->setDefaultLimit(20);
        }
        $this->renderLayout();
    }
    
    
    
    
    /**
     * Manually aggregate tracker
     * 
     * @return void
     */
    public function aggregateAction()
    {
        $tracker = $this->_initTracker();
        if($tracker->getId()) {
            try {
                $tracker->aggregate();
                $tracker->setIsAggregated(true);
                $tracker->save();
                $this->_getSession()->addSuccess($this->__('Tracker aggregated successfully'));
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
     * Duplicate tracker
     *
     * @return void
     */
    public function duplicateAction()
    {
        $tracker = $this->_initTracker();
        if($tracker->getId()) {
            try {
                $trackerClone = clone $tracker;
                $trackerClone->setTitle($trackerClone->getTitle() . $this->__(' (Copy)'));
                $trackerClone->save();
                $this->_getSession()->addSuccess($this->__('Tracker was successfully duplicated'));
                
                return $this->_redirect('*/*/edit', array('id' => $trackerClone->getId(), '_current' => true));
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
     * Make tracker default
     *
     * @return void
     */
    public function makeDefaultAction()
    {
        $tracker = $this->_initTracker();
        if($tracker->getId()) {
            try {
                
                if(!$tracker->isActive()) {
                    $this->_getSession()->addError($this->__('The default tracker must always be active'));
                }
                else if(!$tracker->isTrackingAllCampaigns()) {
                    $this->_getSession()->addError($this->__('The default tracker must always track all campaigns'));
                }
                else {
                    $tracker->setAsDefault();
                    $this->_getSession()->addSuccess($this->__('Tracker "%s" is now the new default conversion tracker.', $tracker->getTitle()));
                }
            }
            catch(Exception $e) {
                if(Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
                $this->_getSession()>addError($e->getMessage());
            }
        }
    
        $this->_redirect('*/*/edit', array('_current' => true));
    }
    
    

    
    /**
     * Download tracker
     *
     * @return void
     */
    public function downloadAction()
    {
        $tracker = $this->_initTracker();
        if ($tracker->getId()) {
            try {
                $data = $tracker->export();
    
                $fileName = preg_replace('/[^a-z0-9]+/', '-', strtolower($tracker->getTitle()));
                $fileName.= '.mzax.tracker';
    
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
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*');
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
            if(!isset($_FILES['tracker'])) {
                throw new Mage_Exception($this->__("No template file selected"));
            }
            $file = $_FILES['tracker'];
            if($file['error']['file'] !== UPLOAD_ERR_OK) {
                throw new Mage_Exception($this->__("Error when uploading template (#%s)", $file['error']['file']));
            }
    
            $tracker = $this->_initTracker();
            $tracker->loadFromFile($file['tmp_name']['file']);
            $tracker->save();
    
            if(version_compare($tracker->getVersion(), Mage::helper('mzax_emarketing')->getVersion()) < 0) {
                $this->_getSession()->addWarning($this->__("The tracker you just uploaded was made with version %s, you only have version %s of Mzax Emarketing. This may cause problems."));
            }
            $this->_getSession()->addSuccess($this->__("Tracker successfully uploaded."));
            $this->_redirect('*/*/edit', array('id' => $tracker->getId()));
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
            $this->_getSession()->addError($this->__("There was an error uploading the tracker."));
        }
    
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    
    public function massAggregateAction()
    {
        $trackerIds = $this->getRequest()->getPost('trackers');
        if(!empty($trackerIds)) {
            
            $options = array(
                'aggregator'  => array('tracker', 'dimension'),
                'tracker_id'  => $trackerIds,
                'verbos'      => false
            );
            
            /* @var $report Mzax_Emarketing_Model_Report */
            $report = Mage::getSingleton('mzax_emarketing/report');
            $report->aggregate($options);
            
            $this->_getResource()->flagAsAggregated($trackerIds);
            
        }
        $this->_redirect('*/*/index');
        
        
    }
    
    
    public function massEnableAction()
    {
        $trackerIds = $this->getRequest()->getPost('trackers');
        $status = (bool) $this->getRequest()->getPost('status');
        if(!empty($trackerIds)) {
            if($status) {
                $rows = $this->_getResource()->enable($trackerIds);
                $this->_getSession()->addSuccess(
                    $this->__('%d tracker(s) have been enabled.', $rows)
                );
                
            }
            else {
                $rows = $this->_getResource()->disable($trackerIds);
                $this->_getSession()->addSuccess(
                        $this->__('%d tracker(s) have been disabled.', $rows)
                );
            }
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    public function massDeleteAction()
    {
        $trackerIds = $this->getRequest()->getPost('trackers');
        if(!empty($trackerIds) ) {
            $rows = $this->_getResource()->massDelete($trackerIds);
            $this->_getSession()->addSuccess(
                $this->__('%d tracker(s) have been deleted.', $rows)
            );
            
        }
        $this->_redirect('*/*/index');
    }
    
    
    
    
    
    
    /**
     * Retrieve tracker resource model
     * 
     * @return Mzax_Emarketing_Model_Resource_Conversion_Tracker
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('mzax_emarketing/conversion_tracker');
    }
    
    
    
    
    /**
     * init tracker
     * 
     * @param string $idFieldName
     * @return Mzax_Emarketing_Model_Conversion_Tracker
     */
    protected function _initTracker($idFieldName = 'id')
    {
        $tracker = Mage::registry('current_tracker');
        if(!$tracker) {
            $trackerId = (int) $this->getRequest()->getParam($idFieldName);
            $tracker = Mage::getModel('mzax_emarketing/conversion_tracker');
            if($trackerId) {
                $tracker->load($trackerId);
            }
            Mage::register('current_tracker', $tracker);
        }
        return $tracker;
    }
    
    
    
    
    
    /**
     * Retrieve current tracker with all post
     * or current session data applied
     *
     * @return Mzax_Emarketing_Model_Conversion_Tracker
     */
    public function getCurrentTracker($idFieldName = 'id')
    {
        $tracker  = $this->_initTracker($idFieldName);
    
        $cacheKey = 'mzax_tracker_test_' . $tracker->getId();
    
        // if we have post data, apply those changes first
        // and store them to the session
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['tracker']) && isset($data['conditions'])) {
                $this->_getSession()->setData($cacheKey, $data);
            }
        }
        $data = $this->_getSession()->getData($cacheKey);
    
        if(is_array($data)) {
            if (isset($data['tracker'])) {
                $tracker->addData($data['tracker']);
            }
    
            if(isset($data['conditions'])) {
                $goal = $tracker->getGoal();
                if($goal && is_array($data['conditions'])) {
                    $goal->getFilter()->loadFlatArray($data['conditions']);
                }
            }
        }
    
        return $tracker;
    }
    
    

    /**
     * ACL check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('promo/emarketing/trackers');
    }
}