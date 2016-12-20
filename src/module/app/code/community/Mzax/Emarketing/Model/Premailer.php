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
 * Class Mzax_Emarketing_Model_Premailer
 *
 * @see http://premailer.dialect.ca/api
 */
class Mzax_Emarketing_Model_Premailer
{
    const ADAPTER_HPRICOT = 'hpricot';
    const ADAPTER_NOKOGIRI = 'nokogiri';
    const API_URI = 'http://premailer.dialect.ca/api/0.1/documents';
    const CACHE_KEY = 'emarketing_premailer';


    protected $_clientConfig = array(
        'maxredirects' => 0,
        'timeout'      => 30,
        'useragent'    => 'Magento - Emarketing Mailer v1'
    );

    /**
     * @var string
     */
    public $html;

    /**
     * @var string
     */
    public $cacheId;

    /**
     * @var string
     */
    public $adapter = self::ADAPTER_HPRICOT;

    /**
     * @var string
     */
    public $baseUrl = '';

    /**
     * @var int
     */
    public $lineLength = 65;

    /**
     * @var string
     */
    public $linkQueryString = '';

    /**
     * @var bool
     */
    public $preserveStyles = true;

    /**
     * @var bool
     */
    public $removeIds = false;

    /**
     * @var bool
     */
    public $removeClasses = false;

    /**
     * @var bool
     */
    public $removeComments = false;

    /**
     * Retrieve params
     *
     * @return array
     */
    protected function getParams()
    {
        $params = array(
            'adapter'  => $this->adapter,
            'base_url' => $this->baseUrl,
            'html'     => $this->html,
            'line_length'       => $this->lineLength,
            'link_query_string' => $this->linkQueryString,
            'preserve_styles'   => $this->preserveStyles,
            'remove_ids'        => $this->removeIds,
            'remove_classes'    => $this->removeClasses,
            'remove_comments'   => $this->removeComments
        );

        return $params;
    }

    /**
     * Retrieve cache key
     *
     * @return bool|string
     */
    protected function _getCacheKey()
    {
        if (!$this->cacheId) {
            return false;
        }
        $params = $this->getParams();
        unset($params['html']);

        return self::CACHE_KEY . '_' . md5($this->cacheId . '_' . print_r($params, true));
    }

    /**
     * Process
     *
     * @return Varien_Object
     * @throws Exception
     */
    public function process()
    {
        $cacheId = $this->_getCacheKey();
        $response = $cacheId ? Mage::app()->loadCache($cacheId) : false;
        if ($response) {
            $response = unserialize($response);
        }

        if (!$response instanceof Varien_Object) {
            $client = new Zend_Http_Client(self::API_URI, $this->_clientConfig);
            $client->setMethod(Zend_Http_Client::POST);
            $client->setParameterPost($this->getParams());
            $result = $client->request();

            $data = Zend_Json::decode($result->getBody());

            if ($data['status'] != 201) {
                throw new Exception("Premailer failed to run: {$data['message']} #{$data['status']}");
            }

            $htmlClient = new Zend_Http_Client($data['documents']['html'], $this->_clientConfig);
            $textClient = new Zend_Http_Client($data['documents']['txt'], $this->_clientConfig);

            $response = new Varien_Object();
            $response->setData('version', $data['version']);
            $response->setData('html', $htmlClient->request()->getBody());
            $response->setData('body', $textClient->request()->getBody());

            if ($cacheId) {
                Mage::app()->saveCache(serialize($response), $cacheId, array(self::CACHE_KEY), 60*60);
            }
        }

        return $response;
    }
}
