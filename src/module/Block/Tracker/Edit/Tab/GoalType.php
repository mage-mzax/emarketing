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
class Mzax_Emarketing_Block_Tracker_Edit_Tab_GoalType extends Mage_Adminhtml_Block_Widget_Form
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
            'legend'   => $this->__('Setup a new conversion goal tracker'),
            'class'    => 'fieldset-wide',
            'offer'    => $this->__('Would you like to track different goals or have any suggestions? <a href="%s" target="_blank">Contact me</a>!', 'http://www.mzax.de/emarketing/goals.html?utm_source=extension&utm_medium=link&utm_content=setup-tracker&utm_campaign=needmore'),
            'continue' => !$tracker->getId()
        ));
        
        $fieldset->addType('info', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_info'));
        $fieldset->addType('wildselect', Mage::getConfig()->getModelClassName('mzax_emarketing/form_element_wildselect'));
        
        
        
        $offerRenderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('mzax/emarketing/campaign/fieldset-offer.phtml');
        $fieldset->setRenderer($offerRenderer);
        
        
        $fieldset->addField('info', 'info', array(
            'text' => $this->__('First you must decide what conversion goals this tracker should use as this will drive what filters are available.')
        ))->setRenderer($renderer);
        
        
        
        $fieldset->addField('title','text', array(
            'name'     => 'title',
        	'required' => true,
            'label'    => $this->__('Title'),
            'title'    => $this->__('Title'),
        ));
        
        
        // @todo disable if it has reci
        $fieldset->addField('goal_type','select', array(
            'name'     => 'goal_type',
            'label'    => $this->__('What goals to track?'),
            'values'   => Mage::getSingleton('mzax_emarketing/conversion_goal')->getAllOptions(false),
            'note'     => $this->__("What conversion goal would you like to track"),
        	'required' => true
        ));
        
        
        
        $form->addValues($tracker->getData());
        $this->setForm($form);
        
        return $this;
        

    }
}
