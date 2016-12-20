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
 * Class Mzax_Emarketing_Model_Link_Reference
 *
 * @method string getLinkId()
 * @method $this setLinkId(string $value)
 *
 * @method string getRecipientId()
 * @method $this setRecipientId(string $value)
 *
 * @method string getPublicId()
 * @method $this setPublicId(string $value)
 *
 * @method Mzax_Emarketing_Model_Resource_Link_Reference getResource()
 * @method Mzax_Emarketing_Model_Resource_Link_Reference _getResource()
 */
class Mzax_Emarketing_Model_Link_Reference extends Mzax_Emarketing_Model_AbstractModel
{
    const CONFIG_ENABLE_GA = 'mzax_emarketing/google_analytics/enable';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_link_reference';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'reference';

    /**
     * url model
     *
     * @var Mzax_Emarketing_Model_Link
     */
    protected $_link;

    /**
     * recipient model
     *
     * @var Mzax_Emarketing_Model_Recipient
     */
    protected $_recipient;

    /**
     * Dependencies
     *
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_config;

    /**
     * Model Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/link_reference');

        $this->_config = Mage::getSingleton('mzax_emarketing/config');
    }

    /**
     * Load public id or create new if non available
     *
     * @return Mzax_Emarketing_Model_Link_Reference
     */
    public function loadPublicId()
    {
        if (!$this->getPublicId() && $this->getLinkId() && $this->getRecipientId()) {
            $this->_getResource()->loadPublicId($this, $this->getLinkId(), $this->getRecipientId());
        }
        if (!$this->getPublicId()) {
            $this->setPublicId($this->makePublicKey($this->getLink()));
        }
        return $this;
    }

    /**
     * Make random public key
     *
     * @param Mzax_Emarketing_Model_Link $link
     *
     * @return string
     */
    public function makePublicKey(Mzax_Emarketing_Model_Link $link)
    {
        /** @var Mzax_Emarketing_Helper_Data $helper */
        $helper = Mage::helper('mzax_emarketing');

        return $helper->randomHash(
            $link->getId() .
            $link->getLinkHash()
        );
    }

    /**
     * Before save
     *
     * @return void
     * @throws Exception
     */
    protected function _beforeSave()
    {
        // make sure the link is saved
        if ($this->_link && !$this->_link->getId()) {
            $this->_link->save();
        }
        if ($this->_link) {
            $this->setLinkId($this->_link->getId());
        }
        if ($this->_recipient) {
            $this->setRecipientId($this->_recipient->getId());
        }

        if (!$this->getLinkId()) {
            throw new Exception("Unable to save link reference, no link id set");
        }

        $this->loadPublicId();

        parent::_beforeSave();
    }

    /**
     * Retrieve link model
     *
     * @return Mzax_Emarketing_Model_Link
     */
    public function getLink()
    {
        if (!$this->_link) {
            $this->_link = Mage::getModel('mzax_emarketing/link');
            $this->_link->load($this->getLinkId());
        }

        return $this->_link;
    }

    /**
     * Set link
     *
     * @param Mzax_Emarketing_Model_Link|string $link
     * @param string $anchor
     *
     * @return Mzax_Emarketing_Model_Link_Reference
     * @throws BadMethodCallException
     */
    public function setLink($link, $anchor)
    {
        if (is_string($link)) {
            $this->_link = Mage::getModel('mzax_emarketing/link');
            $this->_link->init($link, $anchor);
        } elseif ($link instanceof Mzax_Emarketing_Model_Link) {
            $this->_link = $link;
        } else {
            throw new BadMethodCallException("Invalid link argument");
        }

        $this->setLinkId($this->_link->getId());
        $this->loadPublicId();

        return $this;
    }

    /**
     * Retrieve recipient
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getRecipient()
    {
        if (!$this->_recipient) {
            $this->_recipient = Mage::getModel('mzax_emarketing/recipient');
            $this->_recipient->load($this->getRecipientId());
        }
        return $this->_recipient;
    }

    /**
     * Set recipient model
     *
     * @param Mzax_Emarketing_Model_Recipient $recipient
     * @return $this
     */
    public function setRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $this->_recipient = $recipient;
        $this->setRecipientId($recipient->getId());

