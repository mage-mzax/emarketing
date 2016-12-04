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
 * Renderer for campaigns
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Block_Grid_Column_Renderer_Campaign
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{


    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);

        $id = $row->getCampaignId();
        if ($id) {
            $url = $this->getUrl('*/campaign/edit', array('id' => $id));
            return sprintf('<a href="%s">%s</a>', $url, $html);
        }
        return $html;
    }




}
