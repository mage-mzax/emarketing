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



/**
 * Recipient renderer
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Block_Grid_Column_Renderer_Recipient
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{


    /**
     * Renders grid column
     *
     * @param Varien_Object $row
     * @return mixed
     */
    public function _getValue(Varien_Object $row)
    {
        $value = parent::_getValue($row);
        $recipient = $row->getRecipient();

        if ($recipient instanceof Mzax_Emarketing_Model_Recipient)
        {
            $campaign = $recipient->getCampaign();
            $subject  = $campaign->getRecipientProvider()->getSubject();

            if ($subject) {
                $url = $subject->getAdminUrl($recipient->getObjectId());
                return sprintf('<a href="%s">%s</a>', $url, $value);
            }

        }
        return $value;
    }
}
