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
 * Class Mzax_Emarketing_Model_Outbox_Email_Mail
 */
class Mzax_Emarketing_Model_Outbox_Email_Mail
    extends Zend_Mail
{
    const UTF8 = 'utf-8';

    /**
     * @var Mzax_Emarketing_Model_Outbox_Email
     */
    protected $_outboxEmail;

    /**
     * @var string
     */
    protected $_rawBodyHtml;

    /**
     * @var string
     */
    protected $_rawBodyText;

    /**
     * Mzax_Emarketing_Model_Outbox_Email_Mail constructor.
     */
    public function __construct()
    {
        parent::__construct(self::UTF8);
    }

    /**
     *
     *
     * @param string $html
     * @param null $charset
     * @param string $encoding
     *
     * @return $this
     */
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $this->_rawBodyHtml = $html;
        parent::setBodyHtml($html, $charset, $encoding);

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRawBodyHtml()
    {
        if ($this->_rawBodyHtml) {
            return $this->_rawBodyHtml;
        }
        if ($this->_bodyHtml) {
            return $this->_bodyHtml->getContent();
        }
        return null;
    }

    /**
     * @param string $txt
     * @param null $charset
     * @param string $encoding
     *
     * @return $this
     */
    public function setBodyText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $this->_rawBodyText = $txt;
        parent::setBodyText($txt, $charset, $encoding);

        return $this;
    }

    /**
     *
     *
     * @return null|string
     */
    public function getRawBodyText()
    {
        if ($this->_rawBodyText) {
            return $this->_rawBodyText;
        }
        if ($this->_bodyText) {
            return $this->_bodyText->getContent();
        }
        return null;
    }

    /**
     * Set outbox email object
     *
     * @param Mzax_Emarketing_Model_Outbox_Email $email
     *
     * @return Mzax_Emarketing_Model_Outbox_Email_Mail
     */
    public function setOutboxEmail(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $this->_outboxEmail = $email;

        return $this;
    }

    /**
     * Retrieve outbox email object
     *
     * @return Mzax_Emarketing_Model_Outbox_Email
     */
    public function getOutboxEmail()
    {
        return $this->_outboxEmail;
    }

    /**
     * Retrieve recipient
     *
     * @return Mzax_Emarketing_Model_Recipient|NULL
     */
    public function getRecipient()
    {
        if ($this->_outboxEmail) {
            return $this->_outboxEmail->getRecipient();
        }
        return null;
    }
}
