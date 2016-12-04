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
class Mzax_Emarketing_Block_Inbox_Email_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setHtmlIdPrefix("email");
        $form->setFieldNameSuffix("email");


        /* @var $email Mzax_Emarketing_Model_Inbox_Email */
        $email = Mage::registry('current_email');



        // Setting custom renderer for content field to remove label column
        $rendererWide = $this->getLayout()
            ->createBlock('adminhtml/widget_form_renderer_fieldset_element')
                ->setTemplate('cms/page/edit/form/renderer/content.phtml');

        $aceType = Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_ace');


        if ($email->getId()) {
            $form->addField('message_id', 'hidden', array(
                'name'  => 'message_id',
                'value' => $email->getId()
            ));
        }

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Bounce Message'),
            'class'  => 'fieldset-wide',
        ))->addType('ace', $aceType);




        $fieldset->addField('type', 'select', array(
            'name'      => 'type',
            'required'  => true,
            'label'     => Mage::helper('mzax_emarketing')->__('Bounce Type'),
            'title'     => Mage::helper('mzax_emarketing')->__('Bounce Type'),
            'options'   => array(
                Mzax_Emarketing_Model_Inbox_Email::BOUNCE_SOFT => $this->__('Soft'),
                Mzax_Emarketing_Model_Inbox_Email::BOUNCE_HARD => $this->__('Hard'),
                Mzax_Emarketing_Model_Inbox_Email::AUTOREPLY   => $this->__('Autoreply'),
                Mzax_Emarketing_Model_Inbox_Email::NO_BOUNCE   => $this->__('No Bounce'),
            ),
            'note'      => "Email Bounce Type",
        ));


        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => $this->__('Email (From)'),
            'title'     => $this->__('Email (From)'),
            'note'      => $this->__('The recipient we detected after parsing, may not be correct'),
            'readonly'  => true
        ));


        $fieldset->addField('subject', 'text', array(
            'name'      => 'subject',
            'label'     => $this->__('Email Subject'),
            'title'     => $this->__('Email Subject'),
            'readonly'  => true
        ));


        if ($campaign = $email->getCampaign()) {

            $fieldset->addField('campaign_name', 'link', array(
                'label' => $this->__('Campagin'),
                'value' => $campaign->getName(),
                'href'  => $this->getUrl('*/emarketing_campaign/edit', array('id' => $campaign->getId()))
            ));

        }

        if ($recipient = $email->getRecipient()) {
            if ($campaign) {
                $href = $campaign->getRecipientProvider()->getObject()->getAdminUrl($recipient->getId());
                $recipient->prepare();

                $fieldset->addField('recipient_email', 'link', array(
                        'label' => $this->__('Recipient'),
                        'value' => sprintf('%s <%s>', $recipient->getName(), $recipient->getEmail()),
                        'href'  => $href
                ));
            }
        }


        $editor = $fieldset->addField('message', 'ace', array(
            'name'              => 'message',
            'label'             => $this->__('Message'),
            'title'             => $this->__('Message'),
            'allow_fullscreen'  => false,
            'mode'              => 'ace/mode/text',
            'readonly'          => true,
            'autosize'          => true
        ));






        $fieldset = $form->addFieldset('headers_fieldset', array(
            'legend' => $this->__('Email Headers'),
            'class'  => 'fieldset-wide',
        ))->addType('ace', $aceType);

        $editor = $fieldset->addField('headers', 'ace', array(
            'name'      => 'headers',
            'label'     => $this->__('Headers'),
            'title'     => $this->__('Email Headers'),
            'style'     => 'height:12em',
            'required'  => true,
            'logo'      =>  $this->getSkinUrl('images/logo.gif'),
            'fullscreen_title' => $this->__('Bounce Header'),
            'mode'      => 'ace/mode/praat',
            'readonly'  => true,
            'autosize'  => true
        ))->setRenderer($rendererWide);



        $fieldset = $form->addFieldset('content_fieldset', array(
                'legend' => $this->__('Email Content'),
                'class'  => 'fieldset-wide',
        ))->addType('ace', $aceType);

        $editor = $fieldset->addField('content', 'ace', array(
            'name'      => 'content',
            'label'     => $this->__('Content'),
            'title'     => $this->__('Email Content'),
            'style'     => 'height:36em',
            'required'  => true,
            'logo'      =>  $this->getSkinUrl('images/logo.gif'),
            'fullscreen_title' => $this->__('Bounce Body'),
            'mode'      => 'ace/mode/text',
            'readonly'  => true,
            'value'     => $email->getContent()
        ))->setRenderer($rendererWide);




        $form->addValues($email->getData());
        $this->setForm($form);
        $form->setUseContainer(true);
        return parent::_prepareForm();
    }
}
