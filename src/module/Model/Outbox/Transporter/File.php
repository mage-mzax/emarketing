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
 * Simple transporter for testing
 *
 * emails will be saved to ./var/mzax_emails/...
 */
class Mzax_Emarketing_Model_Outbox_Transporter_File
    extends Mzax_Mail_Transport_File
    implements Mzax_Emarketing_Model_Outbox_Transporter_Interface
{
    /**
     * @param Mzax_Emarketing_Model_Outbox_Email $email
     */
    public function setup(Mzax_Emarketing_Model_Outbox_Email $email)
    {
        $path[] = Mage::getBaseDir('var');
        $path[] = 'mzax_emails' ;
        $path[] = 'campaign_' . $email->getCampaignId();
        $path[] = 'mail.txt';

        $this->setFile(implode(DS, $path));
        $this->setSaveHtml(true);
    }
}
