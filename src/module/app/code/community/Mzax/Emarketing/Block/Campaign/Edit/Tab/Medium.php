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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_Medium
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_Medium extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return $this
     */
    public function initForm()
    {
        /** @var Mzax_Emarketing_Model_Medium $mediums */
        $mediums = Mage::getSingleton('mzax_emarketing/medium');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('campaign_');
        $form->setFieldNameSuffix('campaign');

        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');

        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('mzax/emarketing/campaign/fieldset-offer.phtml');

        /**
         * Campaign
         */
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Campaign'),
            'offer'  => $this->__('Would you like to send out campaigns using different mediums? <a href="%s" target="_blank">Contact me</a>!', 'http://www.mzax.de/emarketing/mediums.html?utm_source=extension&utm_medium=link&utm_content=choose-medium-footer&utm_campaign=needmore')
        ))->setRenderer($renderer);


        $fieldset->addField(
            'name',
            'text',
            array(
                'name'     => 'name',
                'required' => true,
                'label' => $this->__('Campaign Name'),
                'title' => $this->__('Campaign Name'),
            )
        );

        $fieldset->addField(
            'medium',
            'select',
            array(
                'name'      => 'medium',
                'label'     => $this->__('Send Medium'),
                'title'     => $this->__('Send Medium'),
                'values'    => $mediums->getAllOptions(false),
                'note'      => $this->__('Which medium would you like to use to send out this campaign?'),
                'required'  => true,
            )
        );

        $form->addFieldset('presets', array(
            'legend' => $this->__('Campaign Presets'),
        ))->setRenderer($this->getLayout()->createBlock('mzax_emarketing/campaign_new_presets'));

        $form->addValues($campaign->getData());
        $this->setForm($form);

        return $this;
    }
}
