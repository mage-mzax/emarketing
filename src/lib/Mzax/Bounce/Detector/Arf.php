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
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Bounce_Detector_Arf extends Mzax_Bounce_Detector_Abstract
{
    const REPORT = 'multipart/report';
    
    const FEEDBACK = 'feedback-report';
    
    
    const STATUS = '5.7.1';
    
    
    public function inspect(Mzax_Bounce_Message $message)
    {
        if($message->type !== self::REPORT) {
            return false;
        }
        if($message->getContentType('report-type') !== self::FEEDBACK) {
            return false;
        }
        
        $part = $message->getMimePart('message/feedback-report');
        if(!$part) {
            return false;
        }
        
        $report = $part->getDecodedHash();
        if(!is_array($report)) {
            return false;
        }
        
        $message->info(Mzax_Bounce::TYPE_ARF, true);
        $message->info('status', self::STATUS);
        $message->info('type',   Mzax_Bounce::TYPE_ARF);
        
        
        // abuse|froud|virus|other|not-spam
        if(isset($report['feedback-type'])) {
            $feedbackType = $report['feedback-type'];
            $message->info('feedback-type', $feedbackType);
        }
        if(isset($report['removal-recipient'])) {
            $recipient = $this->findEmail($report['removal-recipient']);
            $message->info('recipient', $recipient);
        }
        else if(isset($report['original-rcpt-to'])) {
            $recipient = $this->findEmail($report['original-rcpt-to']);
            $message->info('recipient', $recipient);
        }
        
        /*
         * If we still have no recipient, look for our
         * old orignal message and get it from the To header
         */
        if(!$message->info('recipient')) {
            // check for embedded rfc822 message
            if($rfc822 = $message->getMimePart('message/rfc822')) {
                $recipient = $this->findEmail($rfc822->getHeader('to'));
                $message->info('recipient', $recipient);
            }
        }
        
        
        return true;
    }
    
}