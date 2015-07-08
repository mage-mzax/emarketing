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
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Medium_Email_Composer
    extends Mage_Core_Model_Template
{
    
    const PRERENDER_CACHE_PREFIX = 'MZAX_EMARKETING_PRERENDER_CACHE_';
    
    const BEACON_PLACEHOLDER ='{BEACON}';
    
    const LINK_A_TAG = "!<a [^>]*href=\"(.*?)\"[^>]*>(.*?)</a>!is";
    
    const LINK_MAP_TAG = "!<map [^>]*name=\"(.*?)\"[^>]*>.*?</map>!is";
    
    const LINK_AREA_TAG = "!<area [^>]*href=\"(.*?)\"[^>]*/>!i";
    
    
    
    /**
     * 
     * @var Mzax_Emarketing_Model_Recipient
     */
    protected $_recipient;
    
    
    protected $_bodyText;
    
    protected $_bodyHtml;
    
    protected $_subject;
    
    
    protected $_linkReferences;
    
    
    protected $_prerender = true;
    
    protected $_renderTime;
    
    
    
    
    public function setRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $this->_recipient = $recipient;
        $this->reset();
        return $this;
    }
    
    
    public function reset()
    {
        $this->_linkReferences = array();
        $this->_bodyHtml = null;
        $this->_bodyText = null;
        $this->_subject = null;
    }
    
    
    
    public function getType()
    {
        return self::TYPE_HTML;
    }
    
    
    
    
    /**
     * Retrieve all created link references
     *
     * @return array
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
    
    
    public function getBodyHtml()
    {
        return $this->_bodyHtml;
    }
    
    
    public function getBodyText()
    {
        return $this->_bodyText;
    }
    
    
    public function getSubject()
    {
        return $this->_subject;
    }
    
    
    public function getRenderTime()
    {
        return $this->_renderTime;
    }
    
    
    public function allowPrerender()
    {
        $data = $this->getRecipient()->getContent()->getMediumData();
        if(isset($data['prerender']) && $data['prerender']) {
            return true;
        }
        return false;
    }
    
    
    /**
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
        $processor->setStoreId($recipient->getStoreId());
        $processor->setContent($this->getContent());
        $processor->setVariables($recipient->getData());
        $processor->setVariables(array(
            'current_year' => date('Y'),
            'date'         => Mage::app()->getLocale()->storeDate($store),
            'store'        => $recipient->getCampaign()->getStore(),
            'url'          => $recipient->getUrls()
        ));
        
        return $processor;
    }
    
    
    
    
    
    public function getContent()
    {
        $content = $this->getRecipient()->getContent();
        $store   = $this->getRecipient()->getStore();
        
        if($this->allowPrerender()) {
            $cacheId = self::PRERENDER_CACHE_PREFIX . $content->getContentCacheId();
            
            $data = Mage::app()->loadCache($cacheId);
            if($data) {
                $data = unserialize($data);
            }
            if(!$data) {
                
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
                
                if(Mage::getStoreConfigFlag('mzax_emarketing/email/css_inliner', $storeId)) {
                    $this->inlineCss($bodyHtml);
                }
                
                if(Mage::getStoreConfigFlag('mzax_emarketing/email/remove_comments', $storeId)) {
                    $this->removeComments($bodyHtml);
                }
                
                $data = new Varien_Object;
                $data->setSubject($subject);
                $data->setBodyHtml($bodyHtml);
                $data->setBodyText($bodyText);
                
                Mage::app()->saveCache(serialize($data), $cacheId, array(Mzax_Emarketing_Model_Campaign::CACHE_TAG));
            }
            /* @var $fullCache Mzax_Emarketing_Model_Medium_Email_FullCache */
            $fullCache = Mage::getModel('mzax_emarketing/medium_email_fullCache');
            $fullCache->setMediumData($data);
            
            return $fullCache;
        }
        
        return $content;
    }
    
    
    
    
    
    
    
    
    
    /**
     * Compose Email
     * 
     * Parse all mage expresions and prepare html for sending
     * 
     * @throws Exception
     * @return Mzax_Emarketing_Model_Medium_Email_Composer
     */
    public function compose()
    {
        if(!$this->_recipient) {
            throw new Exception("Can not compose email without a recipient");
        }
        $this->reset();
        
        $start = microtime(true);
        
        $recipient = $this->getRecipient();
        $storeId   = $recipient->getStoreId();
        $processor = $this->getTemplateProcessor();
        
        
        if($this->allowPrerender()) {
            $this->_subject  = $processor->getSubject();
            $processor->setVariables(array('subject' => $this->_subject));
            
            $this->_bodyHtml = $processor->getBodyHtml();
            $this->_bodyText = $processor->getBodyText();
            
            // insert view tacking beacon
            $this->insertBeacon($this->_bodyHtml);
            
            // make links trackable
            $this->parseLinks($this->_bodyHtml);
        }
        else {
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
            
            
            if(Mage::getStoreConfigFlag('mzax_emarketing/email/css_inliner', $storeId)) {
                $this->inlineCss($this->_bodyHtml);
            }
            if(Mage::getStoreConfigFlag('mzax_emarketing/email/remove_comments', $storeId)) {
                $this->removeComments($this->_bodyHtml);
            }
        }
        
        $this->_renderTime = microtime(true) - $start;
        
        return $this;
    }
    
    
    
    protected function inlineCss(&$html)
    {
        Mage::helper('mzax_emarketing')->encodeMageExpr($html);
        
        // @todo Maybe use Pelago_Emogrifier (but only available in later versionsof Magento)
        $cssInliner = new TijsVerkoyen_CssToInlineStyles_CssToInlineStyles($html);
        $cssInliner->setUseInlineStylesBlock(true);
        $html = $cssInliner->convert();
        
        Mage::helper('mzax_emarketing')->decodeMageExpr($html);
    }
    
    
    const STYLE_TAGS = '!<style\s+type="text/css">(.+?)</style>!is';
    
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
        $css = preg_replace("/}\s*/", "}\n", $css);
        
        // allow extra line for @media queries
        $css = preg_replace("/^@(.*?){/m", "\n@$1{\n", $css);
        
        
        
        return "<style type=\"text/css\">\n$css</style>\n";
        
    }
    
    
    
    
    
    protected function parseLinks(&$html)
    {
        // replace all links with trackable links
        $html = preg_replace_callback(self::LINK_A_TAG,   array($this, '__replaceLinkCallback'), $html);
        $html = preg_replace_callback(self::LINK_MAP_TAG, array($this, '__replaceMapCallback'),  $html);
    }
    
    
    /**
     * PregReplace callback for <a> tag links
     *
     * @param array $matches
     * @return string
     */
    public function __replaceLinkCallback($matches)
    {
        list($linkHtml, $url, $anchor) = $matches;
    
        if(strpos(strtolower($url), 'mailto:') === 0) {
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
     * @return Mzax_Emarketing_Model_Link_Reference
     */
    public function createLinkReference($url, $anchor)
    {
        // normalize anchor text
        $anchor = trim(preg_replace("!\s+!s", ' ', $anchor));
    
        // unqiue key
        $key = md5(strtolower($url.$anchor));
        
        // if not yet created, create new one
        if(!isset($this->_linkReferences[$key])) {
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
        $baseUrl = $this->_recipient->getCampaign()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false);
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
        if(strpos($html, self::BEACON_PLACEHOLDER) !== false) {
            $html = str_replace(self::BEACON_PLACEHOLDER, $beaconHtml, $html);
        }
        // if not try to append it just before the body tag closes
        else if(strpos($html, "</body>") !== false) {
            $html = str_replace("</body>", "{$beaconHtml}\n</body>", $html);
        }
        // if everything fails add it to the end
        else {
            $html .= $beaconHtml;
        }
    
        return $this;
    }
    
    
    
    
    
    
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
     * Retrieve orginal tag that binds to the given usemap
     *
     * Helpfull to retrieve the image that was used for a <area> tag
     *
     * @param string $usemap
     * @return string
     */
    public function _getTagByUsemap($usemap)
    {
        if(preg_match("!<[^>]*usemap=\"#{$usemap}\"[^>]*>!is", $this->_bodyHtml, $matches)) {
            return $matches[0];
        }
        return '';
    }
    
    
    
    
    
    public function __replaceMapLinkCallback($matches)
    {
        list($area, $url) = $matches;
    
        if(strpos(strtolower($url), 'mailto:') === 0) {
            return $linkHtml;
        }
    
        $label = array($this->_currentMapName[1]);
    
        if($shape = $this->_extractAttribute($area, 'shape')) {
            $label[] = $shape;
        }
        if($coords = $this->_extractAttribute($area, 'coords')) {
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
        if(preg_match("!{$attribute}=\"(.*?)\"!i", $htmlTag, $match)) {
            return $match[1];
        }
        return null;
    }
    
    
    
    
}
