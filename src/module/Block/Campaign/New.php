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
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Block_Campaign_New extends Mzax_Emarketing_Block_Campaign_Edit
{


    public function getHeaderText()
    {
        return $this->__('New Campaign');
    }

    
    public function getValidationUrl()
    {
        return null;
    }
    
    
    protected function _prepareLayout()
    {
    	parent::_prepareLayout();
    	
    	/* @var $campaign  Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');
    	
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('save_and_continue');    	
    }
    
    
    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/new');
    }
    
}
