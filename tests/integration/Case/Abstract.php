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


use Mzax_Emarketing_Model_Campaign_Preset as CampaignPreset;
use Mzax_Emarketing_Model_Recipient_Provider_Customer as RecipientProviderCustomer;
use Mzax_Emarketing_Model_Medium_Email as EmailMedium;
use Mzax_Emarketing_Model_Template as EmailTemplate;


/**
 * Class Mzax_Emarketing_Test_Case_Object_Filter
 *
 * Test case for running filters
 */
abstract class Mzax_Emarketing_Test_Case_Abstract
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return void
     */
    protected function prepareSampleCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $preset = new CampaignPreset();
        $preset->loadByFile(__DIR__ . '/../_data/campaign.json');

        $campaign->addData($preset->getData());

        $template = new EmailTemplate();
        $template->setBody(file_get_contents(__DIR__ . '/../_data/template.html'));

        $mediumData = $campaign->getMediumData();
        $mediumData->setData('template', $template);
    }
}
