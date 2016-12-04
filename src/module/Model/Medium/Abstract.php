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
 *
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
abstract class Mzax_Emarketing_Model_Medium_Abstract
{
    /**
     * Unique Medium ID
     *
     * @return string
     */
    abstract public function getMediumId();


    /**
     * Send a single recipient
     *
     * @param Mzax_Emarketing_Model_Recipient $recipient
     */
    abstract public function sendRecipient(Mzax_Emarketing_Model_Recipient $recipient);



    public function prepareRecipientGrid(Mzax_Emarketing_Block_Campaign_Edit_Tab_Recipients_Grid $grid)
    {
    }



    public function prepareCampaignTabs(Mzax_Emarketing_Block_Campaign_Edit_Tabs $tabs)
    {
    }


    public function initSettingsForm(Varien_Data_Form $form, Mzax_Emarketing_Model_Campaign $campaign)
    {
    }

    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
    }



    /**
     *
     * @param Mzax_Emarketing_Model_Medium_Email_Snippets $snippets
     */
    public function prepareSnippets(Mzax_Emarketing_Model_Medium_Email_Snippets $snippets)
    {
    }






    /**
     * Send message to all pending recipients
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     * @param Varien_Object $options
     * @throws Exception
     * @return number
     */
    public function sendRecipients(Mzax_Emarketing_Model_Campaign $campaign, Varien_Object $options)
    {
        $recipients = $campaign->getPendingRecipients();
        $recipients->setPageSize($options->getMaximum());

        $start = time();

        $prepared = 0;
        $timeout = (int) $options->getTimeout();

        /* @var $recipient Mzax_Emarketing_Model_Recipient */
        foreach ($recipients as $recipient) {
            try {
                $this->sendRecipient($recipient);
                $prepared++;
            }
            catch(Exception $e) {
                $recipient->logException($e);
                if ($options->getBreakOnError()) {
                    throw $e;
                }
                Mage::logException($e);
            }
            $recipient->isPrepared(true);
            $recipient->save();

            if ($timeout && time()-$start > $timeout) {
                break;
            }
        }
        return $prepared;
    }


}
