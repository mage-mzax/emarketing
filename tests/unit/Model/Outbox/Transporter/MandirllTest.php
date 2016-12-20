<?php


use Mzax_Emarketing_Model_Outbox_Transporter_Mandrill as MandrillTransporter;
use PHPUnit_Framework_MockObject_MockObject as MockObject;


/**
 * Class Mzax_Emarketing_Model_Outbox_Transporter_MandrillTest
 */
class Mzax_Emarketing_Model_Outbox_Transporter_MandrillTest
    extends \JSiefer\MageMock\PHPUnit\TestCase
{
    /**
     *
     * @return void
     * @test
     */
    public function testSetup()
    {
        $store = new Mage_Core_Model_Store();

        $config = $this->getMock(Mzax_Emarketing_Model_Config::class);
        $config->expects($this->any())->method('get')->willReturnMap(
            [
                ['mzax_emarketing/email/mandrill_username', $store, 'username'],
                ['mzax_emarketing/email/mandrill_password', $store, 'password'],
                ['mzax_emarketing/email/mandrill_default_tags', $store, 'tags,foo,  bar'],
                ['mzax_emarketing/email/mandrill_subaccount', $store, 'sub-account'],
            ]
        );
        $config->expects($this->any())->method('flag')->willReturnMap(
            [
                ['mzax_emarketing/email/mandrill_category_tags', $store, true],
                ['mzax_emarketing/email/mandrill_metatags', $store, true],
            ]
        );


        $this->getMage()->register('_singleton/mzax_emarketing/config', $config);

        $emailHeaders = [];

        $campaign = new Mzax_Emarketing_Model_Campaign();
        $campaign->setId(1);
        $campaign->setName('test');
        $campaign->setTags(['a', 'b']);
        $campaign->setStore($store);

        $recipient = new Mzax_Emarketing_Model_Recipient();
        $recipient->setCampaign($campaign);
        $recipient->setId(100);

        $outboxEmail = new Mzax_Emarketing_Model_Outbox_Email();
        $outboxEmail->setRecipient($recipient);

        $mail = new Mzax_Emarketing_Model_Outbox_Email_Mail();
        $mail->setOutboxEmail($outboxEmail);
        $mail->addHeader = function ($name, $value) use (&$emailHeaders) {
            $emailHeaders[$name] = $value;
        };


        /** @var MockObject $email */
        $email = new Mzax_Emarketing_Model_Outbox_Email();
        $email->setCampaign($campaign);

        $transporter = new MandrillTransporter();
        $transporter->expects($this->once())->method('send')->with($this->identicalTo($mail));
        $transporter->setup($email);

        $this->assertEquals(
            array (
                'username' => 'username',
                'password' => 'password',
                'port' => 587,
                'ssl' => 'tls',
            ),
            $transporter->_config
        );

        $transporter->send($mail);

        $this->assertEquals(
            [
                'X-MC-Tags' => 'a,b,tags,foo,bar',
                'X-MC-Metadata' => '{"c_name":"test","c_id":1,"r_id":100,"v_id":null}',
                'X-MC-Subaccount' => 'sub-account',
            ],
            $emailHeaders
        );
    }
}
