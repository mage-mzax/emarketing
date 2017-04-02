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
use Mzax_Emarketing_Model_Recipient as Recipient;
use Mzax_Emarketing_Model_Medium_Email_Composer as EmailComposer;
use Mzax_Emarketing_Helper_Data as DataHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;



/**
 * Class Mzax_Emarketing_Test_Model_Medium_Email_Composer
 *
 * @covers Mzax_Emarketing_Model_Medium_Email_Composer
 */
class Mzax_Emarketing_Test_Model_Medium_Email_Composer
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    private $campaign;

    /**
     * Setup test campaign
     *
     * @return void
     */
    public function setUp()
    {
        $this->mockRandomHelper();

        $provider = new RecipientProviderCustomer();
        $medium = new EmailMedium();

        $preset = new CampaignPreset();
        $preset->loadByFile(__DIR__ . '/_data/campaign.json');

        // prepare test campaign
        $this->campaign = $preset->makeCampaign();
        $this->campaign->setMedium($medium);
        $this->campaign->setRecipientProvider($provider);
        $this->campaign->setStoreId(1);

        // assign test template
        $template = new EmailTemplate();
        $template->setBody(file_get_contents(__DIR__ . '/_data/template.html'));

        $mediumData = $this->campaign->getMediumData();
        $mediumData->setData('template', $template);
    }

    /**
     * Compose simple email
     *
     * @return void
     * @test
     */
    public function testComposeEmail()
    {
        $recipient = new Recipient();
        $recipient->setCampaign($this->campaign);
        $recipient->setObjectId(1);
        $recipient->setData('firstname', "John");

        $composer = new EmailComposer();
        $composer->setRecipient($recipient);
        $composer->compose(false);

        $this->assertComposedEmail('simple', $composer);
    }

    /**
     * Test composing using pre-rendering template option
     *
     * @return void
     * @test
     */
    public function testComposePreRenderedEmail()
    {
        $mediumData = $this->campaign->getMediumData();
        $mediumData->setData('prerender', true);

        $recipient = new Recipient();
        $recipient->setCampaign($this->campaign);
        $recipient->setObjectId(1);
        $recipient->setData('firstname', "John");

        $composer = new EmailComposer();
        $composer->setRecipient($recipient);
        $composer->compose(false);

        $this->assertComposedEmail('pre-rendered', $composer);

        // Run again
        $composer = new EmailComposer();
        $composer->setRecipient($recipient);
        $composer->compose(false);

        $this->assertComposedEmail('pre-rendered', $composer);
    }

    /**
     * Check composed email
     *
     * @param string $name
     * @param EmailComposer $composer
     *
     * @return void
     */
    public static function assertComposedEmail($name, EmailComposer $composer)
    {
        $expectedDir = __DIR__ . '/_expected/' . $name;
        $resultDir = __DIR__ . '/_result/' . $name;

        if (!is_dir($resultDir) && !mkdir($resultDir, 0777, true)) {
            self::markTestIncomplete("Unable to create test result directory");
        }

        file_put_contents($resultDir . '/subject.txt', $composer->getSubject());
        file_put_contents($resultDir . '/body.html', $composer->getBodyHtml());
        file_put_contents($resultDir . '/body.txt', $composer->getBodyText());

        self::assertFileEquals(
            $expectedDir . '/subject.txt',
            $resultDir . '/subject.txt',
            "Composed email subject is not correct"
        );

        self::assertFileEquals(
            $expectedDir . '/body.html',
            $resultDir . '/body.html',
            "Composed email html body is not correct"
        );

        self::assertFileEquals(
            $expectedDir . '/body.txt',
            $resultDir . '/body.txt',
            "Composed email test body is not correct"
        );
    }

    /**
     * Mock random string generator helper method
     *
     * @return DataHelper|MockObject
     */
    public function mockRandomHelper()
    {
        /** @var DataHelper|MockObject $helper */
        $helper = $this->getMockBuilder(DataHelper::class)->setMethods(['randomHash'])->getMock();
        $helper->expects($this->any())->method('randomHash')->willReturn('foobar');

        $this->replaceByMock('helper', 'mzax_emarketing', $helper);

        return $helper;
    }
}
