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


class Mzax_Emarketing_Block_Campaign_Edit_Tab_Filters extends Mage_Adminhtml_Block_Widget_Form
{

    
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_mzax_emarketing');
        $form->setFieldNameSuffix('mzax_emarketing');

        
        $campaign = Mage::registry('current_campaign');

        
        $form->setHtmlIdPrefix('filter_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('mzax/emarketing/fieldset.phtml')
            ->setTestUrl($this->getUrl('*/*/testFilters', array('_current' => true)))
            ->setNewFilterUrl($this->getUrl('*/*/newFilterHtml', array('campaign' => $campaign->getId())));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('salesrule')->__('Only send campaign to people matching the filters below')
        ))->setRenderer($renderer);
        
        
        
    	$fieldset->addField('filters', 'text', array(
            'name' => 'filters',
            'label' => $this->__('Filters'),
            'title' => $this->__('Filters'),
        ))->setCampaign($campaign)
    	  ->setRenderer(Mage::getBlockSingleton('mzax_emarketing/filters'));
        
        $this->setForm($form);
        
        return $this;
        

    }
    
    /**
     * This method is called before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
    
    
}
