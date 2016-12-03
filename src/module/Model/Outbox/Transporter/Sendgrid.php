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
 * Sendgrid transporter
 * 
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Outbox_Transporter_Sendgrid
    extends Mzax_Emarketing_Model_Outbox_Transporter_Smtp
{
    
    const HOST  = 'smtp.sendgrid.net';
    const PORT  = 587;
    const SSL   = 'tls';
    const AUTH  = 'login';
    
    
    
    /**
     * @see https://sendgrid.com/docs/API_Reference/SMTP_API/categories.html
     * @var string
     */
    protected $_category;
    
    
    /**
     * 
     * @var boolean
     */
    protected $_categoryTags = false;
    
    
    
    /**
     * @see https://sendgrid.com/docs/API_Reference/SMTP_API/unique_arguments.html
     * @var boolean
     */
    protected $_uniqueArgs = true;
    
    
    
    
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
        $store  = $email->getCampaign()->getStore();
        
        $username = Mage::getStoreConfig('mzax_emarketing/email/sendgrid_username', $store);
        $password = Mage::getStoreConfig('mzax_emarketing/email/sendgrid_password', $store);
        $category = Mage::getStoreConfig('mzax_emarketing/email/sendgrid_category', $store);
        
        $this->_categoryTags = Mage::getStoreConfigFlag('mzax_emarketing/email/sendgrid_category_tags', $store);
        $this->_uniqueArgs   = Mage::getStoreConfigFlag('mzax_emarketing/email/sendgrid_unique_args', $store);
        
        if (!empty($category)) {
            $this->_category = preg_split('/[\s,]+/', $category, -1, PREG_SPLIT_NO_EMPTY);
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
        $smtpApi = array(
             // @see https://sendgrid.com/docs/API_Reference/SMTP_API/unique_arguments.html
            'unique_args' => array(),
                
            // @see https://sendgrid.com/docs/API_Reference/SMTP_API/scheduling_parameters.html
            // Allow to delay emails by 2 minutes to queue up all our emails
            // for this proccess
            'send_at' => 60 + (ceil(time()/60)*60)
        );
        
        
        // @see https://sendgrid.com/docs/API_Reference/SMTP_API/categories.html
        if (is_array($this->_category)) {
            $smtpApi['category'] = $this->_category;
        }
        
        if ($mail instanceof Mzax_Emarketing_Model_Outbox_Email_Mail) {
            
            $recipient = $mail->getRecipient();
            $campaign  = $recipient->getCampaign();
            
            if ($this->_categoryTags) {
                $smtpApi['category'] = array_merge($campaign->getTags(), $smtpApi['category']);
            }
            
            if ($this->_uniqueArgs) {
                $smtpApi['unique_args']['mzax_campaign']     = $campaign->getName();
                $smtpApi['unique_args']['mzax_campaign_id']  = $campaign->getId();
                $smtpApi['unique_args']['mzax_recipient_id'] = $recipient->getId();
                $smtpApi['unique_args']['mzax_variation_id'] = $recipient->getVariationId();
            }
        }
        
        
        $mail->addHeader('X-SMTPAPI', Zend_Json::encode($smtpApi));
        
        return parent::send($mail);
    }
    
    
}
