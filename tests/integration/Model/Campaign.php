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

use Mzax_Emarketing_Model_Recipient as Recipient;
use Mzax_Emarketing_Model_Campaign as Campaign;


/**
 * Class Mzax_Emarketing_Test_Model_Campaign
 */
class Mzax_Emarketing_Test_Model_Campaign
    extends Mzax_Emarketing_Test_Case_Abstract
{
    /**
     * Send recipients and make sure and outbox email gets created.
     *
     * @return void
     * @test
     *
     * @loadFixture customers
     * @loadFixture campaigns
     * @loadFixture recipients
     */
    public function testSendRecipients()
    {
        $options = ['break_on_error' => false];

        $campaign = new Campaign();
        $campaign->load(1);
        $this->prepareSampleCampaign($campaign);

        $result = $campaign->sendRecipients($options);
        $this->assertEquals(1, $result);

        $recipient = new Recipient();
        $recipient->load(1);
        $this->assertNotNull($recipient->getPreparedAt());

        $email = new Mzax_Emarketing_Model_Outbox_Email();
        $email->load(1, 'recipient_id');

        $this->assertEquals($campaign->getId(), $email->getCampaignId());
        $this->assertStringStartsWith('I am a short', $email->getBodyText());

        $email->delete();
        Mage::app()->cleanCache([Mzax_Emarketing_Model_Campaign::CACHE_TAG]);
    }

    /**
     * Send recipient with a failure and make sure an error gets reported
     *
     * @return void
     * @test
     *
     * @loadFixture customers
     * @loadFixture campaigns
     * @loadFixture recipients
     */
    public function testSendRecipientsWithFailure()
    {
        $options = ['break_on_error' => false];

        $campaign = new Campaign();
        $campaign->load(1);

        $result = $campaign->sendRecipients($options);
        $this->assertEquals(0, $result);

        $recipient = new Recipient();
        $recipient->load(1);
        $this->assertNotNull($recipient->getPreparedAt());

        $error = new Mzax_Emarketing_Model_Recipient_Error();
        $error->load(1, 'recipient_id');

        $this->assertEquals($campaign->getId(), $error->getCampaignId());

        $error->delete();
        Mage::app()->cleanCache([Mzax_Emarketing_Model_Campaign::CACHE_TAG]);
    }
}
