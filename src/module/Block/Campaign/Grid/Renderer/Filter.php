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


class Mzax_Emarketing_Block_Campaign_Grid_Renderer_Filter extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($row instanceof Mzax_Emarketing_Model_Campaign) {
            return $this->renderFilters($row);
        }
        return '';

    }



    public function renderFilters(Mzax_Emarketing_Model_Campaign $campagin)
    {
        $html = array();

        $columnFilter = $this->getColumn()->getFilter();
        if ( $columnFilter ) {
            $columnFilter = $columnFilter->getValue();
        }


        /* @var $filter Mzax_Emarketing_Model_Object_Filter_Abstract */
        foreach ($campagin->getFilters() as $filter) {
            $style = '';
            if ($filter->getType() == $columnFilter) {
                $style = 'background:#FFFFDD; color:#222';
            }

            $html[] = "<li style=\"{$style}\">{$filter->getQueryString()}</li>";
        }

        $html = implode("\n", $html);
        $html = "<ul style=\"list-style: inside disc; font-size: 0.8em; color: #666;\">$html</ul>";

        return $html;
    }



}
