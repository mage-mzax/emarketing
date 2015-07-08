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


class Mzax_Emarketing_Block_Campaign_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        if($row->isValidForSend()) {
            $actions[] = array(
                'url' => $this->getUrl('*/admin_queue/edit', array('campaign_id' => $row->getId())),
                'caption' => Mage::helper('mzax_emarketing')->__('Queue Campaign...')
            );
        }

        
        $actions[] = array(
            'url'     => $this->getUrl('*/*/preview', array('campaign'=>$row->getId())),
            'popup'   => true,
            'caption' => Mage::helper('mzax_emarketing')->__('Preview')
        );
        

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
