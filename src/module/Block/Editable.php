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
 *
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Block_Editable extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->addClass('element-value-changer');

        $valueName = $element->getValueName();
        if ($valueName==='') {
            $valueName = '...';
        }

        $valueLabel = htmlspecialchars(Mage::helper('core/string')->truncate($valueName, 150, '...'));

        switch($this->getFormat())
        {
            case 'text':
                $html = $valueLabel;
                break;

            case 'html':
                $html = '<strong>' . $valueLabel . '</strong>' ;
                break;

            default:
                if ($element->getIsMeta()) {
                    $html = '<input type="hidden" class="hidden" id="'.$element->getHtmlId().'" name="'.$element->getName().'" value="'.$element->getValue().'"/>';
                    $html.= htmlspecialchars($valueName);
                } else {
                    $html = '<span class="rule-param"' . ($element->getParamId() ? ' id="' . $element->getParamId() . '"' : '') . '>';
                    $html.= '<a href="javascript:void(0)" class="label">' . $valueLabel . '</a>';
                    $html.= '<span class="element">';
                    $html.= $element->getElementHtml();
                    if ($element->getExplicitApply()) {
                        $html.= '<a href="javascript:void(0)" class="rule-param-apply"><img src="'.$this->getSkinUrl('images/rule_component_apply.gif').'" class="v-middle" alt="'.$this->__('Apply').'" title="'.$this->__('Apply').'" /></a>';
                    }
                    $html.= '</span></span>';
                }
        }
        return $html;
    }
}
