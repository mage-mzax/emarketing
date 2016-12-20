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
 * Class Mzax_Emarketing_Helper_Newsletter
 */
class Mzax_Emarketing_Helper_Newsletter extends Mage_Core_Helper_Abstract
{
    /**
     * Unsubscribe email from newsletter
     *
     * @param string $email
     * @param integer $storeId
     * @param boolean $sendUnSubscriptionEmail
     *
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function unsubscribe($email, $storeId, $sendUnSubscriptionEmail = false)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = Mage::getModel('newsletter/subscriber');
        $subscriber->setStoreId($storeId);
        $subscriber->loadByEmail($email);

        if (!$subscriber->getId()) {
            $subscriber->setEmail($email);
            $subscriber->setSubscriberConfirmCode($subscriber->randomSequence());
        }

        if ($subscriber->getStatus() != $subscriber::STATUS_UNSUBSCRIBED) {
            $subscriber->setIsStatusChanged(true);
            $subscriber->setChangeStatusAt(now());
            $subscriber->setSubscriberStatus($subscriber::STATUS_UNSUBSCRIBED)->save();

            if ($sendUnSubscriptionEmail) {
                $subscriber->sendUnsubscriptionEmail();
            }
        }

        return $subscriber;
    }
}
