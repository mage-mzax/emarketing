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
 * Emarketing Session
 *
 * @method integer|null getLastLinkReferenceId()
 * @method $this setLastLinkReferenceId(int $value)
 *
 * @method integer|null getLastRecipientId()
 * @method $this setLastRecipientId(int $value)
 *
 * @method string|null getLastAddress()
 * @method $this setLastAddress(string $value)
 */
class Mzax_Emarketing_Model_Session extends Mage_Core_Model_Session_Abstract
{
    const TIME_OFFSET_COOKIE = '_mzax_to';

    /**
     * Last recipient
     *
     * @var Mzax_Emarketing_Model_Recipient
     */
    protected $_lastRecipient;

    /**
     * Mzax_Emarketing_Model_Session constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('mzax_emarketing');
    }

    /**
     * Retrieve last recipient
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function getLastRecipient()
    {
        if (!$this->_lastRecipient && $this->getLastRecipientId()) {
            $this->_lastRecipient = Mage::getModel('mzax_emarketing/recipient')->load($this->getLastRecipientId());
        }

        return $this->_lastRecipient;
    }

    /**
     * Add a click reference to the seesion
     *
     * This reference is used by the observer to check if any goal events like
     * a order, sign-up occurred during this session
     *
     * @see Mzax_Emarketing_Model_Observer_Goal
     * @param Mzax_Emarketing_Model_Link_Reference $linkReference
     * @param integer $clickId
     * @return Mzax_Emarketing_Model_Session
     */
    public function addClickReference(Mzax_Emarketing_Model_Link_Reference $linkReference, $clickId)
    {
        $this->setLastLinkReferenceId($linkReference->getId());
        $this->setLastRecipientId($linkReference->getRecipientId());
        $this->setLastAddress($linkReference->getRecipient()->getAddress());
        $this->_lastRecipient = null;

        $clicks = $this->getClickReferences();
        $clicks[] = array(
            'link_reference_id' => (int) $linkReference->getId(),
            'recipient_id'      => (int) $linkReference->getRecipientId(),
            'click_id'          => (int) $clickId
        );

        $this->setData('click_references', $clicks);

        return $this;
    }

    /**
     * Retrieve all current click references
     *
     * @return array
     */
    public function getClickReferences()
    {
        $clicks = $this->getData('click_references');
        if (!$clicks) {
            $clicks = array();
        }

        return $clicks;
    }

    /**
     * Do we need the users time offset
     *
     * @return boolean
     */
    public function requireTimeOffset()
    {
        $timeOffset = $this->getTimeOffset();

        if ($timeOffset !== null) {
            $ids = $this->getData('time_offset_ids', true);
            if (!empty($ids)) {
                Mage::getResourceSingleton('mzax_emarketing/recipient_event')->updateTimeoffset($timeOffset, $ids);
            }
            return false;
        }

        return $this->hasData('time_offset_ids');
    }

    /**
     * Retrieve local user time offset from session or cookie
     *
     * @return NULL|number
     */
    public function getTimeOffset()
    {
        $offset = $this->getData('time_offset');
        if ($offset === null) {
            // check if we have a cookie available
            $offset = $this->getCookie()->get(self::TIME_OFFSET_COOKIE);
            if ($offset === false) {
                return null;
            }
            $this->setData('time_offset', $offset);
        }

        return (int) $offset;
    }

    /**
     * Save recipient id in session and see if we can at some point detect the timeoffset
     * using javascript
     *
     * @param string $recipientId
     *
     * @return $this
     */
    public function fetchTimeOffset($recipientId)
    {
        $offset = $this->getTimeOffset();
        if ($offset !== null) {
            Mage::getResourceSingleton('mzax_emarketing/recipient_event')->updateTimeoffset($offset, $recipientId);
            return $this;
        }

        $ids = $this->getData('time_offset_ids');
        if (!is_array($ids)) {
            $ids = array();
        }
        $ids[] = $recipientId;
        $this->setData('time_offset_ids', $ids);

        return $this;
    }
}
