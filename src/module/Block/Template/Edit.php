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


class Mzax_Emarketing_Block_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';

        $this->_blockGroup = 'mzax_emarketing';
        $this->_controller = 'template';


        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Template'));
        $this->_updateButton('delete', 'label', $this->__('Delete Template'));

        if (Mage::registry('current_template')->getId()) {
            $this->_addButton('download', array(
                'label'     => $this->__('Download'),
                'class'     => 'download',
                'onclick'   => "setLocation('{$this->getUrl('*/*/download', array('_current' => true))}')",
            ));
        }

    }




    public function getHeaderText()
    {
        $template = Mage::registry('current_template');
        if ($template->getId()) {
            return $this->htmlEscape($template->getName());
        }
        else {
            return $this->__('New Template');
        }
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    protected function _prepareLayout()
    {
    	$this->_addButton('save_and_continue', array(
            'label'     => $this->__('Save And Continue Edit'),
            'onclick'   => 'editForm.submit(\''.$this->_getSaveAndContinueUrl().'\')',
            'class' => 'save'
        ), 10);

    	return parent::_prepareLayout();
    }


    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }


    protected function _getSaveAndContinueUrl()
    {
    	return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit'
        ));
    }
}
