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
 * Class Mzax_Emarketing_Block_Recipients_Column_Renderer_Attribute
 */
class Mzax_Emarketing_Block_Recipients_Column_Renderer_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Retrieve attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        return $this->getColumn()->getAttribute();
    }

    /**
     * @param Varien_Object $row
     *
     * @return mixed|string
     */
    public function render(Varien_Object $row)
    {
        $attribute = $this->getAttribute();

        $data = $row->getData($attribute->getAttributeCode());

        if ($attribute->getFrontendInput() === 'multiselect') {
            $searchValue = (array) $this->getColumn()->getSearchValue();

            $result = array();
            foreach (explode(',', $data) as $value) {
                $valueText = $attribute->getSource()->getOptionText($value);
                if (in_array($value, $searchValue)) {
                    $valueText = "<strong>$valueText</strong>";
                }
                $result[] = $valueText;
            }

            $data = implode(', ', $result);
        }

        return $data;
    }
}
