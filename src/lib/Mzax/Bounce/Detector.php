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
class Mzax_Bounce_Detector extends Mzax_Bounce_Detector_Abstract
{
    
    protected $_detectors = array();
    
    
    
    public function __construct()
    {
        $this->_detectors[] = new Mzax_Bounce_Detector_Arf;
        $this->_detectors[] = new Mzax_Bounce_Detector_Rfc1892;
        $this->_detectors[] = new Mzax_Bounce_Detector_Failure;
        $this->_detectors[] = new Mzax_Bounce_Detector_Extended;
        $this->_detectors[] = new Mzax_Bounce_Detector_Autoreply;
    }
    
    
    public function addDetector(Mzax_Bounce_Detector_Abstract $detector, $offset = null)
    {
        if($offset === null) {
            $this->_detectors[] = $detector;
        }
        else {
            $offset = abs($offset);
            $offset = min($offset, count($this->_detectors));
            array_splice($this->_detectors, $offset, 0, array($detector));
        }
        return $this;
    }
    
    
    public function inspect(Mzax_Bounce_Message $message)
    {
        /* @var $detector Mzax_Bounce_Detector_Abstract */
        foreach($this->_detectors as $detector) {
            if($detector->inspect($message) === true) {
                break; 
            }
        }
        
        if(!$message->info('recipient')) {
            if($email = $this->getFromEmail($message)) {
                $message->info('recipient', $email);
            }
        }
        
        
        if(!$message->info('sent_at')) {
            foreach(array('delivery-date', 'date') as $header) {
                if( $date = $message->getHeader($header)) {
                    if($date = strtotime($date)) {
                        $message->info('sent_at', date('Y-m-d H:i:s', $date));
                        break;
                    }
                }
            }
        }
        
        return $message->info();
        
    }
    
    public function getFromEmail(Mzax_Bounce_Message $message)
    {
        $email = $this->findEmail($message->getFrom());
        
        // ingore certain non-user emails
        $skip = array('abuse', 'scomp', 'feedbackloop');
        foreach($skip as $check) {
            if(strpos($email, $check) !== false) {
                return false;
            }
        }
        return $email;
        
        
    }
    
    
    
    
}