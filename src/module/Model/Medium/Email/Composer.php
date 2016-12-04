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
 * Class Mzax_Emarketing_Model_Medium_Email_Composer
 */
class Mzax_Emarketing_Model_Medium_Email_Composer
    extends Mage_Core_Model_Template
    implements Mzax_Emarketing_Model_SalesRule_ICouponManager
{
    const PRERENDER_CACHE_PREFIX = 'MZAX_EMARKETING_PRERENDER_CACHE_';

    const BEACON_PLACEHOLDER ='{BEACON}';

    const LINK_A_TAG = "!<a [^>]*href=\"(.*?)\"[^>]*>(.*?)</a>!is";

    const LINK_MAP_TAG = "!<map [^>]*name=\"(.*?)\"[^>]*>.*?</map>!is";

    const LINK_AREA_TAG = "!<area [^>]*href=\"(.*?)\"[^>]*/>!i";

    const STYLE_TAGS = '!<style\s+type="text/css">(.+?)</style>!is';

    /**
     *
     * @var Mzax_Emarketing_Model_Recipient
     */
    protected $_recipient;

    /**
     * @var string
     */
    protected $_bodyText;

    /**
     * @var string
     */
    protected $_bodyHtml;

    /**
     * @var string
     */
    protected $_subject;

    /**
     * @var Mzax_Emarketing_Model_Link_Reference[]
     */
    protected $_linkReferences;

    /**
     * @var Mage_SalesRule_Model_Coupon[]
     */
    protected $_coupons = array();

    /**
     * Render and cache
     *
     * @var bool
     */
    protected $_prerender = true;

    /**
     * @var int
     */
    protected $_renderTime;

    /**
     * Set recipient
     *
     * @param Mzax_Emarketing_Model_Recipient $recipient
     *
     * @return $this
     */
    public function setRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $this->_recipient = $recipient;
        $this->reset();

        return $this;
    }

    /**
     * Reset composer
     */
    public function reset()
    {
        $this->_linkReferences = array();
        $this->_coupons = array();
        $this->_bodyHtml = null;
        $this->_bodyText = null;
        $this->_subject = null;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return self::TYPE_HTML;
    }

    /**
     * Retrieve all created link references
     *
     * @return Mzax_Emarketing_Model_Link_Reference[]
     */
    public function getLinkReferences()
    {
        return $this->_linkReferences;
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getRecipient()
    {
        return $this->_recipient;
    }

    /**
     * @return string
     */
    public function getBodyHtml()
    {
        return $this->_bodyHtml;
    }

    /**
     * @return string
     */
    public function getBodyText()
    {
        return $this->_bodyText;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @return int
     */
    public function getRenderTime()
    {
        return $this->_renderTime;
    }

    /**
     * @return bool
     */
    public function allowPrerender()
    {
        $data = $this->getRecipient()->getContent()->getMediumData();
        if (isset($data['prerender']) && $data['prerender']) {
            return true;
        }
        return false;
    }

    /**
     * Add coupon
     *
     * @param Mage_SalesRule_Model_Coupon $coupon
     *
     * @return $this
     */
    public function addCoupon(Mage_SalesRule_Model_Coupon $coupon)
    {
        $this->_coupons[] = $coupon;

        return $this;
    }

    /**
     * Retrieve coupons
     *
     * @return Mage_SalesRule_Model_Coupon[]
     */
    public function getCoupons()
    {
        return $this->_coupons;
    }

    /**
     * Retrieve template processor
     *
     * @return Mzax_Emarketing_Model_Medium_Email_Processor
     */
    public function getTemplateProcessor()
    {
        $store = $this->getRecipient()->getStore();

        $recipient = $this->getRecipient();
        $recipient->prepare();

        /* @var $processor Mzax_Emarketing_Model_Medium_Email_Processor */
        $processor = Mage::getModel('mzax_emarketing/medium_email_processor');
        $processor->setCouponManager($this);
        $processor->isPreview();
        $processor->setStoreId($recipient->getStoreId());
        $processor->setContent($this->getContent());
        $processor->setVariables($recipient->getData());
        $processor->setVariables(
            array(
                'current_year' => date('Y'),
                'date'         => Mage::app()->getLocale()->storeDate($store),
                'store'        => $recipient->getCampaign()->getStore(),
                'url'          => $recipient->getUrls()
            )
        );

        return $processor;
    }

    /**
     * Retrieve content
     *
     * @return Mzax_Emarketing_Model_Campaign_Content
     */
    public function getContent()
    {
        $content = $this->getRecipient()->getContent();
        $store   = $this->getRecipient()->getStore();

        if ($this->allowPrerender()) {
            $cacheId = self::PRERENDER_CACHE_PREFIX . $content->getContentCacheId();

            $data = Mage::app()->loadCache($cacheId);
            if ($data) {
                $data = unserialize($data);
            }
            if (!$data) {
                $storeId = $this->getRecipient()->getStoreId();

                /* @var $processor Mzax_Emarketing_Model_Medium_Email_Processor */
                $processor = Mage::getModel('mzax_emarketing/medium_email_processor');
                $processor->disableVarDirective(true);
                $processor->setStoreId($storeId);
                $processor->setContent($content);
                $processor->setVariables(array(
                    'current_year' => date('Y'),
                    'date'         => Mage::app()->getLocale()->storeDate($store),
                    'store'        => $this->getRecipient()->getCampaign()->getStore()
                ));

                $this->emulateDesign($storeId);
                $subject  = $processor->getSubject();
                $bodyHtml = $processor->getBodyHtml();
                $bodyText = $processor->getBodyText();
                $this->revertDesign();

                if (Mage::getStoreConfigFlag('mzax_emarketing/email/css_inliner', $storeId)) {
                    $this->inlineCss($bodyHtml);
                }

                if (Mage::getStoreConfigFlag('mzax_emarketing/email/remove_comments', $storeId)) {
                    $this->removeComments($bodyHtml);
                }

                $data = new Varien_Object;
                $data->setData('subject', $subject);
                $data->setData('body_html', $bodyHtml);
                $data->setData('body_text', $bodyText);

                Mage::app()->saveCache(serialize($data), $cacheId, array(Mzax_Emarketing_Model_Campaign::CACHE_TAG));
            }
            /* @var $fullCache Mzax_Emarketing_Model_Medium_Email_FullCache */
            $fullCache = Mage::getModel('mzax_emarketing/medium_email_fullCache');
            $fullCache->setContentCacheId($cacheId);
            $fullCache->setMediumData($data);

            return $fullCache;
        }

        return $content;
    }

    /**
     * Compose Email
     *
     * Parse all mage expressions and prepare html for sending
     *
     * @throws Exception
     * @param boolean $previewMode
     * @return Mzax_Emarketing_Model_Medium_Email_Composer
     */
    public function compose($previewMode = false)
    {
        if (!$this->_recipient) {
            throw new Exception("Can not compose email without a recipient");
        }
        $this->reset();

        $start = microtime(true);

        $recipient = $this->getRecipient();
        $storeId   = $recipient->getStoreId();
        $processor = $this->getTemplateProcessor();
        $processor->isPreview($previewMode);

        if ($this->allowPrerender()) {
            $this->_subject  = $processor->getSubject();
            $processor->setVariables(array('subject' => $this->_subject));

            $this->_bodyHtml = $processor->getBodyHtml();
            $this->_bodyText = $processor->getBodyText();

            // insert view tacking beacon
            $this->insertBeacon($this->_bodyHtml);

            // make links trackable
            $this->parseLinks($this->_bodyHtml);
        } else {
            $this->emulateDesign($recipient->getStoreId());
            $this->_subject  = $processor->getSubject();
            $processor->setVariables(array('subject' => $this->_subject));

            $this->_bodyHtml = $processor->getBodyHtml();
            $this->_bodyText = $processor->getBodyText();
            $this->revertDesign();

            // insert view tacking beacon
            $this->insertBeacon($this->_bodyHtml);

            // make links trackable
            $this->parseLinks($this->_bodyHtml);


            if (Mage::getStoreConfigFlag('mzax_emarketing/email/css_inliner', $storeId)) {
                $this->inlineCss($this->_bodyHtml);
            }
            if (Mage::getStoreConfigFlag('mzax_emarketing/email/remove_comments', $storeId)) {
                $this->removeComments($this->_bodyHtml);
            }
        }
        $this->_renderTime = microtime(true) - $start;

        return $this;
    }

    /**
     * Inline CSS
     *
     * @param string $html
     *
     * @return void
     */
    protected function inlineCss(&$html)
    {
        /** @var Mzax_Emarketing_Helper_Data $helper */
        $helper = Mage::helper('mzax_emarketing');

        $helper->encodeMageExpr($html);

        // @todo Maybe use Pelago_Emogrifier (but only available in later versionsof Magento)
        $cssInliner = new TijsVerkoyen_CssToInlineStyles_CssToInlineStyles($html);
        $cssInliner->setUseInlineStylesBlock(true);
        $html = $cssInliner->convert();

        $helper->decodeMageExpr($html);
    }

    /**
     * Remove comments
     *
     * @param string $html
     *
     * @return void
     */
    protected function removeComments(&$html)
    {
        // remove leading & trailing spaces
        $html = preg_replace('/^\s+|\s+$/m', "", $html);

        // remove html comments
        $html = preg_replace('/<!--(.*)-->/Uis', '', $html);

        // remove empty lines
        $html = preg_replace("/([\n\r]+)/", "\n", $html);

        // cleanup css tags
        $html = preg_replace_callback(self::STYLE_TAGS, array($this, 'cleanUpCss'), $html);
    }

    /**
     * Clean up CSS
     *
     * @param string[] $match
     *
     * @return string
     */
    protected function cleanUpCss($match)
    {
        $css = $match[1];

        // replace double quotes by single quotes
        $css = str_replace('"', '\'', $css);

        // remove comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);

        // remove leading & trailing spaces
        $css = preg_replace('/^\s+|\s+$/m', '', $css);

        // remove lines
        $css = preg_replace("/([\n\r]+)/", "", $css);
        $css = preg_replace("/}\\s*/", "}\n", $css);

        // allow extra line for @media queries
        $css = preg_replace("/^@(.*?){/m", "\n@$1{\n", $css);

        return "<style type=\"text/css\">\n$css</style>\n";
    }

    /**
     * Parse links
     *
     * @param string $html
     *
     * @return void
     */
    protected function parseLinks(&$html)
    {
        // replace all links with trackable links
        $html = preg_replace_callback(self::LINK_A_TAG, array($this, '__replaceLinkCallback'), $html);
        $html = preg_replace_callback(self::LINK_MAP_TAG, array($this, '__replaceMapCallback'), $html);
    }


    /**
     * PregReplace callback for <a> tag links
     *
     * @param string[] $matches
     *
     * @return string
     */
    public function __replaceLinkCallback($matches)
    {
        list($linkHtml, $url, $anchor) = $matches;

        if (strpos(strtolower($url), 'mailto:') === 0) {
            return $linkHtml;
        }

        $link = $this->createLinkReference($url, $anchor);
        $linkHtml = str_replace("href=\"{$url}\"", "href=\"{$link}\"", $linkHtml);

        return preg_replace("!\s+!", ' ', $linkHtml);
    }

    /**
     * Create new link reference
     *
     * @param string $url
     * @param string $anchor
     *
     * @return Mzax_Emarketing_Model_Link_Reference
     */
    public function createLinkReference($url, $anchor)
    {
        // normalize anchor text
        $anchor = trim(preg_replace("!\s+!s", ' ', $anchor));

        // unique key
        $key = md5(strtolower($url.$anchor));

        // if not yet created, create new one
        if (!isset($this->_linkReferences[$key])) {
            /* @var $reference Mzax_Emarketing_Model_Link_Reference */
            $reference = Mage::getModel('mzax_emarketing/link_reference');
            $reference->setRecipient($this->_recipient);
            $reference->setLink($url, $anchor);

            $this->_linkReferences[$key] = $reference;
        }

        return $this->_linkReferences[$key];
    }

    /**
     * Get url to beacon image
     *
     * @return string
     */
    public function getBeaconImage()
    {
        $store = $this->_recipient->getCampaign()->getStore();
        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false);

        return $baseUrl . "emarketing-media/{$this->_recipient->getBeaconHash()}/logo.gif";
    }

    /**
     * Insert tracking beacon
     * allows to see if an email is viewed or not
     *
     * @return string
     */
    public function insertBeacon(&$html)
    {
        $beaconHtml = "<img alt=\"{$this->_recipient->getCampaign()->getStore()->getName()}\" src=\"{$this->getBeaconImage()}\" style=\"width:10px; height:5px;\" />";

        // if beacon placeholder exist replace it
        if (strpos($html, self::BEACON_PLACEHOLDER) !== false) {
            $html = str_replace(self::BEACON_PLACEHOLDER, $beaconHtml, $html);
        } elseif (strpos($html, "</body>") !== false) {
            // if not try to append it just before the body tag closes
            $html = str_replace("</body>", "{$beaconHtml}\n</body>", $html);
        } else {
            // if everything fails add it to the end
            $html .= $beaconHtml;
        }

        return $this;
    }

    /**
     * @var string[]
     */
    protected $_currentMapName;

    /**
     * PregReplace callback for <map> tag links
     *
     * @param array $matches
     * @return string
     */
    public function __replaceMapCallback($matches)
    {
        list($mapHtml, $name) = $matches;

        $this->_currentMapName = array($name, $this->_getTagByUsemap($name));
        $mapHtml = preg_replace_callback(self::LINK_AREA_TAG, array($this, '__replaceMapLinkCallback'), $mapHtml);
        $this->_currentMapName = null;

        return $mapHtml;
    }

    /**
     * Retrieve original tag that binds to the given usemap
     *
     * Helpful to retrieve the image that was used for a <area> tag
     *
     * @param string $usemap
     *
     * @return string
     */
    public function _getTagByUsemap($usemap)
    {
        if (preg_match("!<[^>]*usemap=\"#{$usemap}\"[^>]*>!is", $this->_bodyHtml, $matches)) {
            return $matches[0];
        }

        return '';
    }

    /**
     * @param string[] $matches
     *
     * @return string
     */
    public function __replaceMapLinkCallback($matches)
    {
        list($area, $url) = $matches;

        if (strpos(strtolower($url), 'mailto:') === 0) {
            return $url;
        }

        $label = array($this->_currentMapName[1]);

        if ($shape = $this->_extractAttribute($area, 'shape')) {
            $label[] = $shape;
        }
        if ($coords = $this->_extractAttribute($area, 'coords')) {
            $label[] = $coords;
        }

        /* @var $link Mzax_Emarketing_Model_Link */
        $link = $this->createLinkReference($url, implode(':', $label));

        return str_replace("href=\"{$url}\"", "href=\"{$link}\"", $area);
    }

    /**
     * Extract attribute from a html tag
     *
     * e.g. '<a href="http://example.com">...'
     * _extractAttribute($tag, 'href');
     *
     * @param string $htmlTag
     * @param string $attribute
     * @return string|null
     */
    protected function _extractAttribute($htmlTag, $attribute)
    {
        if (preg_match("!{$attribute}=\"(.*?)\"!i", $htmlTag, $match)) {
            return $match[1];
        }

        return null;
    }
}
