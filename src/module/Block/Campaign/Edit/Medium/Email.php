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

class Mzax_Emarketing_Block_Campaign_Edit_Medium_Email extends Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }



    protected function getTemplateOptions()
    {
        $templates = Mage::getResourceSingleton('mzax_emarketing/template_collection')->toOptionArray();
        array_unshift($templates, array(
            'value' => '',
            'label' => $this->__('Choose a Template...')
        ));

        return $templates;
    }



    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form     = $this->getForm();
        $campaign = $this->getCampaign();
        $content  = $this->getContent();
        $data     = $content->getMediumData();



        $fieldset = $form->addFieldset('email_fieldset', array(
        	'legend' => $this->__('Email'),
        	'class'  => 'fieldset-wide',
        ));

        $fieldset->addType('editor', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_emailEditor'));


        $templateOptions = $this->getTemplateOptions();


        /* Stop if no template exist */
        if (count($templateOptions) === 1) {

            $fieldset->addField('template_note', 'note', array(
                'label'     => $this->__('Template'),
                'class'     => 'mzax-template-select',
                'text'      => $this->__("Before you can create an email campaign, you need to setup at least one email template."),
                'after_element_html' => $this->__(' <a href="%s" target="_blank">Edit Templates</a>', $this->getUrl('*/emarketing_template')),
            ));
            return;
        }


        $fieldset->addField('designmode', 'select', array(
            'label'     => $this->__('Design Mode'),
            'note'      => $this->__("If design mode is disabled the WYSIWYG editor will be disabled and will not mess with your html code."),
            'name'      => 'designmode',
            'options'   => array(
                '1' => $this->__('Enabled'),
                '0' => $this->__('Disabled'),
            ),
            'value' => '1'
        ));

        $template = $fieldset->addField('template_id', 'select', array(
            'name'      => 'template_id',
            'required'  => true,
            'label'     => $this->__('Template'),
            'title'     => $this->__('Template'),
            'values'    => $templateOptions,
            'class'     => 'mzax-template-select',
            'note'      => $this->__("A template is required for sending out emails"),
            'after_element_html' => $this->__(' <a href="%s" target="_blank">Edit Templates</a>', $this->getUrl('*/emarketing_template')),
        ));


        $subject = $fieldset->addField('subject', 'text', array(
            'name'      => 'subject',
            'required'  => true,
            'label'     => $this->__('Email Subject'),
            'title'     => $this->__('Email Subject'),
        ));




        $urlParams = array('id' => $campaign->getId());
        if ($content instanceof Mzax_Emarketing_Model_Campaign_Variation) {
            $contentName  = $content->getName();
            $urlParams['variation'] = $content->getId();
        }
        else {
            $contentName  = $this->__('Original');
        }

        $quickSaveUrl = $this->getUrl('*/*/quicksave', $urlParams);
        $previewUrl   = $this->getUrl('*/*/preview', $urlParams);



        $editorConfig = new Varien_Object();
        $editorConfig->setTranslator($this);
        $editorConfig->setFilesBrowserWindowUrl($this->getUrl('adminhtml/cms_wysiwyg_images/index'));
        $editorConfig->setWidgetWindowUrl($this->getUrl('adminhtml/widget/index'));
        $editorConfig->setTemplateLoadUrl($this->getUrl('*/*/templateHtml'));
        $editorConfig->setQuicksaveUrl($quickSaveUrl);
        $editorConfig->setQuicksaveFields(array($subject, $template));
        $editorConfig->setTemplateField($template);
        $editorConfig->setEnableCkeditor($data->getDataSetDefault('designmode', 1));


        if ($campaign->getId()) {
            $editorConfig->setButtons(array(
                array(
                    'title'     => $this->__('Quick Save'),
                    'onclick'   => "{editor}.quicksave(); return false;",
                    'class'     => 'mzax-quicksave',
                    'style'     => 'margin-left: 20px; float: right;'
                ),
                array(
                    'title'     => $this->__('Preview'),
                    'onclick'   => "popWin('$previewUrl', '_blank', 'width=800,height=700,resizable=1,scrollbars=1'); return false;",
                    'class'     => '',
                    'style'     => 'margin-left: 10px; float: right;'
                ),
            ));
        }


        $editorConfig->setSnippets($campaign->getSnippets());


        $editor = $fieldset->addField('body', 'editor', array(
            'name'      => 'body',
            'label'     => $this->__('Email Body'),
            'title'     => $this->__('Email Body'),
            'style'     => 'height:36em',
            'required'  => true,
            'logo'      =>  $this->getSkinUrl('images/logo.gif'),
            'fullscreen_title' => $this->__('Email Campaign %s / %s', $campaign->getName(), $contentName),
            'config'    => $editorConfig,
            'template'  => Mage::getModel('mzax_emarketing/template')->load($data->getTemplateId())
        ));


        // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
            ->setTemplate('cms/page/edit/form/renderer/content.phtml');
        $editor->setRenderer($renderer);







        $fieldset = $form->addFieldset('email_delay', array(
            'legend' => $this->__('Only send out emails at certain times'),
            'class'  => 'fieldset-wide mzax-checkboxes',
        ));
        $fieldset->addType('info', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_info'));

        $hourOptions = array();
        for($i = 0; $i < 24; $i++) {
            $hourOptions[$i] = $this->__("%'.02d:00h", $i);
        }

        $fieldset->addField('info', 'info', array(
            'text'      => $this->__('Keep in mind that this option can significantly delay an email from getting send out. Depending on your campaign this may not be a problem, but sometimes it can. You may want to double check the expire time under "Settings" and make sure it is high enough.')
        ))->setRenderer($renderer);



        $fieldset->addField('day_filter_empty', 'hidden', array(
            'name'      => 'day_filter',
            'value'     => '',
        ));
        $fieldset->addField('time_filter_empty', 'hidden', array(
            'name'      => 'time_filter',
            'value'     => '',
        ));

        $fieldset->addField('day_filter', 'checkboxes', array(
            'name'      => 'day_filter[]',
            'label'     => $this->__('Send only on selected weekdays'),
            'values'    => Mage::app()->getLocale()->getOptionWeekdays(),
            'note'      => $this->__("If nothing select, this filter is disabled.")
        ));

        $fieldset->addField('time_filter', 'checkboxes', array(
            'name'      => 'time_filter[]',
            'label'     => $this->__('Send only at selected hours'),
            'options'   => $hourOptions,
            'note'      => $this->__("If nothing select, this filter is disabled.")
        ));





        /*


        $fieldset = $form->addFieldset('email_advanced', array(
            'legend' => $this->__('Advanced Options'),
            'class'  => 'fieldset-wide',
        ));

        $fieldset->addField('prerender', 'select', array(
            'label'     => $this->__('Pre-Render'),
            'note'      => $this->__("If enabled, email will get pre rendered, cached and then only the basic {{var}} expressions will get parsed. If your content is static or only uses var expressions. Enabling this can increase the render performance by a factor of 10."),
            'name'      => 'prerender',
            'options'   => array(
                '0' => $this->__('Disabled'),
                '1' => $this->__('Enabled'),
            ),
            'value' => '0'
        ));


        if ($campaign === $content) {
            $subject = $fieldset->addField('forward_emails', 'text', array(
                'name'      => 'forward_emails',
                'label'     => $this->__('Forward Email'),
                'note'      => $this->__("All non-auto email replies will get forward to this email address."),
            ));
        }

        */
        $form->addValues($data->getData());

        return $this;


    }

}
