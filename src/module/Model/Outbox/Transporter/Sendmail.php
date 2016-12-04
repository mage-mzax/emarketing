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
class Mzax_Emarketing_Model_Outbox_Transporter_Sendmail
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
        $store = $email->getCampaign()->getStore();
        $sender = $email->getCampaign()->getSender();

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host', $store));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port', $store));

        switch (Mage::getStoreConfig(Mage_Core_Model_Email_Template::XML_PATH_SENDING_SET_RETURN_PATH, $store)) {
            case 1:
                $this->parameters = "-f" . $sender['email'];
                break;
            case 2:
                $this->parameters = "-f" . Mage::getStoreConfig(Mage_Core_Model_Email_Template::XML_PATH_SENDING_RETURN_PATH_EMAIL, $store);
                break;
        }
    }
}
