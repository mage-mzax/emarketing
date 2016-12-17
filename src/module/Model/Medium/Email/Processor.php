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
 * Class Mzax_Emarketing_Model_Medium_Email_Processor
 */
class Mzax_Emarketing_Model_Medium_Email_Processor
{
    /**
     * @var Mzax_Emarketing_Model_Medium_Email_Filter
     */
    protected $filter;

    /**
     * @var Mzax_Emarketing_Model_Campaign_Content
     */
    protected $_content;

    /**
     * @var string
     */
    protected $_subject;

    /**
     * @var string
     */
    protected $_bodyHtml;

    /**
     * @var string
     */
    protected $_bodyText;

    /**
     * @var boolean
     */
    protected $_enableFullCache = false;

    /**
     * Mzax_Emarketing_Model_Medium_Email_Processor constructor.
     */
    public function __construct()
    {
        $this->filter = Mage::getModel('mzax_emarketing/medium_email_filter');
        $this->filter->setUseAbsoluteLinks(true);
    }

    /**
     * Disable var directive
     *
     * Allow to disable the var directive to prevent parsing {{$variable}} expressions.
     *
     * This is used to pre-cache templates for faster sending.
     * This only makes sense if other expressions do not render any recipient
     * specific data.
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function disableVarDirective($flag = true)
    {
        $this->filter->disableVarDirective($flag);

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return $this
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
     *
     * @return $this
     */
    public function setContent(Mzax_Emarketing_Model_Campaign_Content $content)
    {
        $this->_content = $content;

        return $this;
    }

    /**
     * Retrieve medium data
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getMediumData($key)
    {
        return $this->getContent()->getMediumData()->getData($key);
    }

    /**
     * Retrieve content provider
     *
     * @return Mzax_Emarketing_Model_Campaign_Content
     * @throws Exception
     */
    public function getContent()
    {
        if (!$this->_content) {
            throw new Exception("No content provider added to processor");
        }

        return $this->_content;
    }

    /**
     *
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables($variables)
    {
        $this->filter->setVariables($variables);

        return $this;
    }

    /**
     *
     * @param mixed $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->filter->setStoreId($storeId);
        return $this;
    }

    /**
     * Set coupon manager
     *
     * @param Mzax_Emarketing_Model_SalesRule_ICouponManager $manager
     *
     * @return $this
     */
    public function setCouponManager(Mzax_Emarketing_Model_SalesRule_ICouponManager $manager)
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
        if (!$this->_subject) {
            $subject = $this->getMediumData('subject');

            // @TODO prepare template vars?
            $this->_subject = $this->filter->filter($subject);
        }

        return $this->_subject;
    }

    /**
     * Retrieve template used by content
     *
     * @return Mzax_Emarketing_Model_Template
     * @throws Exception
     */
    public function getTemplate()
    {
        $template = $this->getMediumData('template');
        if ($template instanceof Mzax_Emarketing_Model_Template) {
            return $template;
        }

        $templateId = $this->getMediumData('template_id');
        $template = $this->createTemplateModel();
        $template->load($templateId);

        if (!$template->getId()) {
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
        if ($this->_bodyHtml === null) {
            $html = $this->composeBodyHtml();
            $html = $this->filter->filter($html);

            $this->_bodyHtml = $html;
        }
        return $this->_bodyHtml;
    }

    /**
     * Retrieve body text
     *
     * @return string
     */
    public function getBodyText()
    {
        if ($this->_bodyText === null) {
            $text = $this->composeBodyText();
            $text = $this->filter->filter($text);

            $this->_bodyText = $text;
        }

        return $this->_bodyText;
    }

    /**
     * Retrieve email body template
     *
     * @return string
     */
    protected function composeBodyHtml()
    {
        // check if medium data provides a body html
        $bodyHtml = $this->getMediumData('body_html');
        if ($bodyHtml) {
            return $bodyHtml;
        }

        $cacheKey = 'mzax_email_cache_' . $this->_content->getContentCacheId();

        // check if template has already been cached
        $bodyHtml = Mage::app()->loadCache($cacheKey);
        if ($bodyHtml) {
            return $bodyHtml;
        }

        $template = $this->getTemplate();
        $contentBody = $this->getMediumData('body');
        if (is_string($contentBody)) {
            $contentBody = Zend_Json::decode($contentBody);
        }

        $bodyHtml = $template->render($contentBody);

        Mage::app()->saveCache($bodyHtml, $cacheKey, array(Mzax_Emarketing_Model_Campaign::CACHE_TAG));

        return $bodyHtml;
    }

    /**
     * Compose text version of the email body
     *
     * Check if the medium provides a text version, if that the case use it
     * other wise create a text version from the HTML version using
     * the Html2Text lib.
     *
     * @return string
     */
    protected function composeBodyText()
    {
        // check if medium data provides a body text
        $bodyText = $this->getMediumData('body_text');
        if ($bodyText) {
            return $bodyText;
        }

        try {
            $html = $this->getBodyHtml();

            libxml_use_internal_errors(true);
            $text = Html2Text_Html2Text::convert($html);

            return $text;
        }
        catch(Exception $e) {
            if (Mage::getIsDeveloperMode()) {
                return $e->getMessage();
            }
        }

        return '';
    }

    /**
     * Create new template model
     *
     * @return Mzax_Emarketing_Model_Template
     */
    protected function createTemplateModel()
    {
        return Mage::getModel('mzax_emarketing/template');
    }
}
