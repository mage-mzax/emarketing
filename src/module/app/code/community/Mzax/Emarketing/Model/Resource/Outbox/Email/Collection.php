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
 * Class Mzax_Emarketing_Model_Resource_Outbox_Email_Collection
 */
class Mzax_Emarketing_Model_Resource_Outbox_Email_Collection
    extends Mzax_Emarketing_Model_Resource_Email_Collection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/outbox_email');
    }

    /**
     * Filter emails that can only be send out at certain
     * days of the week or hours of the day
     *
     * @param integer $now
     * @return Mzax_Emarketing_Model_Resource_Outbox_Email_Collection
     */
    public function addTimeFilter($now = null)
    {
        if (!$now) {
            $now = time();
        }

        // use admin store time
        $now -= (int) Mage::app()->getLocale()->storeDate(Mage_Core_Model_App::ADMIN_STORE_ID)->getGmtOffset();

        $this->addFieldToFilter('day_filter', array(array('finset' => date('w', $now)), array('eq' => '')));
        $this->addFieldToFilter('time_filter', array(array('finset' => date('G', $now)), array('eq' => '')));
        return $this;
    }
}
