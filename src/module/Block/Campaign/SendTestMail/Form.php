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
 * Class Mzax_Emarketing_Block_Campaign_SendTestMail_Form
 */
class Mzax_Emarketing_Block_Campaign_SendTestMail_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign  = Mage::registry('current_campaign');

        /* @var $recipient Mzax_Emarketing_Model_Recipient */
        $recipient = Mage::registry('current_recipient');

        $form->addField('id', 'hidden', array(
            'name'  => 'id',
            'value' => $campaign->getId()
        ));

        $form->addField('object_id', 'hidden', array(
            'name'  => 'object_id',
            'value' => $recipient->getObjectId()
        ));

        $form->addField('recipient_name', 'text', array(
            'name'  => 'recipient_name',
            'label' => $this->__("Recipient Name"),
            'value' => $recipient->getName()
        ));


        $user = Mage::getSingleton('admin/session')->getUser();

        $form->addField('recipient_email', 'text', array(
            'name'  => 'recipient_email',
            'label' => $this->__("Recipient Email"),
            'value' => $user->getEmail()
        ));

        if ($campaign->hasVariations()) {
            $options = $campaign->getVariations()->toOptionArray();

            array_unshift($options, array('value' => '0', 'label' => $this->__('[Orignal]')));

            $form->addField('variation', 'select', array(
                'name'   => 'variation',
                'label'  => $this->__("Variation"),
                'value'  => $this->getRequest()->getParam('variation', '0'),
                'values' => $options
            ));
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
