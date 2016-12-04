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


class Mzax_Emarketing_Block_Campaign_New_Presets extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset
{

    /**
     *
     * @var Mzax_Emarketing_Model_Resource_Campaign_Preset_Collection
     */
    protected $_presets;


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('mzax/emarketing/campaign/presets.phtml');
    }



    /**
     * Retrieve presets
     *
     * @return Mzax_Emarketing_Model_Resource_Campaign_Preset_Collection
     */
    public function getPresets()
    {
        if (!$this->_presets) {
            $this->_presets = Mage::getResourceModel('mzax_emarketing/campaign_preset_collection');
        }
        return $this->_presets;
    }

}
