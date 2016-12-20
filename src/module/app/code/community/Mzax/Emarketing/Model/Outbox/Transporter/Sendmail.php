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

use Mage_Core_Model_Email_Template as CoreTemplate;


/**
 * PHP Sendmail transporter
 */
class Mzax_Emarketing_Model_Outbox_Transporter_Sendmail
    extends Zend_Mail_Transport_Smtp
    implements Mzax_Emarketing_Model_Outbox_Transporter_Interface
{
    const CONFIG_HOST = 'system/smtp/host';
    const CONFIG_PORT = 'system/smtp/port';
    const CONFIG_RETURN_PATH = CoreTemplate::XML_PATH_SENDING_SET_RETURN_PATH;
    const CONFIG_RETURN_PATH_EMAIL = CoreTemplate::XML_PATH_SENDING_RETURN_PATH_EMAIL;

    /**
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_storeConfig;

    /**
     * Mzax_Emarketing_Model_Outbox_Transporter_Sendmail constructor.
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
        $store = $email->getCampaign()->getStore();
        $sender = $email->getCampaign()->getSender();

        ini_set('SMTP', $this->_storeConfig->get(self::CONFIG_HOST, $store));
        ini_set('smtp_port', $this->_storeConfig->get(self::CONFIG_PORT, $store));

        switch ($this->_storeConfig->get(self::CONFIG_RETURN_PATH, $store)) {
            case 1:
                $this->parameters = "-f" . $sender['email'];
                break;
            case 2:
                $this->parameters = "-f" . $this->_storeConfig->get(self::CONFIG_RETURN_PATH_EMAIL, $store);
                break;
        }
    }
}
