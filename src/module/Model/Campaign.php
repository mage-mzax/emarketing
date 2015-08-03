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
 * Campaign
 * 
 * @method Mzax_Emarketing_Model_Campaign setCreatedAt(string $value)
 * @method Mzax_Emarketing_Model_Campaign setUpdatedAt(string $value)
 * @method Mzax_Emarketing_Model_Campaign setStartAt(string $value)
 * @method Mzax_Emarketing_Model_Campaign setEndAt(string $value)
 * @method Mzax_Emarketing_Model_Campaign setRunning(string $value)
 * @method Mzax_Emarketing_Model_Campaign setAutologin(string $value)
 * @method Mzax_Emarketing_Model_Campaign setCheckFrequency(string $value)
 * @method Mzax_Emarketing_Model_Campaign setLastCheck(string $value)
 * @method Mzax_Emarketing_Model_Campaign setMinResendInterval(string $value)
 * @method Mzax_Emarketing_Model_Campaign setExpireTime(string $value)
 * @method Mzax_Emarketing_Model_Campaign setAbtestEnable(string $value)
 * @method Mzax_Emarketing_Model_Campaign setAbtestTraffic(string $value)
 * @method Mzax_Emarketing_Model_Campaign setStoreId(string $value)
 * @method Mzax_Emarketing_Model_Campaign setTemplateId(string $value)
 * @method Mzax_Emarketing_Model_Campaign setDefaultTrackerId(string $value)
 * @method Mzax_Emarketing_Model_Campaign setName(string $value)
 * @method Mzax_Emarketing_Model_Campaign setIdentity(string $value)
 * @method Mzax_Emarketing_Model_Campaign setProvider(string $value)
 * @method Mzax_Emarketing_Model_Campaign setFilterData(string $value)
 * @method Mzax_Emarketing_Model_Campaign setMediumJson(string $value)
 * 
 * @method Mzax_Emarketing_Model_Resource_Campaign getResource()
 * 
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getStartAt()
 * @method string getEndAt()
 * @method string getRunning()
 * @method string getAutologin()
 * @method string getCheckFrequency()
 * @method string getLastCheck()
 * @method string getMinResendInterval()
 * @method string getExpireTime()
 * @method string getAbtestEnable()
 * @method string getAbtestTraffic()
 * @method string getStoreId()
 * @method string getTemplateId()
 * @method string getDefaultTrackerId()
 * @method string getName()
 * @method string getIdentity()
 * @method string getProvider()
 * @method string getFilterData()
 * @method string getMediumJson()
 * @method string getSendingStats()
 * @method string getInteractionStats()
 * @method string getConversionStats()
 * @method string getFailStats()
 * @method string getRevenueStats()
 * 
 * 
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Campaign 
    extends Mage_Core_Model_Abstract
    implements Mzax_Emarketing_Model_Campaign_Content
{
    
    

    const DEFAULT_EMAIL_PROVIDER = 'customers';
    
    
    const CACHE_TAG = 'MZAX_EMARKETING_CAMPAIGN';
    
    
    //protected $_cacheTag = self::CACHE_TAG;

    
    /**
     * Recipient provider
     * 
     * @var Mzax_Emarketing_Model_Recipient_Provider_Abstract
     */
    protected $_provider;
    
    
    
    /**
     * Medium provider
     * 
     * @var Mzax_Emarketing_Model_Medium_Abstract
     */
    protected $_medium;
    
    
    /**
     *
     * @var Varien_Object
     */
    protected $_mediumData;
    
    
    /**
     * Available variations
     * 
     * @var Mzax_Emarketing_Model_Resource_Campaign_Variation_Collection
     */
    protected $_variations;
    
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_campaign';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'campaign';
    
    
    
    /**
     *
     * @var Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    protected $_recipients;
    
    
    
    /**
     *
     * @var Mzax_Emarketing_Model_Resource_Conversion_Tracker_Collection
     */
    protected $_trackers;
    

    /**
     *
     * @var Mzax_Emarketing_Model_Conversion_Tracker
     */
    protected $_defaultTracker;
    
    
    
    /**
     * Store url model
     *
     * @var Mage_Core_Model_Url
     */
    protected $_urlModel;
    
    
    
    
    
    protected function _construct()
    {
        $this->_init('mzax_emarketing/campaign');
        $this->setMinResendInterval(0);
        $this->setCheckFrequency(720);
        $this->setExpireTime(720);
        $this->setIdentity('emarketing');
    }
    
    

    
    
    /**
     *
     * @return string
     */
    public function getContentCacheId()
    {
        return $this->_eventObject . '_' . $this->getId();
    }
    
    
    
    
    protected function _beforeSave()
    {
        
        parent::_beforeSave();
        
        if($this->_mediumData) {
            $this->setData('medium_json', $this->_mediumData->toJson());
        }
        
        if($this->_provider) {
            $this->setProvider($this->_provider->getType());
            $this->setFilterData($this->_provider->getFilter()->asJson());
        }
        
        if(!$this->getData('medium')) {
            throw new Exception("Campaign must define a medium");
        }
        if(!$this->getData('provider')) {
            throw new Exception("Campaign must define a recipient provider");
        }
        
    }
    


    protected function _afterSave()
    {
        parent::_afterSave();
    
        // save all variations if loaded
        if( $this->_variations ) {
            $this->_variations->save();
        }
        
        if( $variations = $this->getClonedVariations() ) {
            foreach($variations as $variation) {
                $variation->save();
            }
            $this->setClonedVariations(false);
        }
    }
    

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if($filters = $this->getData('filters')) {
            $this->setFilters($filters);
        }
    }
    
    

    
    
    
    
    /**
     * Retrieve store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->getStoreId());
    }
    
    
    
    /**
     * Retrieve URL model
     * 
     * @return Mage_Core_Model_Url
     */
    public function getUrlModel()
    {
        if(!$this->_urlModel) {
            $this->_urlModel = Mage::getModel('core/url')->setStore($this->getStore());
        }
        return $this->_urlModel;
    }
    
    
    
    
    
    /**
     * Is campaign archived
     *
     * @return boolean
     */ 
    public function isArchived($flag = null)
    {
        if(is_bool($flag)) {
            $this->setData('archived', $flag ? 1 : 0);
        }
        return (boolean) $this->getData('archived');
    }
    
    
    
    
    /**
     * Is campaign currently running
     *
     * @return boolean
     */
    public function isRunning($flag = null)
    {
        if(is_bool($flag)) {
            $this->setData('running', $flag ? 1 : 0);
        }
        return (boolean) $this->getData('running');
    }
    
    
    
    
    public function start()
    {
        $this->isRunning(true);
        return $this;
    }
    
    
    /**
     * Will not just stop but also remove any
     * already queued recipients
     * 
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function stop()
    {
        $this->isRunning(false);
        
        /* @see Mzax_Emarketing_Model_Resource_Recipient */
        Mage::getResourceSingleton('mzax_emarketing/recipient')->removePending($this->getId());
        
        return $this;
    }
    
    
    
    
    /**
     * check if the campaign is valid for sending
     * 
     * @deprecated
     * @return boolean
     */
    public function isValidForSend()
    {
        return true;
    }
    
    
    
    /**
     * Is a plain text email campaign
     * 
     * @deprecated
     * @return boolean
     */
    public function isPlain()
    {
        return false;
    }
    
    
    
    
    /**
     * Retrieve tags
     * 
     * @return array
     */
    public function getTags()
    {
        $data = $this->getData('tags');
        return preg_split('/[\s,]+/', $data, -1, PREG_SPLIT_NO_EMPTY);
    }
    
    
    /**
     * Set tags
     * 
     * @param string|array $value
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function setTags($value)
    {
        if(is_array($value)) {
            $value = array_unique($value);
            $value = implode(',', $value);
        }
        return $this->setData('tags', $value);
    }
    
    
    /**
     * Add tags to campaign
     * 
     * @param string|array $tags
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function addTags($tags)
    {
        if(is_string($tags)) {
            $tags = preg_split('/[\s,]+/', $tags, -1, PREG_SPLIT_NO_EMPTY);
        }
        if(is_array($tags)) {
            $tags = array_merge($this->getTags(), $tags);
            $this->setTags($tags);
        }
        return $this;
    }
    
    
    /**
     * Remove tags from campaign
     *
     * @param string|array $tags
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function removeTags($tags)
    {
        if(is_string($tags)) {
            $tags = preg_split('/[\s,]+/', $tags, -1, PREG_SPLIT_NO_EMPTY);
        }
        if(is_array($tags)) {
            $tags = array_diff($this->getTags(), $tags);
            $this->setTags($tags);
        }
        return $this;
    }
    
    

    //--------------------------------------------------------------------------
    //
    //  Medium
    //
    //--------------------------------------------------------------------------
    
    
    
    
    /**
     * Retrieve medium
     *
     * @return Mzax_Emarketing_Model_Medium_Abstract
     */
    public function getMedium()
    {
        if(!$this->_medium && $this->getData('medium')) {
            $this->_medium = Mage::getSingleton('mzax_emarketing/medium')->factory($this->getData('medium'));
        }
        return $this->_medium;
    }
    
    
    

    /**
     * Retrieve content data
     *
     * @return Varien_Object
     */
    public function getMediumData()
    {
        if(!$this->_mediumData) {
            if($data = $this->getMediumJson()) {
                $data = Zend_Json::decode($data);
                $this->_mediumData = new Varien_Object($data);
            }
            else {
                $this->_mediumData = new Varien_Object;
            }
            $this->setDataChanges(true);
        }
        return $this->_mediumData;
    }
    
    
    
    
    //--------------------------------------------------------------------------
    //
    //  Provider
    //
    //--------------------------------------------------------------------------
    
    
    
    /**
     * Retrieve all available recipient provider options
     * 
     * @return array
     */
    public function getAvailableProviders()
    {
        return self::getProviderFactory()->getAllOptions(false);
    }
    
    

    
    /**
     * Retrieve recipient provider for this campaign
     * 
     * @return Mzax_Emarketing_Model_Recipient_Provider_Abstract
     */
    public function getRecipientProvider()
    {
        if(!$this->_provider && $this->getData('provider')) {
            $type = $this->getData('provider');
            $this->_provider = self::getProviderFactory()
                ->factory($this->getData('provider'))
                    ->setCampaign($this);
        }
        return $this->_provider;
    }
    
    
    

    /**
     * Retrieve provider factory
     *
     * @return Mzax_Emarketing_Model_Recipient_Provider
     */
    public static function getProviderFactory()
    {
        return Mage::getSingleton('mzax_emarketing/recipient_provider');
    }
    
    
    
    
    
    /**
     * Retrieve filter by id
     * 
     * @param string $id e.g. 1-1-2-3-1-2
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract
     */
    public function getFilterById($id)
    {
        return $this->getRecipientProvider()->getFilterById($id);
    }
    
    
    
    
    
    
    

    //--------------------------------------------------------------------------
    //
    //  Email
    //
    //--------------------------------------------------------------------------
    
    
    
    
    /**
     * Retrieve magento email sender
     * 
     * @return array
     */
    public function getSender()
    {
        $sender = $this->getData('sender');
        if(empty($sender)) {
            $sender = $this->getIdentity();
        }

        if (!is_array($sender)) {
            $sender = array(
                'name'  => Mage::getStoreConfig('trans_email/ident_'.$sender.'/name',  $this->getStore()),
                'email' => Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $this->getStore()),
            );
        }
        $this->setSender($sender);
        return $sender;
    }
    
    
    
    
    
    
    /**
     * Create a mock recipient instance
     * 
     * @param string $entityId Target entity id
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function createMockRecipient($objectId = null)
    {
        /* @var $recipient Mzax_Emarketing_Model_Recipient */
        $recipient = Mage::getModel('mzax_emarketing/recipient');
        $recipient->setCampaign($this);
        $recipient->setObjectId($objectId);
        $recipient->isMock(true);
        
        return $recipient;
    }
    
    
    
    
    /**
     * Prepare recipient
     * 
     * before we send out an email and prepare the template
     * we will give the email provider and the filters a chance to prepare the recipient
     * to add any data usefull information
     * 
     * @param Mzax_Emarketing_Model_Recipient $recipient
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $this->getRecipientProvider()->prepareRecipient($recipient);
        $this->getMedium()->prepareRecipient($recipient);
        return $this;
    }
    

    
    
    /**
     * Retrieve campaign content
     * 
     * If ab-testing is enabled we need to retrieve
     * a random variation
     *
     * @return Mzax_Emarketing_Model_Campaign_Content
     */
    public function getContent($variationId = null)
    {
        if($variationId !== null) {
            // if zero, use orginal campaign
            if($variationId == Mzax_Emarketing_Model_Campaign_Variation::ORIGNAL) {
                return $this;
            }
            
            $variation = $this->getVariation($variationId);
            if(!$variation) {
                return $this;
            }
            return $variation;
        }
        
        $this->setVariationId(Mzax_Emarketing_Model_Campaign_Variation::NONE);
        
        // check if ab-testing is enabled
        if(!$this->getAbtestEnable() && $this->getAbtestTraffic() > 0) {
            return $this;
        }
    
        // include include in test?
        if(mt_rand(0,999)/10 >= $this->getAbtestTraffic()) {
            return $this;
        }
    
        $this->setVariationId(Mzax_Emarketing_Model_Campaign_Variation::ORIGNAL);
        $active = array($this);
    
        /* @var $variation Mzax_Emarketing_Model_Campaign_Variation */
        foreach($this->getVariations() as $variation) {
            if($variation->getIsActive()) {
                $active[] = $variation;
            }
        }
    
        // pick randrom content
        return $active[array_rand($active)];
    }
    
    
    
    
    
    /**
     * Retrieve available snippets for the content manager
     * 
     * @return Mzax_Emarketing_Model_Medium_Email_Snippets
     */
    public function getSnippets()
    {
        /* @var $snippets Mzax_Emarketing_Model_Medium_Email_Snippets */
        $snippets = Mage::getModel('mzax_emarketing/medium_email_snippets');
        
        if($this->getRecipientProvider()) {
            $this->getRecipientProvider()->prepareSnippets($snippets);
        }
        if($this->getMedium()) {
            $this->getMedium()->prepareSnippets($snippets);
        }
        $this->getMedium()->prepareSnippets($snippets);
        
        return $snippets;
    }
    
    
    
    
    //--------------------------------------------------------------------------
    //
    //  Variations
    //
    //--------------------------------------------------------------------------
    
    
    
    /**
     * Check if campagin has any variations
     * 
     * @return int
     */
    public function hasVariations()
    {
        return count($this->getVariations());
    }
    
    
    
    
    
    
    /**
     * Retrieve all variations
     * 
     * @return Mzax_Emarketing_Model_Resource_Campaign_Variation_Collection
     */
    public function getVariations()
    {
        if(!$this->_variations) {
            $this->_variations = Mage::getResourceModel('mzax_emarketing/campaign_variation_collection');
            $this->_variations->addCampaignFilter($this);
        }
        return $this->_variations;
    }
    
    
    
    
    
    /**
     * Retrieve variation by id
     * 
     * @param string $id
     * @return Mzax_Emarketing_Model_Campaign_Variation
     */
    public function getVariation($id)
    {
        return $this->getVariations()->getItemById($id);
    }
    
    
    
    
    /**
     * Create new variation
     * 
     * @param bool $save
     * @return Mzax_Emarketing_Model_Campaign_Variation
     */
    public function createVariation()
    {
        $variations = $this->getVariations();
        
        /* @var $variation Mzax_Emarketing_Model_Campaign_Variation */
        $variation = Mage::getModel('mzax_emarketing/campaign_variation');
        $variation->setCampaign($this);
        $variation->setName('Variation ' . (count($variations)+1));
        $variation->setMediumJson($this->getMediumJson());
        
        return $variation;
    }
    
    
    //--------------------------------------------------------------------------
    //
    //  Trackers
    //
    //--------------------------------------------------------------------------

    
    
    
    /**
     * Retrieve all conversion trackers
     * 
     * @return Mzax_Emarketing_Model_Resource_Conversion_Tracker_Collection
     */
    public function getTrackers()
    {
        if(!$this->_trackers) {
            $this->_trackers = Mage::getResourceModel('mzax_emarketing/conversion_tracker_collection');
            $this->_trackers->addCampaignFilter($this);
            $this->_trackers->addActiveFilter();
        }
        return $this->_trackers;
    }
    
    
    /**
     * Retrieve tracker by id
     * 
     * @param string $id
     * @return Mzax_Emarketing_Model_Conversion_Tracker
     */
    public function getTracker($id)
    {
        return $this->getTrackers()->getItemById($id);
    }
    
    

    /**
     * Retrieve default tracker for this campaign
     *
     * @return Mzax_Emarketing_Model_Conversion_Tracker
     */
    public function getDefaultTracker()
    {
        if(!$this->_defaultTracker) {
            $tracker = $this->getTracker($this->getDefaultTrackerId());
            if(!$tracker) {
                foreach($this->getTrackers() as $tracker) {
                    if($tracker->isDefault()) {
                        break;
                    }
                }
            }
            $this->_defaultTracker = $tracker;
        }
        return $this->_defaultTracker;
    }
    


    

    //--------------------------------------------------------------------------
    //
    //  Recipients
    //
    //--------------------------------------------------------------------------
    
    
    /**
     * Check number of recipients
     *
     * @return integer
     */
    public function countRecipients()
    {
        $count = $this->getData('recipients_count');
        if($count === null) {
            $count = $this->getResource()->countRecipients($this);
            $this->setData('recipients_count', $count);
        }
        return $count;
    }
    
    
    
    
    /**
     * Try to bind recipients to goals
     * 
     * The recipient provider has to do that
     * 
     * @param Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder $binder
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function bindRecipients(Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder $binder)
    {
        if($provider = $this->getRecipientProvider()) {
            $provider->bindRecipients($binder);
        }
        return $this;
    }
    
    
    
    
    /**
     * Find and queue new recipients for this campaign that
     * match the current filters
     * 
     * @return integer the number of recipients found
     */
    public function findRecipients($force = false)
    {
        if($force || $this->getRunning()) {
            $this->setCurrentTime(null);
            return $this->getResource()->findRecipients($this);
        }
        return 0;
    }
    
    
    
    
    
    
    
    /**
     * Retrieve all recipients for this campaign
     * 
     * @return Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    public function getRecipients()
    {
        if($this->_recipients) {
            $this->_recipients = Mage::getResourceModel('mzax_emarketing/recipient_collection');
            $this->_recipients->setCampaign($this);
        }
        return $this->_recipients;
    }
    
    
    
    
    
    /**
     * Retrieve all recipients that have not yet been send
     * 
     * @return Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    public function getPendingRecipients()
    {
        /* @var $recipients Mzax_Emarketing_Model_Resource_Recipient_Collection */
        $recipients = Mage::getResourceModel('mzax_emarketing/recipient_collection');
        $recipients->setCampaign($this);
        $recipients->addPrepareFilter(false);
        
        return $recipients;
    }
    
    
    
    
    /**
     * Prepare pending recipients and move them to
     * the email outbox
     * This will render all emails and makes them ready
     * to get send
     * 
     * @see Mzax_Emarketing_Model_Outbox
     * @return integer Number of prepared recipients
     */
    public function sendRecipients($options = array())
    {
        $options = new Varien_Object($options);
        $options->getDataSetDefault('timeout', 300);
        $options->getDataSetDefault('maximum', 500);
        $options->getDataSetDefault('break_on_error', Mage::getIsDeveloperMode());
        
        if($this->getId() && $this->getRecipientProvider() && $this->getMedium()) {
            return $this->getMedium()->sendRecipients($this, $options);
        }
        return 0;
    }
    
    
    
    
    
    /**
     * Retrieve number of recipient errors
     *
     * @return integer
     */
    public function countRecipientErrors()
    {
        $count = $this->getData('recipient_errors_count');
        if($count === null) {
            $count = $this->getResource()->countRecipientErrors($this);
            $this->setData('recipient_errors_count', $count);
        }
        return $count;
    }
    
    
    
    
    //--------------------------------------------------------------------------
    //
    //  Inbox/Outbox
    //
    //--------------------------------------------------------------------------
    
    /**
     * Check number of messages in inbox
     * 
     * @return integer
     */
    public function countInbox()
    {
        $count = $this->getData('inbox_count');
        if($count === null) {
            $count = $this->getResource()->countInbox($this);
            $this->setData('inbox_count', $count);
        }
        return $count;
    }

    
    
    /**
     * Check number of messages in outbox
     *
     * @return integer
     */
    public function countOutbox()
    {
        $count = $this->getData('outbox_count');
        if($count === null) {
            $count = $this->getResource()->countOutbox($this);
            $this->setData('outbox_count', $count);
        }
        return $count;
    }
    
    
    
    
    
    
    
    //--------------------------------------------------------------------------
    //
    //  Report
    //
    //--------------------------------------------------------------------------
    
    

    /**
     * Aggregate data for this campaign
     *
     * @param integer $incremental
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function aggregate($incremental = null)
    {
        $options = new Varien_Object(array(
            'campaign_id'  => $this->getId(),
            'verbose'      => false
        ));
    
        Mage::dispatchEvent($this->_eventPrefix . '_aggregate',
            array('options' => $options, 'campaign' => $this));
    
        if($incremental) {
            $options->setIncremental((int) $incremental);
        }
    
        /* @var $report Mzax_Emarketing_Model_Report */
        $report = Mage::getSingleton('mzax_emarketing/report');
        $report->aggregate($options->toArray());
    
        return $this;
    }
    
    
    
    
    
    
    
    /**
     * Retrieve a new report query instance
     * 
     * @param string $dimension
     * @param array $metrics
     * @param boolean $variation
     * @return Mzax_Emarketing_Model_Report_Query
     */
    public function queryReport($dimension = 'campaign', $metrics = array('sendings', 'views', 'clicks'), $variations = false, $order = null)
    {
        $metrics = (array) $metrics;
        
        // replace default tracker id
        foreach($metrics as $key => $metric) {
            if( strpos($metric,'#?') !== false) {
                if($tracker = $this->getDefaultTracker()) {
                    $metrics[$key] = str_replace('#?', '#' . $tracker->getId(), $metric);
                }
                else {
                    unset($metrics[$key]);
                }
            }
            if($metric instanceof Mzax_Emarketing_Model_Conversion_Tracker) {
                $metrics[$key] = '#' . $metric->getId();
            }
        }
        
        /* @var $query Mzax_Emarketing_Model_Report_Query */
        $query = Mage::getModel('mzax_emarketing/report_query');
        $query->setParam('campaign',   $this->getId());
        $query->setParam('dimension',  $dimension);
        $query->setParam('metrics',    $metrics);
        $query->setParam('variations', $variations);
        $query->setParam('order',      $order);
        
        
        return $query;
    }
    
    


    
    
    
    
    
    //--------------------------------------------------------------------------
    //
    //  Misc
    //
    //--------------------------------------------------------------------------
    
    
    
    /**
     * Convert to campaign to encoded string
     *
     * @return string
     */
    public function export()
    {
        // set current extension version
        $this->setVersion(Mage::helper('mzax_emarketing')->getVersion());
        
        $filterData = $this->getRecipientProvider()->export();
        $this->setFilterExport(Zend_Json::encode($filterData));
        
        $json = $this->toJson(array(
            'version',
            'name',
            'description',
            'check_frequency',
            'min_resend_interval',
            'autologin',
            'expire_time',
            'provider',
            'filter_export',
            'medium',
            'medium_json'));
        
        return base64_encode($json);
    }
    
    
    
    public function __clone()
    {
        $variations = array();
        foreach($this->getVariations() as $variation) {
            $newVariation = clone $variation;
            $newVariation->setCampaign($this);
            $variations[] = $newVariation;
        }
        $this->_variations = null;
        
        $this->setDuplicateOf($this->getId());
        $this->setId(null);
        $this->setCreatedAt(null);
        $this->setUpdatedAt(null);
        $this->setClonedVariations($variations);
        
    }
    
    
}
