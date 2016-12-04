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
 * Class Mzax_Emarketing_Model_Outbox_Email
 *
 * @method string getExpireAt()
 * @method $this setExpireAt(string $value)
 *
 * @method string getSentAt()
 * @method $this setSentAt(string $value)
 *
 * @method string getStatus()
 * @method $this setStatus(string $value)
 *
 * @method string getEmail()
 * @method $this setEmail(string $value)
 *
 * @method string getTo()
 *
 * @method string getDomain()
 * @method $this setDomain(string $value)
 *
 * @method string getSubject()
 * @method $this setSubject(string $value)
 *
 * @method string getBodyText()
 * @method $this setBodyText(string $value)
 *
 * @method string getBodyHtml()
 * @method $this setBodyHtml(string $value)
 *
 * @method float getRenderTime()
 * @method $this setRenderTime(float $value)
 *
 * @method string getTimeFilter()
 * @method string getDayFilter()
 * @method bool getCancelEmail()
 *
 * @method $this setMessageId(string $value)
 * @method $this setLog()
 * @method $this setCancelEmail(bool $value)
 *
 * @method Mzax_Emarketing_Model_Resource_Outbox_Email getResource()
 */
class Mzax_Emarketing_Model_Outbox_Email extends Mzax_Emarketing_Model_Email
{
    const STATUS_NOT_SEND  = 0;
    const STATUS_SENT      = 1;
    const STATUS_EXPIRED   = 2;
    const STATUS_FAILED    = 3;
    const STATUS_DISCARDED = 4;

    const REGEX_EMAIL = '/^[a-z0-9_\-]+(?:\.[_a-z0-9\-]+)*@((?:[_a-z0-9\-]+\.)+(?:[a-z]{2,}))$/i';

    /**
     * Use a tranport mock to for viewing the
     * message that will be usend with all its headers
     *
     * @var Mzax_Mail_Transport_Mock
     */
    protected $_source;

    /**
     * Zend_Log
     *
     * @var Zend_Log
     */
    protected $_log;

    /**
     *
     * @var array
     */
    protected $_logEvents;

    /**
     * List of all generated link references
     *
     * @var array
     */
    protected $_linkReferences = array();

