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
 * Class Mzax_Emarketing_Block_Template_Upload_Form
 */
class Mzax_Emarketing_Block_Template_Upload_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $form->setHtmlIdPrefix("template");
        $form->setFieldNameSuffix("template");

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Template'),
            'class'  => 'fieldset-wide',
        ));

        $fieldset->addField('template', 'file', array(
            'name'      => 'file',
            'required'  => true,
            'label'     => $this->__('Template File'),
            'title'     => $this->__('Template File'),
        ));

        $this->setForm($form);
        $form->setUseContainer(true);

        return parent::_prepareForm();
    }
}
