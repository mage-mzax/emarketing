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


class Mzax_Emarketing_Block_Newsletter_List_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }



    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('list_');
        $form->setFieldNameSuffix('list');



        /* @var $list Mzax_Emarketing_Model_Newsletter_List */
        $list = Mage::registry('current_list');


        if($list->getId()) {
            $form->addField('list_id', 'hidden', array(
                'name'  => 'list_id',
                'value' => $list->getId()
            ));
        }


        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('List Options'),
            'class'  => 'fieldset-wide',
        ));



        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'required'  => true,
            'label'     => $this->__('List Name'),
            'title'     => $this->__('Newsletter List Name'),
        ));

        $fieldset->addField('description', 'textarea', array(
            'name'      => 'description',
            'required'  => false,
            'label'     => $this->__('Description'),
            'title'     => $this->__('Description'),
            'style'     => 'height:4em;',
            'note'      => "Optional short description for the subscriber",
        ));



        $fieldset->addField('is_private', 'select', array(
            'label'     => $this->__('Visibility'),
            'title'     => $this->__('Visibility'),
            'note'      => $this->__("Private lists are not visible to a subscriber by default."),
            'name'      => 'is_private',
            'required'  => true,
            'options'   => array(
                '1' => $this->__('Private list'),
                '0' => $this->__('Public List'),
            ),
            'value' => '0'
        ));



        $fieldset->addField('auto_subscribe', 'select', array(
            'label'     => $this->__('Auto Subscribe'),
            'title'     => $this->__('Auto Subscribe'),
            'note'      => $this->__("If enabled all existing and new subscribers will get added to the list"),
            'name'      => 'auto_subscribe',
            'required'  => true,
            'options'   => array(
                '1' => $this->__('Enabled'),
                '0' => $this->__('Disabled'),
            ),
            'value' => '1'
        ));



        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'multiselect', array(
                'name'      => 'store_ids[]',
                'label'     => $this->__('Store View'),
                'title'     => $this->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('store_ids', 'hidden', array(
                'name'      => 'store_ids[]'
            ));
        }


        $form->addValues($list->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
