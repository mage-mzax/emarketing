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
 * Class Mzax_Emarketing_BeaconController
 */
class Mzax_Emarketing_BeaconController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return void
     */
    public function imageAction()
    {
        $hash = $this->getRequest()->getParam('hash');

        /* @var $recipient Mzax_Emarketing_Model_Recipient */
        $recipient = Mage::getModel('mzax_emarketing/recipient')->loadByBeacon($hash);

        $response = $this->getResponse();


        if (!$recipient->getId()) {
            $response->setHttpResponseCode(404);
        } else {
            $recipient->captureView($this->getRequest());

            if (!$recipient->getViewedAt()) {
                $recipient->setViewedAt(now());
                $recipient->save();
            }
        }

        // 125x50 blank gif
        $gif = '4749463839617D003200800000FFFFFF'.
               '00000021F90401000000002C00000000'.
               '7D003200000257848FA9CBED0FA39CB4'.
               'DA8BB3DEBCFB0F86E24896E689A6EACA'.
               'B6EE0BC7F24CD7F68DE7FACEF7FE0F0C'.
               '0A87C4A2F1884C2A97CCA6F3098D4AA7'.
               'D4AAF58ACD6AB7DCAEF70B0E8BC7E4B2'.
               'F98C4EABD7ECB6FB0D8FCBE7A602003B';



        $binary = '';
        foreach (str_split($gif, 2) as $byte) {
            $binary .= chr(hexdec($byte));
        }

        $response->setHeader('Content-Type', 'image/gif');
        $response->setHeader('Content-Length', strlen($binary));
        $response->setHeader('Expires', 'Wed, 11 Nov 2200 11:11:11 GMT');
        $response->setHeader('Cache-Control', 'private, max-age=31536000');
        $response->setBody($binary);
    }

    /**
     * Retrieve session model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mzax_emarketing/session');
    }
}
