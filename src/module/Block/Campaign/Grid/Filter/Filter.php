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


class Mzax_Emarketing_Block_Campaign_Grid_Filter_Filter extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{


    protected function _getOptions()
    {
        return Mage::getModel('mzax_emarketing/object_filter')->getAllOptions();
    }


    /**
     * Retrieve condition
     *
     * @return array
     */
    public function getCondition()
    {
        $id = $this->getValue();
        $length = strlen($id);

        $search = "{s:4:\"type\";s:{$length}:\"{$id}\"";

        $helper = Mage::getResourceHelper('core');
        $likeExpression = $helper->addLikeEscape($search, array('position' => 'any'));
        return array('like' => $likeExpression);
    }




}
