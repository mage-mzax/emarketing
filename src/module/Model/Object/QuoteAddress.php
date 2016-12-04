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
 * Class Mzax_Emarketing_Model_Object_QuoteAddress
 */
class Mzax_Emarketing_Model_Object_QuoteAddress extends Mzax_Emarketing_Model_Object_Address
{


    public function _construct()
    {
        $this->_init('sales/quote_address');
    }



    public function getName()
    {
        return $this->__('Quote Address');
    }


    public function getAdminUrl($id)
    {
        return null;
    }


    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('quote_id', 'parent_id');

        return $query;
    }


}
