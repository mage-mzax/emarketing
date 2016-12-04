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
 * Detector for store id
 *
 * if no recipient was found we may can still find the right
 * store id by using the To email address
 */
class Mzax_Emarketing_Model_Inbox_Bounce_Detector_Store
    extends Mzax_Emarketing_Model_Inbox_Bounce_Detector_Abstract
{
    /**
     * Try to detect the original sender store id by email
     *
     * @see Mzax_Bounce_Detector_Abstract::inspect()
     *
     * @return void
     */
    public function inspect(Mzax_Bounce_Message $message)
    {
        $to = strtolower($this->findEmail($message->getHeader('to')));

        if ($store = $this->getStoreByEmail($to)) {
            $message->info('store_id', $store->getId());
        }
    }

    /**
     * Try to retrieve store from email
     *
     * @param string $email
     *
     * @return Mage_Core_Model_Store|null
     */
    public function getStoreByEmail($email)
    {
        $email = strtolower($email);
        /* @var $store Mage_Core_Model_Store */

        /*
         * try to retrieve store by using the email
         */
        foreach (Mage::app()->getStores() as $store) {
            $config = $store->getConfig('trans_email');
            foreach ($config as $key => $data) {
                if (isset($data['email']) && strtolower($data['email']) === $email) {
                    return $store;
                }
            }
        }

        /*
         * try to retrieve the store by using the domain
         */
        $domain = trim(strstr($email, '@'), '@');
        foreach (Mage::app()->getStores() as $store) {
            $baseUrl = strtolower($store->getBaseUrl($store::URL_TYPE_WEB));
            $baseUrl = preg_replace('/^(?:https?:\/\/)?(?:www\.)?([^\/]+)(.*)$/i', '$1', $baseUrl);

            if ($baseUrl === $domain) {
                return $store;
            }
        }

        return null;
    }
}
