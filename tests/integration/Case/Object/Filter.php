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


use Mzax_Emarketing_Model_Object_Filter_Abstract as ObjectFilter;
use Mzax_Emarketing_Model_Recipient_Provider_Abstract as RecipientProvider;



/**
 * Class Mzax_Emarketing_Test_Case_Object_Filter
 *
 * Test case for running filters
 */
abstract class Mzax_Emarketing_Test_Case_Object_Filter
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Run filter on customer provider
     *
     * @param ObjectFilter $filter
     *
     * @return int[]
     */
    protected function runCustomerFilter(ObjectFilter $filter)
    {
        $provider = new Mzax_Emarketing_Model_Recipient_Provider_Customer();
        $provider->addFilter($filter);

        return $this->fetchRecipientIds($provider);
    }

    /**
     * Fetch recipient object ids for a given recipient provider
     *
     * @param RecipientProvider $provider
     *
     * @return int[]
     */
    protected function fetchRecipientIds(RecipientProvider $provider)
    {
        $adapter = $this->getResourceHelper()->getReadAdapter();
        $select = $provider->getSelect();

        $result = $adapter->fetchCol($select);

        return $result;
    }

    /**
     * Retrieve resource helper
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }
}
