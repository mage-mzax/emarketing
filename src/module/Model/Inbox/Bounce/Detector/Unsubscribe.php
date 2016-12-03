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
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Inbox_Bounce_Detector_Unsubscribe
    extends Mzax_Emarketing_Model_Inbox_Bounce_Detector_Abstract
{


    /**
     *
     * (non-PHPdoc)
     * @see Mzax_Bounce_Detector_Abstract::inspect()
     */
    public function inspect(Mzax_Bounce_Message $message)
    {
        $subject = urldecode(trim($message->getSubject()));

        if (preg_match('/^Unsubscribe ([^\s]+) \(([0-9A-Z]+)\)$/i', $subject, $matches)) {
            $email = $matches[1];
            $hash  = $matches[2];

            /* @var $recipient Mzax_Emarketing_Model_Recipient */
            $recipient = Mage::getModel('mzax_emarketing/recipient')->loadByBeacon($hash);
            if ($recipient->getId()) {
                $recipient->prepare();
                if (strtolower($recipient->getAddress()) == strtolower($email)) {
                    $message->info('recipient_id', $recipient->getId(), 200);
                    $message->info('campaign_id',  $recipient->getCampaignId(), 200);
                    $message->info('recipient',    $email, 200);
                    $message->info(Mzax_Bounce::TYPE_UNSUBSCRIBE, true);
                    $message->info('type', Mzax_Bounce::TYPE_UNSUBSCRIBE);

                    $storeId = Mage::getResourceSingleton('mzax_emarketing/recipient')->getStoreId($recipient->getId());
                    if ($storeId) {
                        $message->info('store_id', $storeId, 100);
                    }

                    return true; // stop
                }
            }

        }
    }



}
