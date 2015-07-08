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
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Attribute
    extends Mzax_Emarketing_Model_Object_Filter_Attribute
{
    

    protected $_entity = 'customer';
    
    
    protected $_requireBinding = 'customer_id';

    
    
    public function getTitle()
    {
        return "Customer Attributes";
    }
    
    
    
    protected function isAttributeAllowed(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if($attribute->getFrontendLabel()) {
            return true;
        }
        return false;
    }
    
    

}
