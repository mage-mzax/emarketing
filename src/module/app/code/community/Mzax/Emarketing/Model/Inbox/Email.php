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
 * Class Mzax_Emarketing_Model_Inbox_Email
 *
 * @method string getCreatedAt()
 * @method string getHeaders()
 * @method string getIsParsed()
 * @method string getCampaignId()
 * @method string getRecipientId()
 * @method string getSubject()
 * @method string getMessage()
 * @method string getSentAt()
 * @method string getEmail()
 * @method string getType()
 * @method string getPurged()
 * @method string getSize()
 *
 * @method $this setCreatedAt(string $value)
 * @method $this setHeaders(string $value)
 * @method $this setContent(string $value)
 * @method $this setIsParsed(string $value)
 * @method $this setRecipientId(string $value)
 * @method $this setCampaignId(string $value)
 * @method $this setSubject(string $value)
 * @method $this setMessage(string $value)
 * @method $this setSentAt(string $value)
 * @method $this setEmail(string $value)
 * @method $this setType(string $value)
 * @method $this setSize(string $value)
 *
 * @method Mzax_Emarketing_Model_Resource_Inbox_Email getResource()
 *
 */
class Mzax_Emarketing_Model_Inbox_Email
    extends Mzax_Emarketing_Model_Email
{
    const BOUNCE_SOFT  = 'SB';
    const BOUNCE_HARD  = 'HB';
    const AUTOREPLY    = 'AR';
    const NO_BOUNCE    = 'NB';
    const UNSUBSCRIBE  = 'US';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_inbox_email';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'email';

    /**
     * Email content
     *
     * @var string
     */
    protected $_content;

    /**
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_config;

    /**
     * Retrieve bounce decoder
     *
     * @return Mzax_Bounce_Detector
     */
    public static function getBounceDecoder()
    {
        static $bounceDecoder;
        if (!$bounceDecoder) {
            $bounceDecoder = new Mzax_Bounce_Detector;
            $bounceDecoder->addDetector(Mage::getModel('mzax_emarketing/inbox_bounce_detector_unsubscribe'), 0);
            $bounceDecoder->addDetector(Mage::getModel('mzax_emarketing/inbox_bounce_detector_recipient'), 1);
            $bounceDecoder->addDetector(Mage::getModel('mzax_emarketing/inbox_bounce_detector_store'), 2);
        }
        return $bounceDecoder;
    }

    /**
     * Model constructor
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/inbox_email');

        $this->_config = Mage::getSingleton('mzax_emarketing/config');
    }

    /**
     * Before save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        if ($this->_content !== null && !$this->isPurged()) {
            $this->setSize(strlen($this->_content));
        }
        parent::_beforeSave();

        return $this;
    }

    /**
     * Parse the current message
     *
     * Return the parsing duration
     *
     * @return int
     * @throws Exception
     */
    public function parse()
    {
        /** @var Mzax_Emarketing_Helper_Newsletter $newsletterHelper */
        $newsletterHelper = Mage::helper('mzax_emarketing/newsletter');

        /** @var Mzax_Emarketing_Model_Medium_Email $emailMedium */
        $emailMedium = Mage::getSingleton('mzax_emarketing/medium_email');

        // not possible if email is purged
        if ($this->isPurged()) {
            return 0;
        }
        $start = microtime(true);

        $this->getResource()->flagAsParsed($this);

        try {
            /* @var $message Mzax_Bounce_Message */
            $message = new Mzax_Bounce_Message($this->getRawData());
            $message->setHeader('created_at', $this->getCreatedAt());

            $result = self::getBounceDecoder()->inspect($message);
            $result = new Varien_Object($result);

            $update = array(
                'is_parsed'    => 1,
                'email'        => $result->getRecipient(),
                'sent_at'      => $result->getSentAt(),
                'status_code'  => $result->getStatus(),
                'is_arf'       => (int) $result->getArf(),
                'is_autoreply' => (int) $result->getAutoreply(),
                'arf_type'     => $result->getFeedbackType(),
                'recipient_id' => $result->getRecipientId(),
                'store_id'     => $result->getStoreId(),
                'campaign_id'  => $result->getCampaignId(),
            );

            $status = $result->getStatus();
            if ($status) {
                if (strpos($status, '4.') === 0) {
                    $update['type'] = self::BOUNCE_SOFT;
                }
                if (strpos($status, '5.') === 0) {
                    $update['type'] = self::BOUNCE_HARD;
                }
            } elseif ($result->getAutoreply()) {
                $update['type'] = self::AUTOREPLY;
            } elseif ($result->getUnsubscribe()) {
                $update['type'] = self::UNSUBSCRIBE;
                /* @see $subscriber Mzax_Emarketing_Helper_Newsletter */
                $newsletterHelper->unsubscribe($result->getRecipient(), $result->getStoreId(), false);
            } else {
                $update['type'] = self::NO_BOUNCE;
            }


            if ($part = $message->findMinePart(Zend_Mime::TYPE_TEXT)) {
                $text = $part->getDecodedContent();
            } else {
                $text = $message->getDecodedContent();
            }
            if ($pos = strpos($text, '---')) {
                $text = substr($text, 0, $pos);
            }
            $text = Mage::helper('core/string')->stripTags($text);
            $text = Mage::helper('core/string')->truncate($text, 512);

            $update['message'] = $text;
            $update['subject'] = $message->getSubject();


            $this->addData($update);
            $this->save();

            Mage::dispatchEvent('mzax_emarketing_inbox_email_parse', array(
                'email'   => $this,
                'message' => $message,
                'result'  => $result
            ));

            if ($this->shouldForward()) {
                $this->forward($message);
            }

            $unsubscribeHardBounce = $this->_config->flag(
                'mzax_emarketing/inbox/unsubscribe_hard_bounce',
                $this->getStore()
            );

            // unsubscribe hard bounces
            if (!$this->getData('no_unsubscribe') && $this->getType() == self::BOUNCE_HARD && $unsubscribeHardBounce) {
                $emailMedium->unsubscribe($this->getEmail(), sprintf('%s bounce, email %s', $status, $this->getId()));
            }
        } catch (Exception $e) {
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
            Mage::logException($e);
        }

        return microtime(true) - $start;
    }

    /**
     * Forward email
     *
     * @param Mzax_Bounce_Message $message
     *
     * @return boolean
     */
    public function forward(Mzax_Bounce_Message $message = null)
    {
        // not possible if email is purged
        if ($this->isPurged()) {
            return false;
        }

        if (!$message) {
            $message = new Mzax_Bounce_Message($this->getRawData());
        }

        $mail = $message->forward();

        $canSend = false;
        foreach ($this->getForwardToEmails() as $email) {
            if (Zend_Validate::is($email, 'EmailAddress')) {
                $mail->addTo($email);
                $canSend = true;
            }
        }

        if (!$canSend) {
            Mage::helper('mzax_emarketing')->log("Unable to forward message, no email address defined.");
            return false;
        }

        $sender = $this->getSender();
        $mail->setFrom($sender['email'], $sender['name']);
        $mail->addHeader('X-Mailer', 'Mzax-Emarketing '.Mage::helper('mzax_emarketing')->getVersion());
        $mail->addHeader('X-Originating-IP', Mage::app()->getRequest()->getServer('SERVER_ADDR'));


        $this->setMailTransport(null);
        Mage::dispatchEvent('mzax_emarketing_inbox_email_forward', array(
            'mail'    => $mail,
            'email'   => $this,
            'message' => $message
        ));

        // in case an event observer has set one
        $transport = $this->getMailTransport();

        $mail->send($transport);
        Mage::helper('mzax_emarketing')->log("Email %s been forwarded.", $this->getId());

        return true;
    }

    /**
     * Send email report
     *
     * @return void
     */
    public function report()
    {
        // not possible if email is purged
        if ($this->isPurged()) {
            return;
        }
        $sender = $this->getSender();

        $mail = new Zend_Mail();
        $mail->setFrom($sender['email'], $sender['name']);
        $mail->setSubject(sprintf("Bounce Report (%s)", Mage::helper('mzax_emarketing')->getVersion()));
        $mail->setBodyText("The following message appears to be a bounce.\nPlease verify.\n\n");
        $mail->createAttachment(
            $this->getRawData(),
            'message/rfc822',
            Zend_Mime::DISPOSITION_ATTACHMENT,
            Zend_Mime::ENCODING_BASE64,
            sprintf('bounce.%s.%s.eml', Mage::app()->getRequest()->getServer('SERVER_ADDR'), time())
        );
        $mail->addTo('jacob@mzax.de');
        $mail->addHeader('X-Mailer', 'Mzax-Emarketing '.Mage::helper('mzax_emarketing')->getVersion());
        $mail->send();
    }

    /**
     * Retrieve sender
     *
     * @return string[]
     */
    public function getSender()
    {
        if ($campaign = $this->getCampaign()) {
            return $campaign->getSender();
        }
        $sender = $this->_config->get('mzax_emarketing/inbox/forward_identity', $this->getStore());

        return array(
            'name'  => $this->_config->get('trans_email/ident_'.$sender.'/name', $this->getStore()),
            'email' => $this->_config->get('trans_email/ident_'.$sender.'/email', $this->getStore()),
        );
    }

    /**
     * Retrieve email addresses to forward non-bonce messages
     *
     * @return string[]
     */
    public function getForwardToEmails()
    {
        $emails = $this->_config->get('mzax_emarketing/inbox/forward_emails', $this->getStore());
        $emails = explode(',', $emails);
        $emails = array_map('trim', $emails);

        $campaign = $this->getCampaign();
        if ($campaign) {
            $campaignEmails = $campaign->getMediumData()->getForwardEmails();
            if (!empty($campaignEmails)) {
                $campaignEmails = explode(',', $campaignEmails);
                $emails = array_merge($emails, $campaignEmails);
            }
        }

        $emails = array_map('trim', $emails);
        $emails = array_map('strtolower', $emails);
        $emails = array_filter($emails);
        $emails = array_unique($emails);

        return $emails;
    }

    /**
     * Should this message get forwarded
     *
     * @return boolean
     */
    public function shouldForward()
    {
        if (!$this->getIsParsed() || $this->getNoForward() || $this->isPurged()) {
            return false;
        }
        if ($this->isARF() || $this->isAutoreply() || $this->isBounce()) {
            return false;
        }
        return true;
    }

    /**
     * Load email content
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->_content === null && $this->getId() && !$this->isPurged()) {
            $file = $this->getResource()->getContentFile($this->getId());
            if (file_exists($file)) {
                $this->_content = file_get_contents($file);
            } else {
                $this->_content = (string) $this->getData('content');
            }
        }
        return $this->_content;
    }

    /**
     * Retrieve full raw email data
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->getHeaders() . Zend_Mime::LINEEND . $this->getContent();
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
     * Is already parsed
     *
     * @return boolean
     */
    public function isParsed()
    {
        return (bool) $this->getData('is_parsed');
    }

    /**
     * Is Bounce (soft or hard)
     *
     * @return boolean
     */
    public function isBounce()
    {
        switch ($this->getData('type')) {
            case self::BOUNCE_HARD:
            case self::BOUNCE_SOFT:
                return true;
        }
        return false;
    }

    /**
     * Is ARF (aka Feedback Loop)
     *
     * @return boolean
     */
    public function isARF()
    {
        return (bool) $this->getData('is_arf');
    }

    /**
     * Is autoreply
     *
     * @return boolean
     */
    public function isAutoreply()
    {
        return (bool) $this->getData('is_autoreply');
    }
}
