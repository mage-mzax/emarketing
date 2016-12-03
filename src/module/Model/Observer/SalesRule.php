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
 * SalesRule Oberserver
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Observer_SalesRule extends Mzax_Emarketing_Model_Observer_Abstract
{
	
    
    /**
     * Register emarketing sales rule conditions
     * 
     * @see Mzax_Emarketing_Model_SalesRule_Condition_Emarketing
     * @see Mage_SalesRule_Model_Rule_Condition_Combine::getNewChildSelectOptions()
     * @event salesrule_rule_condition_combine
     * @param $observer
     */
    public function addConditions($observer)
    {
        $conditions = $observer->getAdditional()->getConditions();
        if (!$conditions) {
            $conditions = array();
        }
        
        /* @var $condition Mzax_Emarketing_Model_SalesRule_Condition_Emarketing */
        $condition = Mage::getModel('mzax_emarketing/salesRule_condition_emarketing');
        
        $attributes = $condition->loadAttributeOptions()->getAttributeOption();
        
        $values = array();
        foreach ($attributes as $attribute => $label) {
            $values[] = array(
                'value' => "mzax_emarketing/salesRule_condition_emarketing|".$attribute,
                'label' => $label
            );
        }
        
        $conditions[] = array(
            'value' => $values,
            'label' => "Emarketing"
        );
        
        $observer->getAdditional()->setConditions($conditions);
    }
    
    
}
