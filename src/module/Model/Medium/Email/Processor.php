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
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Medium_Email_Processor
{
    
    
    /**
     * 
     * @var Mzax_Emarketing_Model_Medium_Email_Filter
     */
    protected $filter;
    
    
    /**
     * 
     * @var Mzax_Emarketing_Model_Campaign_Content
     */
    protected $_content;
    
    
    /**
     * 
     * @var string
     */
    protected $_subject;
    
    
    /**
     * 
     * @var string
     */
    protected $_bodyHtml;
    
    
    /**
     *
     * @var string
     */
    protected $_bodyText;
    
    
    /**
     * 
     * @var boolean
     */
    protected $_enableFullCache = false;
    
    
    public function __construct()
    {
        $this->filter = Mage::getModel('mzax_emarketing/medium_email_filter');
        $this->filter->setUseAbsoluteLinks(true);
    }
    
    
    /**
     * 
     * 
     * @param string $flag
     * @return Mzax_Emarketing_Model_Medium_Email_Processor
     */
    public function disableVarDirective($flag = true)
    {
        $this->filter->disableVarDirective($flag);
        return $this;
    }


    /**
     *
     *
     * @param string $flag
     * @return Mzax_Emarketing_Model_Medium_Email_Processor
     */
    public function isPreview($flag = true)
    {
        $this->filter->isPreview($flag);
        return $this;
    }


    
    /**
     * Add content provider
     * 
     * @param Mzax_Emarketing_Model_Campaign_Content $content
     * @return Mzax_Emarketing_Model_Recipient_Processor
     */
    public function setContent(Mzax_Emarketing_Model_Campaign_Content $content)
    {
        $this->_content = $content;
        return $this;
    }
    
    
    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function getMediumData($key)
    {
        return $this->getContent()->getMediumData()->getData($key);
    }
    
    
    
    /**
     * Retrieve content provider
     * 
     * @throws Exception
     * @return Mzax_Emarketing_Model_Campaign_Content
     */
    public function getContent()
    {
        if(!$this->_content) {
            throw new Exception("No content provider added to processor");
        }
        return $this->_content;
    }
    
    
    /**
     * 
     * @param array $variables
     * @return Mzax_Emarketing_Model_Medium_Email_Processor
     */
    public function setVariables($variables)
    {
        $this->filter->setVariables($variables);
        return $this;
    }
    
    
    /**
     * 
     * @param mixed $storeId
     * @return Mzax_Emarketing_Model_Medium_Email_Processor
     */
    public function setStoreId($storeId)
    {
        $this->filter->setStoreId($storeId);
        return $this;
    }
    
    
    
    /**
     * Set coupon manager
     * 
     * @param unknown $manager
     * @return Mzax_Emarketing_Model_Medium_Email_Processor
     */
    public function setCouponManager($manager)
    {
        $this->filter->setCouponManager($manager);
        return $this;
    }
    
    
    
    /**
     * Retrieve processed email subject
     * 
     * @return string
     */
    public function getSubject()
    {
        if(!$this->_subject) {
            $subject = $this->getMediumData('subject');
            
            // @TODO prepare template vars?
            $this->_subject = $this->filter->filter($subject);
        }
        return $this->_subject;
    }
    

    
    /**
     * Retrieve template used by content
     * 
     * @throws Exception
     * @return Mzax_Emarketing_Model_Template
     */
    public function getTemplate()
    {
        $templateId = $this->getMediumData('template_id');
        $template = Mage::getModel('mzax_emarketing/template')->load($templateId);
        if(!$template->getId()) {
            throw new Exception("Template not found");
        }
        return $template;
    }
    
    
    
    
    /**
     * Retrieve processed email body
     *
     * @return string
     */
    public function getBodyHtml()
    {
        if(!$this->_bodyHtml) {
            
            $bodyHtml = $this->getMediumData('body_html');
            // if no body html is set, render template
            if(!$bodyHtml) {
                $cacheKey = 'mzax_email_cache_' . $this->_content->getContentCacheId();
                $bodyHtml = Mage::app()->loadCache($cacheKey);
                
                if(!$bodyHtml) {
                    $template = $this->getTemplate();
                
                    $data = Zend_Json::decode($this->getMediumData('body'));
                    $bodyHtml = $template->render($data);
                
                    Mage::app()->saveCache($bodyHtml, $cacheKey, array(Mzax_Emarketing_Model_Campaign::CACHE_TAG));
                }
            }
            
            // @TODO prepare template vars?
            $this->_bodyHtml = $this->filter->filter($bodyHtml);
        }
        return $this->_bodyHtml;
    }
    
    
    /**
     * 
     * @return string
     */
    public function getBodyText()
    {
        if(!$this->_bodyText) {
            
            $bodyText = $this->getMediumData('body_text');
            
            // if body text is defined, use it
            if($bodyText) {
                $this->_bodyText = $this->filter->filter($bodyText);
            }
            // create one using the html
            else {
                // @todo Allow custom body text
                try {
                    libxml_use_internal_errors(true);
                    $this->_bodyText = Html2Text_Html2Text::convert($this->getBodyHtml());
                }
                catch(Exception $e) {
                    if(Mage::getIsDeveloperMode()) {
                        $this->_bodyText = $e->getMessage();
                    }
                    else {
                        $this->_bodyText = '';
                    }
                    
                }
            }
        }
        return $this->_bodyText;
    }
    
    

    
    
    
    
}