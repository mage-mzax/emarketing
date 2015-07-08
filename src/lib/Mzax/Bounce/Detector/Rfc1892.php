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
class Mzax_Bounce_Detector_Rfc1892 extends Mzax_Bounce_Detector_Abstract
{
    
    
    const REPORT = 'multipart/report';
    
    const DELIVERY_STATUS = 'delivery-status';
    
    
    /**
     * @see http://www.iana.org/assignments/smtp-enhanced-status-codes/smtp-enhanced-status-codes.xhtml
     * @var string
     */
    const STATUS_REGEX = '/([245]\.[0-7]\.[0-9]{1,3})/';
    
    
    const DEFAULT_STATUS = '5.0.0';
    
    

    public function inspect(Mzax_Bounce_Message $message)
    {
        if($message->type !== self::REPORT) {
            return false;
        }
        if($message->getContentType('report-type') !== self::DELIVERY_STATUS) {
            return false;
        }
        
        $part = $message->getMimePart('message/delivery-status');
        if(!$part) {
            return false;
        }
        
        $status = $part->getDecodedHash();
        if(!is_array($status)) {
            return false;
        }
        
        $message->info('rfc1892', true);
        $message->info('type', Mzax_Bounce::TYPE_BOUNCE);
        
        
        
        // @see https://ohse.de/uwe/rfc/rfc1894.html#2.3.4
        if(isset($status['status'])) {
            if(preg_match(self::STATUS_REGEX, $status['status'], $matches)) {
                $message->info('status', $matches[1]);
            }
            else {
                $message->info('status', $status['status']);
            }
        }
        else {
            $message->info('status', self::DEFAULT_STATUS);
        }
        
        
        // @see https://ohse.de/uwe/rfc/rfc1894.html#2.3.1
        if(isset($status['original-recipient'])) {
            // address-type ; generic-address
            $recipient = $this->findEmail($status['original-recipient']);
            $message->info('recipient', $recipient);
        }
        // @see https://ohse.de/uwe/rfc/rfc1894.html#2.3.2
        else if(isset($status['final-recipient'])) {
            // address-type ; generic-address
            $recipient = $this->findEmail($status['final-recipient']);
            $message->info('recipient', $recipient);
        }
        // @see https://ohse.de/uwe/rfc/rfc1894.html#2.2.1
        if(isset($status['original-envelope-id'])) {
            $envelopeId = $status['original-envelope-id'];
            $message->info('envelope_id', $envelopeId);
        }
        
        
    }

}