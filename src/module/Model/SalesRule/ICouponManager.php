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
 * Emarketing Sales Rule Condition
 * 
 * 
 * @method string getUnit()
 * @method string getCampaign()
 * @method string getValue()
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
interface Mzax_Emarketing_Model_SalesRule_ICouponManager
{
    
    
    public function addCoupon(Mage_SalesRule_Model_Coupon $coupon);
    
    public function getCoupons();
    
    
}
