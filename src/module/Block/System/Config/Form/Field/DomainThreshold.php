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


/**
 * 
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Block_System_Config_Form_Field_DomainThreshold
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('domain', array(
            'label' => $this->__('Domain'),
            'style' => 'width:150px',
            'class' => 'required-entry'
        ));
        $this->addColumn('time_threshold', array(
            'label' => $this->__('[T]'),
            'style' => 'width:30px',
            'class' => 'required-entry validate-zero-or-greater'
        ));
        $this->addColumn('send_threshold', array(
            'label' => $this->__('[S]'),
            'style' => 'width:30px',
            'class' => 'required-entry validate-zero-or-greater'
        ));
        $this->addColumn('rest_time', array(
            'label' => $this->__('[R]'),
            'style' => 'width:30px',
            'class' => 'required-entry validate-zero-or-greater'
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Domain');
        parent::__construct();
    }
}
