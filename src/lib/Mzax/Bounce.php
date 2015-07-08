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
class Mzax_Bounce
{

    const TYPE_ARF         = 'arf';
    const TYPE_BOUNCE      = 'bounce';
    const TYPE_AUTOREPLY   = 'autoreply';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';
    
    
    
    /**
     * 
     * @var Mzax_Bounce_Detector
     */
    private static $_detector;
    
    public static function detect($message, &$info = null)
    {
        if(!self::$_detector) {
            self::$_detector = new Mzax_Bounce_Detector;
        }
        
        if(!$message instanceof Mzax_Bounce_Message) {
            $message = new Mzax_Bounce_Message($message);
        }
        
        $info = self::$_detector->inspect($message);
        return $message;
        
    }
    
    
}