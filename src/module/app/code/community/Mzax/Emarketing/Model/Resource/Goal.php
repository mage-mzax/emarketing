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
 * Class Mzax_Emarketing_Model_Resource_Goal
 */
class Mzax_Emarketing_Model_Resource_Goal extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initiate resources
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/goal', 'goal_id');
    }

    /**
     * Inserts the goal table multiply rows with specified data.
     *
     * @param array $data Column-value pairs or array of Column-value pairs.
     *
     * @return int
     */
    public function insertMultiple($data)
    {
        return $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $data);
    }
}
