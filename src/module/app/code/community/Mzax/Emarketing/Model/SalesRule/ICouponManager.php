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
 * Interface Mzax_Emarketing_Model_SalesRule_ICouponManager
 */
interface Mzax_Emarketing_Model_SalesRule_ICouponManager
{
    /**
     * Add coupon instance
     *
     * @param Mage_SalesRule_Model_Coupon $coupon
     *
     * @return mixed
     */
    public function addCoupon(Mage_SalesRule_Model_Coupon $coupon);

    /**
     * Retrieve all coupon instances
     *
     * @return Mage_SalesRule_Model_Coupon[]
     */
    public function getCoupons();
}
