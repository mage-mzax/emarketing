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
 *
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getName()
 * @method string getIsActive()
 * @method string getIsRemoved()
 * @method string getMediumJson()
 *
 * @method Mzax_Emarketing_Model_Campaign_Variation setCreatedAt()
 * @method Mzax_Emarketing_Model_Campaign_Variation setUpdatedAt()
 * @method Mzax_Emarketing_Model_Campaign_Variation setName()
 * @method Mzax_Emarketing_Model_Campaign_Variation setIsActive()
 * @method Mzax_Emarketing_Model_Campaign_Variation setIsRemoved()
 * @method Mzax_Emarketing_Model_Campaign_Variation setMediumJson()
 *
 *
 *
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Campaign_Variation
    extends Mage_Core_Model_Abstract
    implements Mzax_Emarketing_Model_Campaign_Content
{




    const ORIGNAL = 0;
    const NONE = -1;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_campaign_variation';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'variation';



    /**
     *
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;


    /**
     *
     * @var Varien_Object
     */
    protected $_mediumData;




    protected function _construct()
    {
        $this->_init('mzax_emarketing/campaign_variation');
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
        if ($this->_mediumData) {
            $this->setData('medium_json', $this->_mediumData->toJson());
        }
        if ($this->_campaign) {
            $this->setCampaignId($this->_campaign->getId());
        }

        parent::_beforeSave();
    }




    /**
     * set campaign id
     *
     * @param string|integer $campaignId
     * @return Mzax_Emarketing_Model_Campaign_Variation
     */
    public function setCampaignId($campaignId)
    {
        if ($this->_campaign && $this->_campaign->getId() != $campaignId) {
            $this->_campaign = null;
        }

        $this->setData('campaign_id', $campaignId);
        return $this;
    }




    /**
     * set campaign
     *
     * @param Varien_Object $campaign
     * @return Mzax_Emarketing_Model_Campaign_Variation
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->setCampaignId($campaign->getId());
        $this->_campaign = $campaign;

        return $this;
    }




    /**
     *
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        return $this->_campaign;
    }




    /**
     * Is variation removed
     *
     * For statistical reference, variations should not get deleted
     *
     * @param string $flag
     * @return boolean
     */
    public function isRemoved($flag = null)
    {
        if (is_bool($flag)) {
            $this->setIsRemoved((int) $flag);
        }
        return (bool) $this->getIsRemoved();
    }



    /**
     * Is variation active
     *
     * @param string $flag
     * @return boolean
     */
    public function isActive($flag = null)
    {
        if (is_bool($flag)) {
            $this->setIsActive((int) $flag);
        }
        return (bool) $this->getIsActive();
    }






    /**
     * Retrieve content data
     *
     * @return Varien_Object
     */
    public function getMediumData()
    {
        if (!$this->_mediumData) {
            if ( $json = $this->getMediumJson() ) {
                $json = Zend_Json::decode($json);
                $this->_mediumData = new Varien_Object($json);
            }
            else {
                $this->_mediumData = new Varien_Object;
            }
        }
        return $this->_mediumData;
    }




    public function __clone()
    {
        $this->setDuplicateOf($this->getId());
        $this->setId(null);
        $this->setCreatedAt(null);
        $this->setUpdatedAt(null);
    }


}
