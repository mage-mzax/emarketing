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
 * Class Mzax_Emarketing_Model_Resource_Inbox_Email_Collection
 */
class Mzax_Emarketing_Model_Resource_Inbox_Email_Collection extends Mzax_Emarketing_Model_Resource_Email_Collection
{
    /**
     * Resource Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/inbox_email');
    }
}
