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
 * Class Mzax_Bounce_Detector_Abstract
 */
abstract class Mzax_Bounce_Detector_Abstract
{
    const REGEX_EMAIL = '/(?:[a-z0-9_\-]+(?:\.[_a-z0-9\-]+)*@(?:[_a-z0-9\-]+\.)+(?:[a-z]+))/i';

    /**
     * @param Mzax_Bounce_Message $message
     *
     * @return bool
     */
    abstract public function inspect(Mzax_Bounce_Message $message);

    /**
     * Scan given text for an email address
     *
     * @param string $text
     *
     * @return string|null
     */
    public function findEmail($text)
    {
        if (preg_match(self::REGEX_EMAIL, $text, $matches)) {
            // ignore e.g. redacted@aol.com or redacted@comcaset.com
            if (stripos($matches[0], 'redacted@') !== 0) {
                return $matches[0];
            }
        }

        return null;
    }
}
