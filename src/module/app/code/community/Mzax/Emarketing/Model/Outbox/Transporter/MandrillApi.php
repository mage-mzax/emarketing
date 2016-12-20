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
 * Mandrill API transporter
 */
class Mzax_Emarketing_Model_Outbox_Transporter_MandrillApi
    extends Zend_Mail_Transport_Abstract
    implements Mzax_Emarketing_Model_Outbox_Transporter_Interface
{
    const API_URI = 'https://mandrillapp.com/api/1.0/';

    /**
     * @var string
     */
    protected $_defaultTags;

    /**
     * @var boolean
     */
    protected $_categoryTags = false;

    /**
     * @var boolean
     */
    protected $_metaTags = true;

    /**
     * Optional mandrill subacount
     *
     * @var string
     */
    protected $_subaccount;

    /**
     * API Key
     *
     * @var string
     */
    protected $_apiKey;

    /**
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_config;

    /**
     * Mzax_Emarketing_Model_Outbox_Transporter_MandrillApi constructor.
     *
     * Load dependencies
     */
    public function __construct()
    {
        $this->_config = Mage::getSingleton('mzax_emarketing/config');
    }

    /**
     * Test API call
     * or string with error message
     *
     * @param string $apiKey
     * @param string $subAccountId
     *
     * @return string|true
     */
    public function testApi($apiKey, $subAccountId)
    {
        try {
            $response = $this->_call('subaccounts/list', array('key' => $apiKey));
        } catch (Exception $e) {
            return $e->getMessage();
        }

        // Sub-Account is optional
        if (!$subAccountId) {
            return true;
        }

        foreach ($response as $subAccount) {
            if ($subAccount['id'] == $subAccountId) {
                return true;
            }
        }

        return sprintf('No sub-account exists with the id "%s"', $subAccountId);
    }

    /**
     *
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Outbox_Transporter_Smtp::setup()
     */
    public function setup(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $store  = $email->getRecipient()->getStore();

        $this->_apiKey       = $this->_config->get('mzax_emarketing/email/mandrill_api_key', $store);
        $this->_subaccount   = $this->_config->get('mzax_emarketing/email/mandrill_api_subaccount', $store);

        $this->_categoryTags = $this->_config->flag('mzax_emarketing/email/mandrill_api_category_tags', $store);
        $this->_metaTags     = $this->_config->flag('mzax_emarketing/email/mandrill_api_metatags', $store);

        $defaultTags = $this->_config->get('mzax_emarketing/email/mandrill_api_default_tags', $store);
        if (!empty($defaultTags)) {
            $this->_defaultTags = preg_split('/[\s,]+/', $defaultTags, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    /***
     * (non-PHPdoc)
     * @see Zend_Mail_Transport_Abstract::send()
     */
    public function send(Zend_Mail $mail)
    {
        $message = $this->_prepareMessageData($mail);

        $response = $this->_call('messages/send', array('message' => $message));

        if ($response[0]['status'] != 'sent') {
            throw new Exception(
                "Mandrill API status {$response[0]['status']}, reason: {$response[0]['reject_reason']}",
                2
            );
        }
    }

    /**
     * Call action on API
     *
     * @param $action
     * @param array $data
     * @return array
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    protected function _call($action, $data = array())
    {
        if (!isset($data['key'])) {
            $data['key'] = $this->_apiKey;
        }

        $request = Zend_Json::encode($data);

        $client = new Zend_Http_Client(self::API_URI . $action . '.json');
        $client->setMethod($client::POST);
        $client->setRawData($request);

        $response = $client->request();

        return $this->_processResponse($response);
    }

    /**
     * Convert Zend_Mail object to a valid API array
     *
     * @see https://mandrillapp.com/api/docs/messages.JSON.html
     * @param Zend_Mail $mail
     * @return array
     */
    protected function _prepareMessageData(Zend_Mail $mail)
    {
        $headers = $mail->getHeaders();
        $from = $this->_decodeAddressHeader($headers['From'][0]);

        $message = array();
        $message['to'] = array();
        $message['headers'] = array();
        $message['metadata'] = array();
        $message['tags'] = array();
        $message['merge'] = false;

        $message['subject'] = $mail->getSubject();
        $message['html'] = $mail->getBodyHtml()->getContent();
        $message['text'] = $mail->getBodyText()->getContent();
        $message['from_email'] = $from[0];
        $message['from_name']  = $from[1];

        foreach ($headers['To'] as $recipient) {
            if (is_string($recipient)) {
                $recipient = $this->_decodeAddressHeader($recipient);
                $message['to'][] = array(
                    'email' => $recipient[0],
                    'name' => $recipient[1],
                    'type' => 'to',
                );
            }
        }
        $message['subject'] = $mail->getSubject();

        if ($replyTo = $mail->getReplyTo()) {
            $message['headers']['Reply-To'] = $replyTo;
        }

        // @see https://mandrill.zendesk.com/hc/en-us/articles/205582117-Using-SMTP-Headers-to-customize-your-messages#tag-your-messages
        $tags = array();

        // @see https://mandrill.zendesk.com/hc/en-us/articles/205582117-Using-SMTP-Headers-to-customize-your-messages#use-custom-metadata
        $metadata = array();

        if (is_array($this->_defaultTags)) {
            $tags = $this->_defaultTags;
        }

        if ($mail instanceof Mzax_Emarketing_Model_Outbox_Email_Mail) {
            $message['html'] = $mail->getRawBodyHtml();
            $message['text'] = $mail->getRawBodyText();

            $recipient = $mail->getRecipient();
            $campaign  = $recipient->getCampaign();

            if ($this->_categoryTags) {
                $tags = array_merge($campaign->getTags(), $tags);
            }

            // there is 200 byte limit - keep things short
            if ($this->_metaTags) {
                $metadata['c_name'] = $campaign->getName();
                $metadata['c_id']   = $campaign->getId();
                $metadata['r_id']   = $recipient->getId();
                $metadata['v_id']   = $recipient->getVariationId();

                if (strlen($metadata['c_name']) > 100) {
                    $metadata['c_name'] = substr($metadata['c_name'], 0, 97) . '...';
                }
            }
        }

        $message['metadata'] = array();
        $message['tags'] = array();

        if (!empty($tags)) {
            $message['tags'] = $tags;
        }
        if (!empty($metadata)) {
            $message['metadata'] = $metadata;
        }
        if (!empty($this->_subaccount)) {
            $message['subaccount'] = $this->_subaccount;
        }

        return $message;
    }

    /**
     * Process Mandrill API response
     *
     * @param Zend_Http_Response $response
     * @return array
     * @throws Exception
     */
    protected function _processResponse(Zend_Http_Response $response)
    {
        try {
            $data = Zend_Json::decode($response->getBody());
        } catch (Exception $e) {
            throw new Exception("Unexpected API return value", 1);
        }

        if ($response->getStatus() != 200) {
            throw new Exception("Mandrill API Error: {$data['message']}", $data['code']);
        }

        return $data;
    }

    /**
     * @return void
     */
    protected function _sendMail()
    {
    }

    /**
     * Decode from/to headers
     *
     * @param $header
     * @return array
     */
    protected function _decodeAddressHeader($header)
    {
        if (preg_match('/(.*)\s+\<(.+)\>/', $header, $match)) {
            $name = $match[1];
            $email = $match[2];
            // @todo decode more?
            return array($email, $name);
        }
        return array($header, null);
    }
}
