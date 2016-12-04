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


class Mzax_Emarketing_Block_Campaign_Edit_Tab_Content_Variation
    extends Mzax_Emarketing_Block_Campaign_Edit_Tab_Content_Original
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }


    /**
     *
     * @return Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract
     */
    public function getContentForm()
    {
        $variation = $this->getContent();


        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix("variation_medium_{$variation->getId()}_");
        $form->setFieldNameSuffix("variation[{$variation->getId()}][medium_data]");

        /* @var $mediumForm Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract */
        $mediumForm = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_medium_email');
        $mediumForm->setContent($this->getContent());
        $mediumForm->initForm($form);

        return $mediumForm;
    }




    public function initForm()
    {
        parent::initForm();

        $form = $this->getForm();

        /* @var $content Mzax_Emarketing_Model_Campaign_Content */
        $variation = $this->getContent();

        $form->setHtmlIdPrefix("variation_{$variation->getId()}_");
        $form->setFieldNameSuffix("variation[{$variation->getId()}]");



        /*
         * Variation
         */
        $fieldset = $form->addFieldset('variation_fieldset', array(
            'legend' => $this->__('Settings'),
            'class'  => 'fieldset-wide',
        ));


        $fieldset->addField('is_active', 'select', array(
            'label'     => $this->__('Is Active'),
            'title'     => $this->__('Is Active'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => array(
                    '1' => $this->__('Enabled'),
                    '0' => $this->__('Disabled'),
            ),
            'value' => '1'
        ));


        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'required'  => true,
            'label'     => $this->__('Name'),
            'title'     => $this->__('Name'),
        ));

        $form->addValues($variation->getData());

        return $this;


    }
}
