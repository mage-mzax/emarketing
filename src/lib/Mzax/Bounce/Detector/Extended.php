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
 *
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Bounce_Detector_Extended extends Mzax_Bounce_Detector_Abstract
{


    public function inspect(Mzax_Bounce_Message $message)
    {
        // Check for Hotmail Abuse Feedback Message
        if($recipient = $message->getHeader('X-HmXmrOriginalRecipient')) {
            $message->info('feedback-type', 'abuse');
            $message->info('status', Mzax_Bounce_Detector_Arf::STATUS);
            $message->info('type',   Mzax_Bounce::TYPE_ARF);
            $message->info('recipient', $recipient, 10);
            $message->info('hotmail_fbl', true);
            return;
        }


        // Check for Hotmail Abuse Feedback Message in embedded message
        if($rfc822 = $message->getMimePart('message/rfc822')) {
            $hash = $rfc822->getDecodedHash();
            if(isset($hash['x-hmxmroriginalrecipient'])) {
                $recipient = $hash['x-hmxmroriginalrecipient'];
                $message->info('feedback-type', 'abuse');
                $message->info('status', Mzax_Bounce_Detector_Arf::STATUS);
                $message->info('type',   Mzax_Bounce::TYPE_ARF);
                $message->info('recipient', $recipient, 10);
                $message->info('hotmail_fbl', $rfc822);
            }
        }

    }




}
