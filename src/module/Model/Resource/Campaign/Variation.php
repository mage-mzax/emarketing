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
 * Class Mzax_Emarketing_Model_Resource_Campaign_Variation
 */
class Mzax_Emarketing_Model_Resource_Campaign_Variation extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/campaign_variation', 'variation_id');
    }

    /**
     * Prepare data for save
     *
     * @param   Mage_Core_Model_Abstract $object
     * @return  array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt(now());
        }
        $object->setUpdatedAt(now());
        $data = parent::_prepareDataForSave($object);

        return $data;
    }

}
