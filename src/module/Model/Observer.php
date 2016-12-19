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
 * Class Mzax_Emarketing_Model_Observer
 */
class Mzax_Emarketing_Model_Observer extends Mzax_Emarketing_Model_Observer_Abstract
{
    /**
     * If required we want to get the users timezone offset
     *
     * We will inject a tiny javascript code to the bottom of the page
     * which will set a cookie which we then can use to determin the exact
     * time zone.
     *
     * @see Mage_Core_Controller_Varien_Action::renderLayout()
     * @event controller_action_layout_generate_blocks_after
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function injectTimeOffsetJs(Varien_Event_Observer $observer)
    {
        if (!$this->_config->get('mzax_emarketing/tracking/inject_timeoffset_js')) {
            return;
        }

        if (!$this->getSession()->requireTimeOffset()) {
            return;
        }

        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getData('layout');

        $script = $this->getTimeOffsetScript();

        /* @var $block Mage_Core_Block_Text_List */
        $block = $layout->getBlock('before_body_end');
        if ($block && $script) {
            /* @var $script Mage_Core_Block_Text_Tag_Js */
            $jsTag = $layout->createBlock('core/text_tag_js', 'time_offset_js');
            $jsTag->setContents($script);

            $block->append($jsTag);
        }
    }

    /**
     * Retrieve script for setting time offset cookie
     *
     * @return string
     */
    public function getTimeOffsetScript()
    {
        $cookie = Mzax_Emarketing_Model_Session::TIME_OFFSET_COOKIE;

        return "(Mage && Mage.Cookies && Mage.Cookies.set('$cookie', (new Date).getTimezoneOffset()));";
    }
}
