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
 * Class Mzax_Emarketing_Model_System_Config_Source_Trackers
 */
class Mzax_Emarketing_Model_System_Config_Source_Trackers
{
    /**
     * @var array[]
     */
    protected $_options;

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $collection = $this->getCollection();
            $this->_options = $collection->toOptionArray();
        }

        return $this->_options;
    }

    /**
     * Retrieve tracker collection
     *
     * @return Mzax_Emarketing_Model_Resource_Conversion_Tracker_Collection
     */
    protected function getCollection()
    {
        return Mage::getResourceModel('mzax_emarketing/conversion_tracker_collection');
    }
}