    /**
     * List of generated coupons
     *
     * @var array
     */
    protected $_coupons = array();

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/outbox_email');
    }

    /**
     * Retrieve medium
     *
     * @return Mzax_Emarketing_Model_Medium_Email
     */
    public function getMedium()
    {
        return Mage::getSingleton('mzax_emarketing/medium_email');
    }

    /**
     * Before save
     *
     * @return void
     */
    protected function _beforeSave()
    {
        if (!empty($this->_logEvents)) {
            $this->setData('log', Zend_Json::encode($this->_logEvents));
        }

        // make sure we have a message id
        $this->getMessageId();

        parent::_beforeSave();
    }

    /**
     * After save
     *
     * @return void
     */
    protected function _afterSave()
    {
        if (!empty($this->_linkReferences)) {
            $this->saveLinks();
        }
        if (!empty($this->_coupons)) {
            $this->saveCoupons();
        }
    }

    /**
     * Retrieve all created link references
     *
     * @return array
     */
    public function getLinkReferences()
    {
        return $this->_linkReferences;
    }

    /**
     * Remove all temporary links
     *
     * @return $this
     */
    public function clearLinks()
    {
        $this->_linkReferences = array();

        return $this;
    }

    /**
     * Save all attached links
     *
     * @return $this
     */
    protected function saveLinks()
    {
        foreach ($this->_linkReferences as $link) {
            $link->save();
        }
        $this->_linkReferences = array();

        return $this;
    }

    /**
     * Save all attached coupons
     *
     * @return Mzax_Emarketing_Model_Outbox_Email
     */
    protected function saveCoupons()
    {
        foreach ($this->_coupons as $coupon) {
            $coupon->save();
        }
        $this->_coupons = array();

        return $this;
    }

    /**
     * Use a mock mail transport to genereate the
     * final output
     *
     * @return Mzax_Mail_Transport_Mock
     */
    public function getSource()
    {
        if (!$this->_source) {
            $this->_source = new Mzax_Mail_Transport_Mock();

            $mailObject = $this->createMailObject();
            if ($mailObject) {
                $mailObject->send($this->_source);
            }
        }

        return $this->_source;
    }

    /**
     * Get plain text message version
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getBodyText();
    }

    /**
     * Get raw mime email content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getSource()->body;
    }

    /**
     * Get raw email headers
     *
     * @return string
     */
    public function getHeaders()
    {
        return $this->getSource()->header;
    }

    /**
     * Retrieve log object
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        if (!$this->_log) {
            $writer = new Zend_Log_Writer_Mock;
            if ($data = $this->getData('log')) {
                try {
                    $writer->events = Zend_Json::decode($data);
                } catch (Exception $e) {
                    // @todo backup current data
                    Mage::logException($e);
                }
            }

            $this->_logEvents = &$writer->events;

            $this->_log = new Zend_Log;
            $this->_log->addWriter($writer);
        }
        return $this->_log;
    }

    /**
     * Retrieve email composer
     *
     * @return Mzax_Emarketing_Model_Medium_Email_Composer
     */
    public function getEmailComposer()
    {
        return Mage::getSingleton('mzax_emarketing/medium_email_composer');
    }

    /**
     * Render this email
     * An email can be rerendered as lon as it has not been sent
     *
     * @param bool $previewMode
     *
     * @return $this
     */
    public function render($previewMode = false)
    {
        if ($this->getStatus() == self::STATUS_NOT_SEND) {
            $composer = $this->getEmailComposer();
            $composer->setRecipient($this->getRecipient());
            $composer->compose($previewMode);

            $this->setSubject($composer->getSubject());
            $this->setBodyHtml($composer->getBodyHtml());
            $this->setBodyText($composer->getBodyText());
            $this->setRenderTime($composer->getRenderTime());

            $this->_linkReferences = $composer->getLinkReferences();

            // don't actually save any coupons for mock emails
            if (!$this->getRecipient()->isMock()) {
                $this->_coupons = $composer->getCoupons();
            }
        }

        return $this;
    }

    /**
     * Retrieve message id or generate new one if
     * we don't have one yet
     *
     * @return string
     */
    public function getMessageId()
    {
        $id = $this->getData('message_id');
        if (!$id) {
            $sender = $this->getCampaign()->getSender();
            $serverName = strstr($sender['email'], '@');

            if (!$serverName) {
                $baseUrl = $this->getCampaign()->getStore()->getBaseUrl();
                if (preg_match('/([a-z0-9][a-z0-9-]{1,61}[a-z0-9]\.[a-z]{2,})/i', $baseUrl, $match)) {
                    $serverName = '@' . $match[1];
                } else {
                    $serverName = '@' . Mage::app()->getRequest()->getServer('SERVER_NAME');
                }
            }

            $id = time() . '.' . $this->getRecipient()->getBeaconHash() . $serverName;
            $this->setData('message_id', $id);
        }

        return $id;
    }

    /**
     * Create zend mail object for sending out
     * the email
     *
     * @return Zend_Mail
     */
    public function createMailObject()
    {
        $sender  = $this->getCampaign()->getSender();

        $recipient = $this->getRecipient();
        $recipient->prepare();

        /** @var Mzax_Emarketing_Helper_Data $helper */
        $helper = Mage::helper('mzax_emarketing');

        $version = $helper->getVersion();

        /* @var $mail Mzax_Emarketing_Model_Outbox_Email_Mail */
        $mail = Mage::getModel('mzax_emarketing/outbox_email_mail');
        $mail->setOutboxEmail($this);
        $mail->setSubject($this->getSubject());
        $mail->addTo($recipient->getAddress(), '=?utf-8?B?'.base64_encode($recipient->getName()).'?=');
        $mail->setMessageId($this->getMessageId());
        $mail->setBodyText($this->getBodyText());
        $mail->setBodyHtml($this->getBodyHtml());
        $mail->setFrom($sender['email'], $sender['name']);
        $mail->addHeader('X-Mailer', 'Mzax-Emarketing ' . $version);
        $mail->addHeader('X-Mailer-Version', $version);
        $mail->addHeader('X-Originating-IP', Mage::app()->getRequest()->getServer('SERVER_ADDR'));

        // Add List-Unsubscribe
        if (Mage::getStoreConfigFlag('mzax_emarketing/email/list_unsubscribe', $recipient->getStoreId())) {
            $unsubscribe = array();

            $address = Mage::getStoreConfig('mzax_emarketing/email/list_unsubscribe_address', $recipient->getStoreId());
            if ($address) {
                $unsubscribe[] = "mailto:{$address}?subject=Unsubscribe%20{$recipient->getAddress()}%20({$recipient->getBeaconHash()})";
            }
            $unsubscribe[] = $recipient->getUrl('mzax_emarketing/unsubscribe/list', array('id' => $recipient->getBeaconHash()));

            foreach ($unsubscribe as &$value) {
                $value = "<{$value}>";
            }

            $mail->addHeader('List-Unsubscribe', implode(',', $unsubscribe));
        }

        $this->setCancelEmail(false);
        Mage::dispatchEvent('mzax_emarketing_create_mail_object', array(
            'mail'         => $mail,
            'recipient'    => $recipient,
            'outbox_email' => $this
        ));

        if ($this->getCancelEmail()) {
            return null;
        }

        if (Mage::getStoreConfigFlag('mzax_emarketing/email/test_mode', $recipient->getStoreId())) {
            $address = Mage::getStoreConfig('mzax_emarketing/email/test_mode_address', $recipient->getStoreId());

            if ($recipient->getForceAddress()) {
                $address = $recipient->getForceAddress();
            }

            if (!$address) {
                return null;
            }
            $mail->clearRecipients();
            $mail->addTo($address);
        }

        return $mail;
    }

    /**
     * Set recipients email address
     *
     * @param string $email
     * @return Mzax_Emarketing_Model_Outbox_Email
     */
    public function setTo($email)
    {
        $this->setData('to', $email);
        if (preg_match(self::REGEX_EMAIL, $email, $match)) {
            $this->setData('domain', $match[1]);
        }

        return $this;
    }

    /**
     * If a time filter is set, then the outbox will only send
     * this email if the current time (hour of the day [0-23]) matches
     * this filter
     *
     * Note: Setting a time filter can cause a significant delay
     * for the email beore it gets send or it prevents the email from
     * sending at all if the expire date has reached
     *
     * @param string[] $times
     *
     * @return $this
     */
    public function setTimeFilter($times)
    {
        if (is_array($times)) {
            $times = implode(',', $times);
        }
        $this->setData('time_filter', $times);

        return $this;
    }

    /**
     * If a day filter is set, then the outbox will only send
     * this email if the current day (day of the week [0-6]) matches
     * this filter
     *
     * Note: Setting a day filter can cause a significant delay
     * for the email beore it gets send or it prevents the email from
     * sending at all if the expire date has reached
     *
     * @param string[] $days
     *
     * @return $this
     */
    public function setDayFilter($days)
    {
        if (is_array($days)) {
            $days = implode(',', $days);
        }
        $this->setData('day_filter', $days);

        return $this;
    }

    /**
     * Check if email is expired and should not
     * get send out anymore
     *
     * @param integer $now
     *
     * @return boolean
     */
    public function isExpired($now = null)
    {
        if (!$now) {
            $now = time();
        }
        if ($this->getExpireAt()) {
            $expireAt = Varien_Date::toTimestamp($this->getExpireAt());
            return $expireAt < $now;
        }

        return false;
    }

    /**
     * Is purged
     *
     * @return boolean
     */
    public function isPurged()
    {
        return (bool) $this->getData('purged');
    }

    /**
     * Can email be send right now
     * Check any filters (e.g. Time and Day) and make
     * sure none of them prevent the email from sending
     *
     * @param integer $now
     *
     * @return boolean
     */
    public function canSend($now = null)
    {
        if (!$now) {
            $now = time();
        }

        // use admin store time
        $now -= (int) Mage::app()->getLocale()->storeDate(Mage_Core_Model_App::ADMIN_STORE_ID)->getGmtOffset();

        if ($filter = $this->getDayFilter()) {
            $dayOfWeek = date('w', $now);
            $filter = explode(',', $filter);

            if (!in_array($dayOfWeek, $filter)) {
                return false;
            }
        }
        if ($filter = $this->getTimeFilter()) {
            $hourOfDay = date('G', $now);
            $filter = explode(',', $filter);

            if (!in_array($hourOfDay, $filter)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare mail transporter
     *
     * @return Mzax_Emarketing_Model_Outbox_Transporter_Interface
     */
    protected function _prepareTransporter()
    {
        $store  = $this->getCampaign()->getStore();

        /** @var Mzax_Emarketing_Model_Outbox_Transporter $factory */
        $factory = Mage::getSingleton('mzax_emarketing/outbox_transporter');

        $transporter = Mage::getStoreConfig('mzax_emarketing/email/transporter', $store);
        $transporter = $factory->factory($transporter);
        $transporter->setup($this);

        $wrapper = new Varien_Object();
        $wrapper->setData('transporter', $transporter);

        Mage::dispatchEvent('mzax_emarketing_email_prepare_transport', array(
            'data'  => $wrapper,
            'email' => $this
        ));

        return $wrapper->getData('transporter');
    }

    /**
     * Send email
     *
     * @param bool $verbose
     *
     * @return $this
     * @throws Exception
     */
    public function send($verbose = false)
    {
        /** @var Mzax_Emarketing_Helper_Data $h */
        $h = Mage::helper('mzax_emarketing');

        try {
            $h->log("Send Message: #%s - %s", $this->getId(), $this->getEmail());

            if (!$this->_send()) {
                $h->log("Message was not send. #%s - %s", $this->getId(), $this->getEmail());
            }

            $this->setStatus(self::STATUS_SENT);
            $this->setSentAt(now());
            $this->getRecipient()->isSent(true);
        } catch (Exception $e) {
            Mage::logException($e);

            $h->log("Mail send exception: %s", $e->getMessage());

            $message = $e->getMessage() . "\nStackTrace:\n";
            $message.= $e->getTraceAsString();

            $this->getLog()->err($message);
            $this->setStatus(self::STATUS_FAILED);

            if ($verbose) {
                echo $message;
            }

            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
        }

        $this->save();
        $this->getRecipient()->save();

        return $this;
    }

    /**
     * Send email
     *
     * @return bool
     */
    protected function _send()
    {
        $mail = $this->createMailObject();
        if ($mail) {
            $mail->send($this->_prepareTransporter());
            return true;
        }

        return false;
    }
}
