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
 * Class Mzax_Emarketing_Block_Tracker_Upload_Form
 */
class Mzax_Emarketing_Block_Tracker_Upload_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $form->setHtmlIdPrefix("tracker");
        $form->setFieldNameSuffix("tracker");

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Upload new conversion tracker'),
            'class'  => 'fieldset-wide',
        ));

        $fieldset->addType('info', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_info'));

        $noLabel = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
            ->setTemplate('cms/page/edit/form/renderer/content.phtml');


        $fieldset->addField('info', 'info', array(
            'text' => $this->__('You can download and upload trackers. This makes it easy if you want to share or backup any custom configuration. Trackers should always be backwards compatible however if you load a tracker from a newer version than you have installed a warning will appear. If thats the case, test the filter and make sure it works as expected.')
        ))->setRenderer($noLabel);


        $fieldset->addField('template', 'file', array(
            'name'      => 'file',
            'required'  => true,
            'label'     => $this->__('Conversion Tracker File'),
            'title'     => $this->__('Conversion Tracker File'),
            'note'      => $this->__('Select the tracker file that you want to upload (*.mzax.tracker).'),
        ));

        $this->setForm($form);
        $form->setUseContainer(true);

        return parent::_prepareForm();
    }
}
