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


class Mzax_Emarketing_Model_Form_Element_Credits extends Varien_Data_Form_Element_Abstract
{
    
    public function getElementHtml()
    {
        $credits = $this->getEscapedValue();
        $credits = preg_replace('/\[(.*?)\|(.*?)\]/', '<a href="$2" target="_blank">$1</a>', $credits);
        
        $credits = nl2br($credits);
        
        return '<div class="mzax-credits">' . $credits . '</div>';
    }
    
}
