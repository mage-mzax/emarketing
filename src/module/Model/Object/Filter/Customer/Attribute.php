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
 * Class Mzax_Emarketing_Model_Object_Filter_Customer_Attribute
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Attribute
    extends Mzax_Emarketing_Model_Object_Filter_Attribute
{
    /**
     * @var string
     */
    protected $_entity = 'customer';

    /**
     * @var string
     */
    protected $_requireBinding = 'customer_id';

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Customer Attributes";
    }

    /**
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     *
     * @return bool
     */
    protected function isAttributeAllowed(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if ($attribute->getFrontendLabel()) {
            return true;
        }

        return false;
    }
}
