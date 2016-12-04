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
 * Class Mzax_Emarketing_Model_Newsletter_List_Subscriber
 *
 * @method string getListId()
 * @method string getSubscriberId()
 * @method string getChangedAt()
 * @method string getListStatus()
 */
class Mzax_Emarketing_Model_Newsletter_List_Subscriber
    extends Varien_Object
{
    /**
     * Retrieve list resource model
     *
     * @return Mzax_Emarketing_Model_Resource_Newsletter_List
     */
    protected function getResource()
    {
        return Mage::getResourceSingleton('mzax_emarketing/newsletter_list');
    }

    /**
     * Retrieve list resource model
     *
     * @return Mzax_Emarketing_Model_Resource_Newsletter_List
     * @deprecated Typo
     */
    protected function getResouce()
    {
        return $this->getResource();
    }
}
