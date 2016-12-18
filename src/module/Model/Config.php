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


use Mage_Core_Model_Store as Store;


/**
 * Class Mzax_Emarketing_Model_Config
 *
 * Config manager to minimize the usage of super class Mage
 */
class Mzax_Emarketing_Model_Config
{
    /**
     * @param string $path
     * @param int|Store $store
     *
     * @return string
     */
    public function get($path, $store = null)
    {
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * @param string $path
     * @param int|Store $store
     *
     * @return bool
     */
    public function flag($path, $store = null)
    {
        return Mage::getStoreConfigFlag($path, $store);
    }
}
