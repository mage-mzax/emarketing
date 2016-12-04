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
 * Class Mzax_Emarketing_Model_Resource_Template_Collection
 */
class Mzax_Emarketing_Model_Resource_Template_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/template');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('template_id', 'name');
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('template_id', 'name');
    }
}
