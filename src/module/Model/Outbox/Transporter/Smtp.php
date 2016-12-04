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
    /**
     * @param Mzax_Emarketing_Model_Outbox_Email $email
     *
     * @return void
     */
    public function setup(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $store  = $email->getCampaign()->getStore();

        $hostname = Mage::getStoreConfig('mzax_emarketing/email/smtp_hostname', $store);
        $username = Mage::getStoreConfig('mzax_emarketing/email/smtp_username', $store);
        $password = Mage::getStoreConfig('mzax_emarketing/email/smtp_password', $store);
        $auth     = Mage::getStoreConfig('mzax_emarketing/email/smtp_auth', $store);
        $ssl      = Mage::getStoreConfig('mzax_emarketing/email/smtp_ssl', $store);
        $port     = Mage::getStoreConfig('mzax_emarketing/email/smtp_port', $store);

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
}
