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


class Mzax_Emarketing_Block_Outbox_Email extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';

        $this->_blockGroup = 'mzax_emarketing';
        $this->_controller = 'outbox';
        $this->_mode       = 'email';

        parent::__construct();
        $email = Mage::registry('current_email');


        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));

        if ($email->getId() && $email->getStatus() == Mzax_Emarketing_Model_Outbox_Email::STATUS_NOT_SEND) {
            $this->_addButton('discard', array(
                'label'     => $this->__('Discard'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''. $this->__('Are you sure you want to do this?')
                    .'\', \'' . $this->getUrl('*/*/*', array('_current' => true)) . '\')',
            ));
            $this->_addButton('render', array(
                'label'     => $this->__('re-Render'),
                'class'     => 'save',
                'onclick'   => "setLocation('{$this->getUrl('*/*/render', array('_current' => true))}')",
            ));
        }

        $this->_addButton('download', array(
            'label'     => Mage::helper('adminhtml')->__('Download'),
            'class'     => 'download',
            'onclick'   => "setLocation('{$this->getUrl('*/*/download', array('_current' => true))}')",
        ));


        $this->_removeButton('save');
        $this->_removeButton('reset');

    }


    public function getHeaderText()
    {
        $message = Mage::registry('current_email');
        if ($message->getId()) {
            return $this->htmlEscape($message->getSubject());
        }
        else {
            return $this->__('New Email');
        }
    }



    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }



    protected function _prepareLayout()
    {
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


}
