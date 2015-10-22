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

class Mzax_Emarketing_Emarketing_ShortcutController extends Mage_Adminhtml_Controller_Action
{
	
	
    
    /**
     * 
     */
    public function orderItemAction()
    {
        $id   = $this->getRequest()->getParam('id');
        $item = Mage::getModel('sales/order_item')->load($id);
        
        if($item->getId()) {
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $item->getOrderId(), '_fragment' => 'order_item_'.$id));
        }
        else {
            $this->_redirect('adminhtml/sales_order/index');
        }
    }
 

    
}