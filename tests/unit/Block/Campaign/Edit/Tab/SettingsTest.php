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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_SettingsTest
 *
 * @covers Mzax_Emarketing_Block_Campaign_Edit_Tab_Settings
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_SettingsTest
    extends \JSiefer\MageMock\PHPUnit\TestCase\Block\Widget\FormTestCase
{
    /**
     * Test from with new campaign
     *
     * @return void
     * @test
     */
    public function testInitNewCampaign()
    {
        $layout = new Mage_Core_Model_Layout();
        $layout->createBlock = function($name) {
            switch ($name) {
                case 'adminhtml/widget_form_renderer_fieldset':
                    return new Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset();
            }

            return new Mage_Core_Block_Template();
        };

        $campaign = new Mzax_Emarketing_Model_Campaign();

        $block = new Mzax_Emarketing_Block_Campaign_Edit_Tab_Settings();
        $block->setLayout($layout);
        $block->setCampaign($campaign);
        $block->initForm();

        $form = $block->getForm();

        $this->assertForm('settings/new-campaign.form', $form);
    }

    /**
     * Test from with an existing campaign
     *
     * @return void
     * @test
     */
    public function testWithExistingCampaignNoRecipient()
    {
        $layout = new Mage_Core_Model_Layout();
        $layout->createBlock = function($name) {
            switch ($name) {
                case 'adminhtml/widget_form_renderer_fieldset':
                    return new Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset();
            }

            return new Mage_Core_Block_Template();
        };

        $trackers = $this->getSingleton('mzax_emarketing/system_config_source_trackers');
        $trackers->expects($this->once())->method('toOptionArray')->willReturn(
            [
                ['value' => 1, 'label' => 'Tracker 1'],
                ['value' => 2, 'label' => 'Tracker 2']
            ]
        );


        $campaign = new Mzax_Emarketing_Model_Campaign();
        $campaign->setId(1);
        $campaign->setMedium('email');
        $campaign->setAbtestEnable(1);
        $campaign->setAbtestTraffic(20);
        $campaign->setCheckFrequency(1);
        $campaign->setProvider('customer');
        $campaign->setData('recipients_count', 0);

        $block = new Mzax_Emarketing_Block_Campaign_Edit_Tab_Settings();
        $block->setLayout($layout);
        $block->setCampaign($campaign);
        $block->initForm();

        $form = $block->getForm();

        $this->assertForm('settings/existing-campaign-no-recipients.form', $form);
    }


}
