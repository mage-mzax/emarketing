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
 * Campaign Preset Collection
 *
 * @method Mzax_Emarketing_Model_Campaign_Preset getNewEmptyItem()
 */
class Mzax_Emarketing_Model_Resource_Campaign_Preset_Collection extends Varien_Data_Collection
{
    /**
     * Mzax_Emarketing_Model_Resource_Campaign_Preset_Collection constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setItemObjectClass('mzax_emarketing/campaign_preset');
    }

    /**
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        $files = $this->_getResource()->getAllPresetFiles();
        foreach ($files as $file) {
            $item = $this->getNewEmptyItem();
            $item->loadByFile($file);
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Retrieve resource model
     *
     * @return Mzax_Emarketing_Model_Resource_Campaign_Preset
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('mzax_emarketing/campaign_preset');
    }
}
