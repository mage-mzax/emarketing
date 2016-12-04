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
 * Class Mzax_Emarketing_Model_Inbox_Email_Collector
 */
class Mzax_Emarketing_Model_Inbox_Email_Collector
{
   /**
    * Retrieve puller
    *
    * @return Mzax_Emarketing_Model_Inbox_Email_Pull_Abstract
    */
    public function getPuller()
    {
        return Mage::getModel('mzax_emarketing/inbox_email_pull_storage');
    }

    /**
     * Collect new messages from inbox
     *
     * Returns the number of new messages found
     *
     * @return integer
     */
    public function collect()
    {
        $puller = $this->getPuller();
        $messages = $puller->pull($this);

        return $messages;
    }

    /**
     * Test current setup
     *
     * @return boolean
     */
    public function test()
    {
        $puller = $this->getPuller();
        if ($puller) {
            return $puller->test();
        }
        return false;
    }

    /**
     * Add email
     *
     * @param string $header
     * @param string $content
     *
     * @return $this
     */
    public function add($header, $content)
    {
        /* @var $resource Mzax_Emarketing_Model_Resource_Inbox_Email */
        $resource = Mage::getResourceModel('mzax_emarketing/inbox_email');
        $resource->insertEmail($header, $content);

        return $this;
    }
}
