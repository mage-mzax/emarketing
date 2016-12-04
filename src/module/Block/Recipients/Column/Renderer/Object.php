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


class Mzax_Emarketing_Block_Recipients_Column_Renderer_Object extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    /**
     * Retrieve current object
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getObject()
    {
        return $this->getColumn()->getObject();
    }




    public function render(Varien_Object $row)
    {
        $idField = $this->getColumn()->getIdField();
        if (!$idField) {
            $idField = $row->getIdFieldName();
        }

        $labelField = $this->getColumn()->getLabelField();
        if ($labelField) {
            $label = $row->getData($labelField);
        }
        else {
            $label = $this->getObject()->getRowId($row);
        }


        $url = $this->getObject()->getAdminUrl($row->getData($idField));
        if ($url) {
            return "<a href=\"{$url}\" target=\"_blank\">$label</a>";
        }
        return $label;
    }
}
