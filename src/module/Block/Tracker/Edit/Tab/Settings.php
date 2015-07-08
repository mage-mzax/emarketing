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
 * Default tracker settings tab
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Block_Tracker_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{

    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
    
    
    
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('tracker_');
        $form->setFieldNameSuffix('tracker');

        /* @var $tracker Mzax_Emarketing_Model_Conversion_Tracker */
        $tracker = Mage::registry('current_tracker');

        
        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
            ->setTemplate('cms/page/edit/form/renderer/content.phtml');
        
        /**
         * Tracker
         */
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'   => $this->__('Tracker'),
            'class'    => 'fieldset-wide',
            'offer'    => $this->__('Would you like to track different goals or have any suggestions? <a href="%s" target="_blank">Contact me</a>!', 'http://www.mzax.de/emarketing/goals.html?utm_source=extension&utm_medium=link&utm_content=tracker-settings&utm_campaign=needmore'),
            'continue' => !$tracker->getId()
        ));
        
        $fieldset->addType('info',       Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_info'));
        $fieldset->addType('wildselect', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_wildselect'));
        $fieldset->addType('textarea',   Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_textarea'));
        
        
        $offerRenderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('mzax/emarketing/campaign/fieldset-offer.phtml');
        $fieldset->setRenderer($offerRenderer);
        
        
        
        
        $fieldset->addField('info', 'info', array(
            'text' => $this->__('A tracker allows you to define what conversion goals you want to track for all or certain campaigns.<br />Don’t worry if you are not yet sure what to track yet, your data is there, here you just define how to aggregate that data so it will be visible in your reports. So if you do changes or create a new tracker later then your data will be re-aggregated and you have your full reports.')
        ))->setRenderer($renderer);
        
        
        

        $fieldset->addField('title','text', array(
            'name'     => 'title',
        	'required' => true,
            'label'    => $this->__('Title'),
            'title'    => $this->__('Title')
        ));
    
        
        if(!$tracker->getId()) {
            $fieldset->addField('goal_type','hidden', array(
                'name'     => 'goal_type'
            ));
        }
        else {
            $fieldset->addField('goal_type','select', array(
                'name'     => 'goal_type',
                'label'    => $this->__('Goals'),
                'values'   => Mage::getSingleton('mzax_emarketing/conversion_goal')->getAllOptions(false),
                'note'     => $tracker->hasFilters() 
                    ? $this->__("You can not change this value if you have any filters defined")
                    : false,
            	'required' => true,
                'disabled' => $tracker->hasFilters()
            ));
        }
        
        
        $fieldset->addField('is_active', 'select', array(
            'label'     => $this->__('Enabled'),
            'title'     => $this->__('Enabled'),
            'name'      => 'is_active',
            'required'  => true,
            'disabled'  => $tracker->isDefault(),
            'note'      => $tracker->isDefault() 
                ? $this->__('You can not disable the default tracker.') 
                : '',
            'options'   => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No'),
            ),
            'value' => '1'
        ));
        
        
        $fieldset->addField('description', 'textarea', array(
            'label'       => $this->__('Description'),
            'title'       => $this->__('Description'),
            'placeholder' => $this->__('Add a simple description that is used internally only for your own records.'),
            'name'        => 'description',
            'style'       => 'height:4em;',
        ));
        
        
        /* @var $campaigns Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $campaigns = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $campaigns->addArchiveFilter(false);
        
        
        if($tracker->isDefault()) {
            $fieldset->addField('is_default', 'checkbox', array(
                'label'     => $this->__('Track Campaigns'),
                'title'     => $this->__('Track Campaigns'),
                'disabled'  => true,
                'checked'   => true,
                'note'      => $this->__('The default tracker must always track all campaigns'),
                'name'      => 'is_default',
                'after_element_html' => '<label>'.$this->__('Always track all campaigns').'</label>'
            ));
        }
        else {
            $campaignOptions = $campaigns->toOptionArray();
                 
            $fieldset->addField('campaign_ids', 'wildselect', array(
                'label'     => $this->__('Track Campaigns'),
                'title'     => $this->__('Track Campaigns'),
                'wildcard_label' => $this->__('Always track all campaigns'),
                'name'      => 'campaign_ids[]',
                'values'    => $campaignOptions
            ));
        }
        
        $form->addValues($tracker->getData());
        $this->setForm($form);
        
        return $this;
    }
}