        return $this;
    }

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     * @deprecated Typo in name
     */
    public function getCampagin()
    {
        return $this->getCampaign();
    }

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        return $this->getRecipient()->getCampaign();
    }

    /**
     * Retrieve redirect URL used in emails
     *
     * @param array $params
     *
     * @return string
     */
    public function getRedirectUrl($params = array())
    {
        $store = $this->getCampaign()->getStore();
        $url = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "link-goto/".$this->getPublicId();

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Retrieve target url
     *
     * @return string
     */
    public function getTargetUrl()
    {
        $storeId = $this->getCampaign()->getStoreId();

        $url = $this->getLink()->getUrl();

        $gaEnabled = $this->_config->flag(self::CONFIG_ENABLE_GA, $storeId);

        if ($gaEnabled) {
            $utmParams = array();
            $utmParams['utm_source']   = $this->getUtmSource();
            $utmParams['utm_medium']   = $this->getUtmMedium();
            $utmParams['utm_campaign'] = $this->getUtmCampaign();

            if ($content = $this->getUtmContent()) {
                $utmParams['utm_content'] = $content;
            }
            if ($term = $this->getUtmTerm()) {
                $utmParams['utm_term'] = $term;
            }

            $url .= (strpos($url, '?') ? '&' : '?') . http_build_query($utmParams);
        }

        return $url;
    }

    /**
     * Source is globally set
     *
     * @return string
     */
    public function getUtmSource()
    {
        return $this->_config->flag(
            'mzax_emarketing/google_analytics/utm_source',
            $this->getCampaign()->getStoreId()
        );
    }

    /**
     * Medium is globally set
     *
     * @todo should be defined by campaign medium?
     * @return string
     */
    public function getUtmMedium()
    {
        return$this->_config->get(
            'mzax_emarketing/google_analytics/utm_medium',
            $this->getCampaign()->getStoreId()
        );
    }

    /**
     * Retrieve the utm term
     * Use variation name
     *
     * @return string
     */
    public function getUtmContent()
    {
        $variationId = $this->getRecipient()->getVariationId();

        switch ($variationId) {
            case Mzax_Emarketing_Model_Campaign_Variation::ORIGNAL:
                return '[Original]';
            case Mzax_Emarketing_Model_Campaign_Variation::NONE:
                return '[None]';
        }

        $variation = $this->getCampaign()->getVariation($variationId);
        if ($variation) {
            return $variation->getName();
        }

        return '[N/A]';
    }

    /**
     * Use a stripped version of the link anchor text as term
     *
     * @return string
     */
    public function getUtmTerm()
    {
        $anchor = trim($this->getLink()->getAnchor());

        if ($this->getLink()->getUrl() === $anchor) {
            return "DIRECT LINK";
        }

        // check for any image tag <img src="foo" alt="bar" />
        $anchor = preg_replace_callback('/<img\s+(.*)\s*\/?>/i', function ($matches) {

            // if an alt tag is given, use it
            if (preg_match('/alt=("|\')(.*?)(?:\1)/i', $matches[1], $m)) {
                return "IMG[{$m[2]}]";
            }

            // otherwise use the image src but only the basename to keep it short
            if (preg_match('/src=("|\')(.*?)(?:\1)/i', $matches[1], $m)) {
                $src = basename($m[2]);
                return "IMG[{$src}]";
            }

            return 'IMG';

        }, $anchor);

        return strip_tags($anchor);
    }

    /**
     * Use the campaign name
     *
     * @todo Add extra field for GA name
     * @return string
     */
    public function getUtmCampaign()
    {
        $name = $this->getCampaign()->getName();

        return $name;
    }

    /**
     * Log click for this link so we know that someone clicked
     * on a link inside an email
     *
     * @param Zend_Controller_Request_Http $request
     * @param string $clickId
     * @param string $eventId
     *
     * @return Mzax_Emarketing_Model_Link_Reference
     */
    public function captureClick(Zend_Controller_Request_Http $request = null, &$clickId = null, &$eventId = null)
    {
        $eventId = $this->getRecipient()->captureClick($request);
        $clickId = $this->getResource()->captureClick($this, $eventId);

        return $this;
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getRedirectUrl();
    }
}
