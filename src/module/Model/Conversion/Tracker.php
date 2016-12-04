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



$this->setIsDefault(false);
$this->setIsAggregated(true);
$this->setCreatedAt(null);
$this->setUpdatedAt(null);

/**
 *
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $value)
 *
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $value)
 *
 * @method string getTitle()
 * @method $this setTitle(string $value)
 *
 * @method string getDescription()
 * @method $this setDescription(string $value)
 *
 * @method string getIsActive()
 * @method $this setIsActive(bool $value)
 *
 * @method string getIsDefault()
 * @method $this setIsDefault(bool $value)
 *
 * @method string getIsAggregated()
 * @method $this setIsAggregated(bool $value)
 *
 * @method string getGoalType()
 * @method $this setGoalType(string $value)
 *
 * @method string getFilters()
 * @method $this setFilters(string $value)
 *
 * @method string getFilterData()
 * @method $this setFilterData(string $json)
 *
 * @method Mzax_Emarketing_Model_Resource_Conversion_Tracker getResource()
 */
class Mzax_Emarketing_Model_Conversion_Tracker
    extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_conversion_tracker';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'tracker';

    /**
     *
     * @var Mzax_Emarketing_Model_Conversion_Goal_Abstract
     */
    protected $_goal;

    /**
     * Model Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/conversion_tracker');
    }

    /**
     * Before model save
     *
     * @return void
     */
    protected function _beforeSave()
    {
        if ($this->_goal) {
            $json = $this->_goal->getFilter()->asJson();
            if ($this->getFilterData() !== $json) {
                $this->setFilterData($json);
                $this->setIsAggregated(false);
            }
        }

        $campaigns = $this->getData('campaign_ids');
        if (is_array($campaigns)) {
            $campaigns = array_filter($campaigns, function ($id) {
                return (bool) ($id === '*' || ((int) $id));
            });
            $this->setData('campaign_ids', implode(',', $campaigns));
        }

        parent::_beforeSave();
    }

    /**
     * Retrieve campaign ids
     *
     * @return string[]
     */
    public function getCampaignIds()
    {
        $data = $this->getData('campaign_ids');
        if (!is_array($data)) {
            $data = $data ? explode(',', $data) : array();
            $this->setData('campaign_ids', $data);
        }

        return $data;
    }

    /**
     * Retrieve goal model
     *
     * @param bool $validateFilters
     *
     * @return Mzax_Emarketing_Model_Conversion_Goal_Abstract
     * @throws Mage_Exception
     */
    public function getGoal($validateFilters = false)
    {
        if (!$this->_goal && $this->getGoalType()) {
            /** @var Mzax_Emarketing_Model_Conversion_Goal $factory */
            $factory = Mage::getSingleton('mzax_emarketing/conversion_goal');

            $this->_goal = $factory->factory($this->getGoalType());
            if (!$this->_goal) {
                throw new Mage_Exception(
                    Mage::helper('mzax_emarketing')->__(
                        'Failed to initialise goal type “%s”. This goal type might not be installed on your system.',
                        $this->getGoalType()
                    )
                );
            }
            $this->_goal->setTracker($this, $validateFilters);
        }

        return $this->_goal;
    }

    /**
     * Set this tracker as default tracker
     *
     * @return $this
     */
    public function setAsDefault()
    {
        $this->getResource()->setDefaultTracker($this->getId());

        return $this;
    }

    /**
     * Check if tracker has any filters defined
     *
     * @return boolean
     */
    public function hasFilters()
    {
        return $this->getGoal()->hasFilters();
    }

    /**
     * Is current default tracker
     *
     * @return boolean
     */
    public function isDefault()
    {
        return (bool)$this->getIsDefault();
    }

    /**
     * Is tracker aggregated
     *
     * @return boolean
     */
    public function isAggregated()
    {
        return (bool)$this->getIsAggregated();
    }

    /**
     * Is tracker active
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool)$this->getIsActive();
    }

    /**
     * Is tracking all campaigns
     *
     * @return boolean
     */
    public function isTrackingAllCampaigns()
    {
        return in_array('*', $this->getCampaignIds());
    }

    /**
     * Retrieve campaigns tracked by this tracker
     *
     * @return Mzax_Emarketing_Model_Resource_Campaign_Collection
     */
    public function getCampaigns()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $collection->addArchiveFilter(false);

        if (!$this->isTrackingAllCampaigns()) {
            $collection->addIdFilter($this->getCampaignIds());
        }

        return $collection;
    }

    /**
     * Aggregate data for this tracker
     *
     * @param string $incremental
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return $this
     */
    public function aggregate($incremental = null, Mzax_Emarketing_Model_Campaign $campaign = null)
    {
        $options = new Varien_Object(array(
            'aggregator'  => array('goals', 'tracker', 'dimension'),
            'tracker_id'  => $this->getId(),
            'verbose'     => false
        ));

        Mage::dispatchEvent(
            $this->_eventPrefix . '_aggregate',
            array(
                'options' => $options,
                'campaign' => $campaign,
                'tracker' => $this
            )
        );

        if ($incremental) {
            $options->setData('incremental', (int)$incremental);
        }
        if ($campaign) {
            if ($campaign instanceof Mzax_Emarketing_Model_Campaign) {
                $campaign = $campaign->getId();
            }
            $options->setData('campaign_id', (int)$campaign);
        }

        /* @var $report Mzax_Emarketing_Model_Report */
        $report = Mage::getSingleton('mzax_emarketing/report');
        $report->aggregate($options->toArray());

        return $this;
    }

    /**
     * Load tracker data from file
     *
     * @param string $filename
     *
     * @return $this
     * @throws Mage_Exception
     */
    public function loadFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new Mage_Exception("File not found ($filename)");
        }
        $this->import(file_get_contents($filename));

        return $this;
    }

    /**
     * Load tracker data from encoded string
     *
     * @param string $str
     *
     * @return Mzax_Emarketing_Model_Conversion_Tracker
     * @throws Mage_Exception
     */
    public function import($str)
    {
        try {
            $data = Zend_Json::decode(base64_decode($str));
            $this->addData($data);
            $this->_goal = null;
            // check and make sure goal and filters exist
            $this->getGoal(true);
        } catch (Zend_Json_Exception $e) {
            throw new Mage_Exception("Failed to decode template file");
        }
        return $this;
    }



    /**
     * Convert to tracker to encoded string
     *
     * @return string
     */
    public function export()
    {
        /** @var Mzax_Emarketing_Helper_Data $helper */
        $helper = Mage::helper('mzax_emarketing');
        $this->setData('version', $helper->getVersion());

        $json = $this->toJson(array('version', 'title', 'description', 'goal_type', 'filter_data'));
        return base64_encode($json);
    }

    /**
     * Clone Object
     *
     * @return $this
     */
    public function __clone()
    {
        $this->setId(null);
        $this->setIsDefault(false);
        $this->setIsAggregated(true);
        $this->setCreatedAt(null);
        $this->setUpdatedAt(null);

        return $this;
    }



}
