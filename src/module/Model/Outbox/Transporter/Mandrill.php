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
 * Mandrill transporter
 * 
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Outbox_Transporter_Mandrill
    extends Mzax_Emarketing_Model_Outbox_Transporter_Smtp
{
    
    const HOST  = 'smtp.mandrillapp.com';
    const PORT  = 587;
    const SSL   = 'tls';
    const AUTH  = 'login';
    
    
    
    /**
     * 
     * @var string
     */
    protected $_defaultTags;
    
    
    /**
     * 
     * @var boolean
     */
    protected $_categoryTags = false;
    
    
    
    /**
     * 
     * @var boolean
     */
    protected $_metaTags = true;
    
    
    
    /**
     * Optional mandrill subacount
     * 
     * @var string
     */
    protected $_subaccount = '';
    
    
    
    /**
     * Check login data and return true on success
     * or string with error message
     * 
     * @param string $username
     * @param string $password
     * @return string|true
     */
    public function testAuth($username, $password)
    {
        $connection = new Zend_Mail_Protocol_Smtp_Auth_Login(self::HOST, self::PORT, array(
            'username' => $username,
            'password' => $password,
            'port'     => self::PORT,
            'ssl'      => self::SSL
        ));
        
        try {            
            $connection->connect();
            $connection->helo(Mage::app()->getRequest()->getServer('SERVER_ADDR'));
            $connection->disconnect();
            return true;
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    
    
    
    /**
     * 
     * 
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Outbox_Transporter_Smtp::setup()
     */
    public function setup(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $store  = $email->getRecipient()->getStore();
        
        $username    = Mage::getStoreConfig('mzax_emarketing/email/mandrill_username', $store);
        $password    = Mage::getStoreConfig('mzax_emarketing/email/mandrill_password', $store);
        $defaultTags = Mage::getStoreConfig('mzax_emarketing/email/mandrill_default_tags', $store);
        
        $this->_subaccount   = Mage::getStoreConfig('mzax_emarketing/email/mandrill_subaccount', $store);
        $this->_categoryTags = Mage::getStoreConfigFlag('mzax_emarketing/email/mandrill_category_tags', $store);
        $this->_metaTags     = Mage::getStoreConfigFlag('mzax_emarketing/email/mandrill_metatags', $store);
        
        if (!empty($defaultTags)) {
            $this->_defaultTags = preg_split('/[\s,]+/', $defaultTags, -1, PREG_SPLIT_NO_EMPTY);
        }
        
        $this->_auth = self::AUTH;
        $this->_host = self::HOST;
        $this->_port = self::PORT;
        
        $this->_config = array(
            'username' => $username,
            'password' => $password,
            'port'     => self::PORT,
            'ssl'      => self::SSL
        );
    }
    
    
    
    /***
     * (non-PHPdoc)
     * @see Zend_Mail_Transport_Abstract::send()
     */
    public function send(Zend_Mail $mail)
    {
        // @see https://mandrill.zendesk.com/hc/en-us/articles/205582117-Using-SMTP-Headers-to-customize-your-messages#tag-your-messages
        $tags = array();
         
        // @see https://mandrill.zendesk.com/hc/en-us/articles/205582117-Using-SMTP-Headers-to-customize-your-messages#use-custom-metadata
        $metadata = array();
        
        
        if (is_array($this->_defaultTags)) {
            $tags = $this->_defaultTags;
        }
        
        if ($mail instanceof Mzax_Emarketing_Model_Outbox_Email_Mail) {
            
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
        
        
        if (!empty($tags)) {
            $mail->addHeader('X-MC-Tags', implode(',', $tags));
        }
        if (!empty($metadata)) {
            $mail->addHeader('X-MC-Metadata', Zend_Json::encode($metadata));
        }
        if (!empty($this->_subaccount)) {
            $mail->addHeader('X-MC-Subaccount', $this->_subaccount);
        }
        
        return parent::send($mail);
    }
    
    
}
