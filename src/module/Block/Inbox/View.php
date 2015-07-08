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


class Mzax_Emarketing_Block_Inbox_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	$this->_blockGroup = 'mzax_emarketing';
        $this->_controller = 'inbox';
        $this->_headerText = Mage::helper('mzax_emarketing')->__('Manage Inbox');
        
        parent::__construct();
        
        $this->removeButton('add');
        
        $this->_addButton('fetch', array(
            'label'     => $this->__('Get new messages'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/fetch') .'\')',
            'class'     => '',
        ));
        
    }

    
    
}
