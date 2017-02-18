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
 * Class Mzax_Mail_Transport_Mock
 */
class Mzax_Mail_Transport_Mock extends Zend_Mail_Transport_Abstract
{
    /**
     * EOL character string used by transport
     * @var string
     * @access public
     */
    public $EOL = "\r\n";

    /**
     * Ignore don't send anything
     *
     * @return void
     */
    public function _sendMail()
    {
        // do nothing
    }

    /**
     * Retrieve size of email
     *
     * @return int
     */
    public function getSize()
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($this->getRawData(), $this->_mail->getCharset());
        }
        return strlen($this->getRawData());
    }

    /**
     * Retrieve raw data
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->header . $this->EOL . $this->body;
    }
}
