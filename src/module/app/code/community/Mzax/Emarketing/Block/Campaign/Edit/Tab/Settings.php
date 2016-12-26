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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_Settings
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    /**
     * Dependency
     *
     * @var Mage_Core_Model_Locale
     */
    protected $_locale;

    /**
     * @var Mzax_Emarketing_Model_System_Config_Source_Trackers
     */
    protected $_trackers;

    /**
     * Load dependencies
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_locale = Mage::app()->getLocale();
        $this->_trackers = Mage::getSingleton('mzax_emarketing/system_config_source_trackers');
    }

    /**
     * @return $this
     */
    public function initForm()
    {
        $form = $this->getForm();
        $form->setHtmlIdPrefix('campaign_');
        $form->setFieldNameSuffix('campaign');

        $campaign = $this->getCampaign();

        $outputFormat = $this->_locale->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        /**
         * Campaign
         */
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'   => $this->__('Campaign'),
            'offer'    => $this->__('Would you like to send to different recipients? <a href="%s" target="_blank">Contact me</a>!', 'http://www.mzax.de/emarketing/recipients.html?utm_source=extension&utm_medium=link&utm_content=campaign-settings&utm_campaign=needmore'),
            'continue' => false
        ));

        if (!$campaign->getId()) {
            /** @var Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset $renderer */
            $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset');
            $renderer->setTemplate('mzax/emarketing/campaign/fieldset-offer.phtml');

            $fieldset->setRenderer($renderer);
        }

        $fieldset->addField('medium', 'hidden', array(
            'name' => 'medium'
        ));

        if ($campaign->getPreset()) {
            $fieldset->addField('preset_name', 'hidden', array(
                'name'  => 'preset_name',
                'value' => $campaign->getPreset()->getName()
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name'     => 'name',
            'required' => true,
            'label'    => $this->__('Campaign Name'),
            'title'    => $this->__('Campaign Name'),
        ));

        if ($campaign->getId()) {
            $fieldset->addField('description', 'textarea', array(
                'name'     => 'description',
                'required' => false,
                'style'    => 'height:60px;',
                'label'    => $this->__('Description'),
                'title'    => $this->__('Description'),
                'note'     => $this->__("For internal use only"),
            ));

            $fieldset->addField('tags', 'text', array(
                'name'     => 'tags',
                'required' => false,
                'label'    => $this->__('Tags'),
                'title'    => $this->__('Campaign'),
                'note'     => $this->__("Space separated tags for internal organising purpose only (e.g. draft reminder ad need-review…)"),
            ));
        }


        // don't show when creating by presets
        if (!$campaign->getPreset()) {
            $fieldset->addField('provider', 'select', array(
                'name'      => 'provider',
                'label'     => $this->__('Campaign Recipient'),
                'title'     => $this->__('Campaign Recipient'),
                'values'    => $campaign->getAvailableProviders(false),
                'note'      => !$campaign->getId()
                    ? $this->__("Who are the recipients of this campaign")
                    : ($campaign->countRecipients()
                        ? $this->__("Changing may alter your current filters")
                        : $this->__("This can not be changed once a recipient has been created.")
                    ),
                'disabled'  => $campaign->getId() && $campaign->countRecipients(),
                'required'  => true,
            ));
        } else {
            $fieldset->addField('provider', 'hidden', array(
                'name' => 'provider'
            ));
        }

        $fieldset->addField('store_id', 'select', array(
            'name'     => 'store_id',
            'label'    => $this->__('Sender Store'),
            'title'    => $this->__('Sender Store'),
            'values'   => Mage::getModel('adminhtml/system_config_source_store')->toOptionArray(),
            'required' => true,
        ));

        // don't show when creating by presets
        if (!$campaign->getPreset()) {
            $fieldset->addField('check_frequency', 'select', array(
                'label'     => $this->__('Check Frequency'),
                'title'     => $this->__('Check Frequency'),
                'name'      => 'check_frequency',
                'required'  => true,
                'options'   => array(
                    '1440'      => $this->__('Once a day'),
                    '720'       => $this->__('Twice a day'),
                    '360'       => $this->__('Every 6 hours'),
                    '180'       => $this->__('Every 3 hours'),
                    '60'        => $this->__('Every hour'),
                    '30'        => $this->__('Every 30 minutes'),
                    '1'         => $this->__('Every time'),
                    '0'         => $this->__('Never automatically'),
                ),
                'value'    => '720',
                'note'     => $this->__("How often to check for new recipients, e.g. Birthdays will do once a day, while abandon carts need to check all the time."),
            ));

            $fieldset->addField('min_resend_interval', 'select', array(
                'label'     => $this->__('Minimum Resend Interval'),
                'title'     => $this->__('Minimum Resend Interval'),
                'name'      => 'min_resend_interval',
                'required'  => true,
                'options'   => array(
                    '0'   => $this->__('[Only send once]'),
                    '1'   => $this->__('One day'),
                    '2'   => $this->__('Two days'),
                    '7'   => $this->__('One week'),
                    '14'  => $this->__('Two weeks'),
                    '31'  => $this->__('1 month'),
                    '62'  => $this->__('2 months'),
                    '91'  => $this->__('3 months'),
                    '122' => $this->__('4 months'),
                    '152' => $this->__('5 months'),
                    '183' => $this->__('6 months'),
                    '274' => $this->__('9 months'),
                    '364' => $this->__('12 months')
                ),
                'value' => '0',
                'note'   => $this->__("The minimum time before a recipient can recieve this campaign again."),
            ));

            $fieldset->addField('max_per_recipient', 'text', array(
                'label'     => $this->__('Max Send-outs per Recipient'),
                'title'     => $this->__('Max Send-outs per Recipient'),
                'name'      => 'max_per_recipient',
                'required'  => true,
                'value' => '0',
                'note'   => $this->__("Maximum send-outs per recipient, zero = unlimited"),
            ));
        }

        if ($campaign->getId()) {
            $fieldset->addField('start_at', 'date', array(
                'name'      => 'start_at',
                'time'      => true,
                'style'     => 'width:60%;',
                'format'    => $outputFormat,
                'label'     => $this->__('Date Start'),
                'image'     => $this->getSkinUrl('images/grid-cal.gif')
            ));
            $fieldset->addField('end_at', 'date', array(
                'name'      => 'end_at',
                'time'      => true,
                'style'     => 'width:60%;',
                'format'    => $outputFormat,
                'label'     => $this->__('Date End'),
                'image'     => $this->getSkinUrl('images/grid-cal.gif')
            ));

            /**
             * Ab Test
             */
            $abtestFieldset = $form->addFieldset('abtest', array(
                'note'    => "If enabled, the user will be logged in if possible.",
                'legend'  => $this->__('AB Testing'),
            ));


            $abtestFieldset->addField('abtest_enable', 'select', array(
                'label'     => $this->__('Enabled'),
                'title'     => $this->__('Enabled'),
                'name'      => 'abtest_enable',
                'required'  => true,
                'options'   => array(
                    '0' => $this->__('No'),
                    '1' => $this->__('Yes')
                ),
                'value' => '0'
            ));

            $abtestFieldset->addField('abtest_traffic', 'text', array(
                'name'      => 'abtest_traffic',
                'label'     => $this->__('Traffic Percentage'),
                'title'     => $this->__('Traffic Percentage'),
                'required'  => true,
                'value'     => '100'
            ));

            /**
             * Campaign
             */
            $advanced = $form->addFieldset(
                'advanced_fieldset',
                array('legend'=>$this->__('Advanced Settings'))
            );

            $advanced->addField('autologin', 'select', array(
                'label'     => $this->__('Autologin'),
                'title'     => $this->__('Autologin'),
                'note'      => $this->__("If enabled, the user will be logged in if possible."),
                'name'      => 'autologin',
                'required'  => true,
                'options'   => array(
                    '1' => $this->__('Enabled'),
                    '0' => $this->__('Disabled'),
                ),
                'value' => '1'
            ));

            $advanced->addField('expire_time', 'select', array(
                'name'      => 'expire_time',
                'label'     => $this->__('Expire Time'),
                'options'   => array(
                       '15' => $this->__('15 minutes'),
                       '30' => $this->__('Half an hour'),
                       '60' => $this->__('One hour'),
                      '120' => $this->__('Two hours'),
                      '180' => $this->__('Three hours'),
                      '720' => $this->__('6 hours'),
                     '1440' => $this->__('12 hours'),
                     '1440' => $this->__('One day'),
                     '2880' => $this->__('Two days'),
                     '7200' => $this->__('5 days'),
                    '10080' => $this->__('One week'),
                    '20160' => $this->__('Two weeks'),
                    '87658' => $this->__('One month'),
                        '0' => $this->__('Never expire')
                ),
                'required'  => true,
                'value'     => '120',
                'note'      => $this->__("When an recipient is queued things can get delayed do to technical issue, domain throttling or other things. You can define an expire time so that if the message has not been sent in the given time frame, don’t send it anymore."),
            ));


            $advanced->addField('identity', 'select', array(
                'name'     => 'identity',
                'label'    => $this->__('Email Sender'),
                'title'    => $this->__('Email Sender'),
                'values'   => Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray(),
                'required' => true,
                'note'     => $this->__("The sender identity to use when sending out the emails. Keep in mind that email bounces will get send back to that email address in case you have defined an inbox to check.")
            ));


            $advanced->addField('default_tracker_id', 'select', array(
                'name'     => 'default_tracker_id',
                'label'    => $this->__('Default Conversion Tracker'),
                'title'    => $this->__('Default Conversion Tracker'),
                'values'   => $this->getTrackerOptions(),
                'required' => true,
            ));


            if ($medium = $campaign->getMedium()) {
                $medium->initSettingsForm($form, $campaign);
                $form->addValues($campaign->getMediumData()->getData());
            }

        } // has id

        $form->addValues($campaign->getData());

        return $this;
    }

    /**
     * Retrieve tracker options
     *
     * @return array
     */
    public function getTrackerOptions()
    {
        $options = $this->_trackers->toOptionArray();
        array_unshift(
            $options,
            array(
                'value' => '0',
                'label' => $this->__("Use Config Default Tracker")
            )
        );

        return $options;
    }

    /**
     * Retrieve form
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }

        return $this->_form;
    }

    /**
     * @param Varien_Data_Form $form
     *
     * @return $this
     */
    public function setForm(Varien_Data_Form $form)
    {
        $this->_form = $form;

        return $this;
    }

    /**
     * Retrieve current campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {
            $this->_campaign = Mage::registry('current_campaign');
        }
        return $this->_campaign;
    }

    /**
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return $this
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->_campaign = $campaign;

        return $this;
    }
}
