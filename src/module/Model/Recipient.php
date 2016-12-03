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
 * Recipient
 *
 *
 * @method Mzax_Emarketing_Model_Recipient setCreatedAt(string $value)
 * @method Mzax_Emarketing_Model_Recipient setSentAt(string $value)
 * @method Mzax_Emarketing_Model_Recipient setViewedAt(string $value)
 * @method Mzax_Emarketing_Model_Recipient setObjectId(string $value)
 * @method Mzax_Emarketing_Model_Recipient setCampaignId(string $value)
 * @method Mzax_Emarketing_Model_Recipient setVariationId(string $value)
 * @method Mzax_Emarketing_Model_Recipient setIsMock(string $value)
 *
 * @method Mzax_Emarketing_Model_Recipient setAddress(string $value)
 * @method Mzax_Emarketing_Model_Recipient setName(string $value)
 *
 * @method Mzax_Emarketing_Model_Resource_Recipient getResource()
 *
 * @method string getCreatedAt()
 * @method string getSentAt()
 * @method string getViewedAt()
 * @method string getObjectId()
 * @method string getCampaignId()
 * @method string getVariationId()
 * @method string getIsMock()
 * @method string getName()
 *
 * @method string getForceAddress()
 * @method $this setForceAddress(string $value)
 *
 *
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Recipient extends Mage_Core_Model_Abstract
{

    const EVENT_TYPE_VIEW  = 1;
    const EVENT_TYPE_CLICK = 2;


    const BEACON_SECRET_PATH = 'mzax_emarketing/beacon_secret';



    /**
     * Campaign
     *
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;



    /**
     * Content Provider
     *
     * @var Mzax_Emarketing_Model_Campaign_Content
     */
    protected $_content;




    protected function _construct()
    {
        $this->_init('mzax_emarketing/recipient');
    }



    /**
     * Little helper function to add URLs for the content
     * so they are available via {{var urls.key}}
     *
     * @param string $key
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function addUrl($key, $routePath = null, $routeParams = null)
    {
        $this->getUrls()->setData($key, $this->getUrl($routePath, $routeParams));
        return $this;
    }



    /**
     * Retrieve URL in scope of campaign sender store
     *
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        return $this->getCampaign()->getUrlModel()->getUrl($routePath, $routeParams);
    }




    /**
     * A temporary namespace for urls that can be later used
     * in emails
     *
     *
     * @return Varien_Object
     */
    public function getUrls()
    {
        $urls = $this->getData('url_namespace');
        if (!$urls) {
            $urls = new Varien_Object();
            $this->setData('url_namespace', $urls);
        }
        return $urls;
    }





    /**
     * Return true if recipient is a mock used
     * by the preview
     *
     * @return boolean
     */
    public function isMock($flag = null)
    {
        if (is_bool($flag)) {
            $this->setData('is_mock', $flag ? 1 : 0);
        }
        return (boolean) $this->getData('is_mock');
    }



    /**
     * Retrieve recipient address
     *
     * Depending on the medium the address can
     * be an email, telephone number or real address
     *
     * @return string
     */
    public function getAddress()
    {
        $address = $this->getData('address');
        if ($address) {
            return $address;
        }
        if ($this->getAddressId()) {
            $address = Mage::getResourceSingleton('mzax_emarketing/recipient_address')->getAddress($this->getAddressId());
            $this->setData('address', $address);
        }
        return $address;
    }




    /**
     *
     * @param string $hash
     */
    public function loadByBeacon($hash)
    {
        return $this->load($hash, 'beacon_hash');
    }




    /**
     * Retrieve beacon hash
     *
     * @return string
     */
    public function getBeaconHash()
    {
        $hash = $this->getData('beacon_hash');
        if (!$hash) {
            $hash = md5($this->getObjectId() .
                        $this->getAddress() .
                        $this->getCampaignId() .
                        mt_rand(0, 99999999) .
                        microtime());

            $hash = Mage::helper('mzax_emarketing')->compressHash($hash);
            $this->setBeaconHash($hash);
        }
        return $hash;
    }




    /**
     * Get campaign content
     * This is either a campaign or a variation
     *
     * @return Mzax_Emarketing_Model_Campaign_Content
     */
    public function getContent()
    {
        if (!$this->_content) {
            $this->_content = $this->getCampaign()->getContent($this->getVariationId());

            if ($this->getVariationId() === null) {
                $this->setVariationId((int) $this->_content->getVariationId());
            }
        }
        return $this->_content;
    }




    /**
     * Set content
     *
     * @param Mzax_Emarketing_Model_Campaign_Content $content
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function setContent(Mzax_Emarketing_Model_Campaign_Content $content)
    {
        $this->_content = $content;
        if ($content) {
            $this->setVariationId((int) $content->getVariationId());
        }
        return $this;
    }




    /**
     * Retrieve variation
     *
     * @deprecated
     * @return Mzax_Emarketing_Model_Campaign_Variation
     */
    public function getVariation()
    {
        $id = $this->getVariationId();
        if ($id === null) {
            return null;
        }

        if ($id) {
            $variation = $this->getCampaign()->getVariation($id);
        }
        else if ($id == 0) {
            $variation = $this->getCampaign()->createVaration();
            $variation->setName('Original');
        }
        else {
            $variation = $this->getCampaign()->createVaration();
            $variation->setName('No Testing');
        }

        return $variation;
    }







    protected function _beforeSave()
    {
        // generate hash
        $this->getBeaconHash();

        parent::_beforeSave();
        if ($this->_campaign) {
            $this->setCampaignId($this->_campaign->getId());
        }
    }


    public function save()
    {
        try {
            parent::save();
        }
        catch(Exception $e) {
            $this->unsetData('beacon_hash');
            $this->save();
        }
    }



    /**
     *
     * @deprecated
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function reload()
    {
        $this->load($this->getId());
        return $this;
    }




    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {
            $this->_campaign = Mage::getModel('mzax_emarketing/campaign');
            $this->_campaign->load($this->getCampaignId());
        }
        return $this->_campaign;
    }



    /**
     * Set campaign
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->_campaign = $campaign;
        $this->setCampaignId($campaign->getId());
        return $this;
    }



    /**
     * Retrieve store id from campaign
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getCampaign()->getStoreId();
    }


    /**
     * Retrieve store from campaign
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->getCampaign()->getStore();
    }



    /**
     * Prepare recipient
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function prepare()
    {
        if (!$this->getIsPrepared())
        {
            $this->setIsPrepared(true);
            $this->getCampaign()->prepareRecipient($this);
            Mage::dispatchEvent($this->_eventPrefix.'_prepare', $this->_getEventData());
        }
        return $this;
    }






    /**
     * Is sent out
     *
     * @param boolean $flag
     * @return boolean
     */
    public function isSent($flag = null)
    {
        if (is_bool($flag)) {
            $this->setSentAt($flag ? now() : null);
        }
        return $this->getSentAt() !== null;
    }



    /**
     * Is prepared
     *
     * @param boolean $flag
     * @return boolean
     */
    public function isPrepared($flag = null)
    {
        if (is_bool($flag)) {
            $this->setPreparedAt($flag ? now() : null);
        }
        return $this->getPreparedAt() !== null;
    }



    /**
     * The expire at time stamp for this recipient
     *
     * @return string|NULL
     */
    public function getExpireAt()
    {
        $expireTime = (int) $this->getCampaign()->getExpireTime();
        if ($expireTime > 0) {
            $expireAt = strtotime($this->getCreatedAt()) + ($expireTime*60);
            return date(Varien_Date::DATETIME_PHP_FORMAT, $expireAt);
        }
        return null;
    }




    /**
     * Retrieve the number of seconds since the email
     * was sent
     *
     * @return integer|false
     */
    public function getAge()
    {
        $sentAt = $this->getSentAt();
        if ($sentAt) {
            $sentAt = new Zend_Date($sentAt, Zend_Date::DATETIME);
            return Zend_Date::now()->sub($sentAt)->toValue();
        }
        return false;
    }



    /**
     * Try to loggin in customer by id
     *
     * The method usually gets called by the email provider
     * on link click.
     *
     * @param string $customerId
     * @return boolean
     */
    public function autologin($customerId)
    {
        $enabled = Mage::getStoreConfigFlag('mzax_emarketing/autologin/enable', $this->getStoreId());
        $expire  = (float) Mage::getStoreConfig('mzax_emarketing/autologin/expire', $this->getStoreId());


        if ($enabled && $customerId && $this->getCampaign()->getAutologin()) {
            $age = $this->getAge();

            if ($expire <= 0 || ($age && $age < 60*60 * $expire)) {
                /* @var $session Mage_Customer_Model_Session */
                $session = Mage::getSingleton('customer/session');
                if (!$session->isLoggedIn()) {
                    return $session->loginById($customerId);
                }
            }
        }
        return false;
    }




    /**
     * log exception
     *
     * @param Exception $exception
     * @return Mzax_Emarketing_Model_Recipient_Error
     */
    public function logException(Exception $exception)
    {
        /* @var $error Mzax_Emarketing_Model_Recipient_Error */
        $error = Mage::getModel('mzax_emarketing/recipient_error');
        $error->setRecipient($this)
              ->setException($exception)
              ->save();

        return $error;
    }







    /**
     * Capture view event
     *
     * @param Zend_Controller_Request_Http $request
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function captureView(Zend_Controller_Request_Http $request = null)
    {
        return $this->_capture(self::EVENT_TYPE_VIEW, $request);
    }




    /**
     * Capture click event
     *
     * @param Zend_Controller_Request_Http $request
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function captureClick(Zend_Controller_Request_Http $request = null)
    {
        return $this->_capture(self::EVENT_TYPE_CLICK, $request);
    }



    /**
     * Capture new recipient event and
     * return event id
     *
     * @param int $type
     * @param Zend_Controller_Request_Http $request
     * @return integer EventID
     */
    protected function _capture($type, Zend_Controller_Request_Http $request = null)
    {
        if (!$this->getId()) {
            return false;
        }
        try {
            if (!$request) {
                $request = Mage::app()->getRequest();
            }

            /* @var $session Mzax_Emarketing_Model_Session */
            $session = Mage::getSingleton('mzax_emarketing/session');

            $eventId = $this->getResource()->insertEvent(array(
                'event_type'   => $type,
                'recipient_id' => $this->getId(),
                'ip'           => $request->getServer('REMOTE_ADDR'),
                'useragent'    => $request->getServer('HTTP_USER_AGENT'),
                'time_offset'  => $session->getTimeOffset()
            ));


            if ($session->getTimeOffset() === null) {
                // try to get the timeoffset using javascript if possible
                $session->fetchTimeOffset($this->getId());
            }
            return $eventId;
        }
        catch(Exception $e) {
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
            Mage::logException($e);
        }

        return false;
    }



}
