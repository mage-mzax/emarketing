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
 * Filter for customer address attributes
 * 
 * @author Jacob Siefer
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Address_Attribute
    extends Mzax_Emarketing_Model_Object_Filter_Attribute
{
    
    /**
     * Entity type code
     * 
     * @var string
     */
    protected $_entity = 'customer_address';
    
    
    /**
     * Binding required
     * 
     * @var string
     */
    protected $_requireBinding = 'customer_address_id';

    
    
    public function getTitle()
    {
        return "Customer Adddress";
    }
    
    

}
