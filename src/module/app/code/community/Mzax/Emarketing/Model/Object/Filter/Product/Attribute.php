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
 * Class Mzax_Emarketing_Model_Object_Filter_Product_Attribute
 */
class Mzax_Emarketing_Model_Object_Filter_Product_Attribute
    extends Mzax_Emarketing_Model_Object_Filter_Attribute
{
    /**
     * Attribute data key that indicates whether it should be used for rules
     *
     * @var string
     */
    protected $_isUsedForRuleProperty = 'is_used_for_promo_rules';

    /**
     * @var string
     */
    protected $_entity = Mage_Catalog_Model_Product::ENTITY;

    /**
     * @var string
     */
    protected $_requireBinding = 'product_id';

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Product Attributes";
    }

    /**
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     *
     * @return bool
     */
    protected function isAttributeAllowed(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            if ($attribute->isAllowedForRuleCondition() && $attribute->getDataUsingMethod($this->_isUsedForRuleProperty)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getChooserUrl()
    {
        if ($this->getAttribute()->getAttributeCode() === 'sku') {
            return 'adminhtml/promo_widget/chooser/attribute/sku/form/filter_conditions_fieldset';
        }

        return null;
    }
}
