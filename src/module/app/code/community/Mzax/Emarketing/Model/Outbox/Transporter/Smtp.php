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
 * PHP Sendmail transporter
 */
class Mzax_Emarketing_Model_Outbox_Transporter_Smtp
    extends Zend_Mail_Transport_Smtp
    implements Mzax_Emarketing_Model_Outbox_Transporter_Interface
{
    const CONFIG_HOSTNAME = 'mzax_emarketing/email/smtp_hostname';
    const CONFIG_USERNAME = 'mzax_emarketing/email/smtp_username';
    const CONFIG_PASSWORD = 'mzax_emarketing/email/smtp_password';
    const CONFIG_AUTH = 'mzax_emarketing/email/smtp_auth';
    const CONFIG_SSL = 'mzax_emarketing/email/smtp_ssl';
    const CONFIG_PORT = 'mzax_emarketing/email/smtp_port';

    /**
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_storeConfig;

    /**
     * Mzax_Emarketing_Model_Outbox_Transporter_Smtp constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_storeConfig = Mage::getSingleton('mzax_emarketing/config');
    }

    /**
     * @param Mzax_Emarketing_Model_Outbox_Email $email
     *
     * @return void
     */
    public function setup(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $store  = $email->getCampaign()->getStore();

        $hostname = $this->_storeConfig->get(self::CONFIG_HOSTNAME, $store);
        $username = $this->_storeConfig->get(self::CONFIG_USERNAME, $store);
        $password = $this->_storeConfig->get(self::CONFIG_PASSWORD, $store);
        $auth = $this->_storeConfig->get(self::CONFIG_AUTH, $store);
        $ssl = $this->_storeConfig->get(self::CONFIG_SSL, $store);
        $port = $this->_storeConfig->get(self::CONFIG_PORT, $store);

        $this->_auth = $auth;
        $this->_host = $hostname;
        $this->_port = $port;

        $this->_config = array(
            'username' => $username,
            'password' => $password,
            'port'     => $port,
            'ssl'      => $ssl ? 'tls' : false,
        );
    }

    /**
     * @param Zend_Mail $mail
     *
     * @return void
     */
    public function send(Zend_Mail $mail)
    {
        parent::send($mail);
    }
}
