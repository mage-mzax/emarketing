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
 * Works like multislect but one can also select all options
 * like a wildcard
 */
class Mzax_Emarketing_Model_Form_Element_Wildselect
    extends Varien_Data_Form_Element_Multiselect
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        $disabled = false;
        if ($this->getValue() === $this->getDataSetDefault('wildcard', '*')) {
            $this->setData('disabled', 'disabled');
            $disabled = true;
        }
        $html = parent::getElementHtml();
        $htmlId = 'wildcard_' . $this->getHtmlId();
        $html .= '<input id="'.$htmlId.'" name="wildcard[]" value="' . $this->getId() . '"';
        $html .= ($disabled ? ' checked="checked"' : '');

        if ($this->getReadonly()) {
            $html .= ' disabled="disabled"';
        }

        $wildcardLabel = $this->getDataSetDefault('wildcard_label', Mage::helper('mzax_emarketing')->__('Use All'));

        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox" />';

        $html .= ' <label for="'.$htmlId.'" class="normal">' . $wildcardLabel . '</label>';
        $html .= '<script type="text/javascript">toggleValueElements($(\''.$htmlId.'\'), $(\''.$htmlId.'\').parentNode);</script>';

        return $html;
    }
}
