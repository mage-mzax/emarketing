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


class Mzax_Emarketing_Block_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setHtmlIdPrefix("template");
        $form->setFieldNameSuffix("template");
        
        
        
                
        
        /* @var $template Mzax_Emarketing_Model_Template */
        $template = Mage::registry('current_template');
        
        
        if($template->getId()) {
            $form->addField('template_id', 'hidden', array(
                'name'  => 'template_id',
                'value' => $template->getId()
            ));
        }

        
        $fieldset = $form->addFieldset('base_fieldset', array(
                'legend' => $this->__('Template Option'),
                'class'  => 'fieldset-wide',
            ))
            ->addType('editor', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_templateEditor'))
            ->addType('credits', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_credits'));
        

        
        $fieldset->addField('credits', 'credits', array(
                'name'      => 'credits',
                'required'  => true
        ));
        
        
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'required'  => true,
            'label'     => $this->__('Template Name'),
            'title'     => $this->__('Template Name'),
        ));
        
        $fieldset->addField('description', 'textarea', array(
            'name'      => 'description',
            'required'  => true,
            'label'     => $this->__('Description'),
            'title'     => $this->__('Description'),
            'style'     => 'height:4em;',
            'note'      => "For internal use only",
        ));
        
        
        
        $snippets = new Mzax_Emarketing_Model_Medium_Email_Snippets;
        Mage::getSingleton('mzax_emarketing/medium_email')->prepareSnippets($snippets);
        
        
        $editorConfig = new Varien_Object();
        $editorConfig->setFilesBrowserWindowUrl($this->getUrl('adminhtml/cms_wysiwyg_images/index'));
        $editorConfig->setWidgetWindowUrl($this->getUrl('adminhtml/widget/index'));
        $editorConfig->setSnippets($snippets);
        
        
        
        $editor = $fieldset->addField('body', 'editor', array(
            'name'      => 'body',
            'required'  => true,
            'label'     => $this->__('Template HTML'),
            'title'     => $this->__('Template HTML'),
            'logo'      =>  $this->getSkinUrl('images/logo.gif'),
            'fullscreen_title' => $this->__('Template %s', $template->getName()),
            'style'     => 'height:50em;',
            'value'     => '',
            'config'    => $editorConfig
        ));
        
        // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
                ->setTemplate('cms/page/edit/form/renderer/content.phtml');
        $editor->setRenderer($renderer);
        
        
        
        
        $form->addValues($template->getData());
        $this->setForm($form);
        $form->setUseContainer(true);
        return parent::_prepareForm();
    }
}
