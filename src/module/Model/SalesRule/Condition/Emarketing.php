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
 * Emarketing Sales Rule Condition
 * 
 * 
 * @method string getUnit()
 * @method string getCampaign()
 * @method string getValue()
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_SalesRule_Condition_Emarketing extends Mage_Rule_Model_Condition_Abstract
{
    const DEFAULT_UNIT = 'day';
    
    
    /**
     * 
     * (non-PHPdoc)
     * @see Mage_Rule_Model_Condition_Abstract::loadAttributeOptions()
     * @return Mzax_Emarketing_Model_SalesRule_Condition_Emarketing
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'mzax_emarketing_campaign' => Mage::helper('mzax_emarketing')->__('Campaign')
        );

        $this->setAttributeOption($attributes);

        return $this;
    }
    
    
    
    /**
     * As array
     * 
     * @see Mage_Rule_Model_Condition_Abstract::asArray()
     * @return array
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'type'     => $this->getType(),
            'campaign' => $this->getCampaign(),
            'unit'     => $this->getUnit(),
            'value'    => $this->getValue()
        );
        return $out;
    }
    
    
    
    /**
     * Load from array
     * 
     * @see Mage_Rule_Model_Condition_Abstract::loadArray()
     * @param array $arr
     * @return Mzax_Emarketing_Model_SalesRule_Condition_Emarketing
     */
    public function loadArray($arr)
    {
        $this->setCampaign($arr['campaign']);
        $this->setUnit($arr['unit']);
        parent::loadArray($arr);
        return $this;
    }

    
    
    /**
     * As XML
     * 
     * @see Mage_Rule_Model_Condition_Abstract::asXml()
     * @return string
     */
    public function asXml()
    {
        $xml = '<campaign>' . $this->getCampaign() . '</campaign>'
             . '<unit>' .     $this->getUnit() .     '</unit>'
             . parent::asXml();
        return $xml;
    }

    
    
    /**
     * As html
     * 
     * @see Mage_Rule_Model_Condition_Abstract::asHtml()
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml()
              . Mage::helper('mzax_emarketing')->__("If user came via campaign %s which was sent no later than %s %s ago.", 
                    $this->getCampaignElement()->getHtml(),
                    $this->getValueElement()->getHtml(), 
                    $this->getUnitElement()->getHtml());
        
        return $html . $this->getRemoveLinkHtml();
    }
    
    
    
    /**
     * Retrieve campaign form element
     * 
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getCampaignElement()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $collection->addArchiveFilter(false);
        
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        $campaign = $collection->getItemById($this->getCampaign());
        
        $elementId   = sprintf('%s__%s__campaign', $this->getPrefix(), $this->getId());
        $elementName = sprintf('rule[%s][%s][campaign]', $this->getPrefix(), $this->getId());
        $element     = $this->getForm()->addField($elementId, 'select', array(
            'name'          => $elementName,
            'values'        => $collection->toOptionArray(),
            'value'         => $campaign ? $campaign->getId() : '',
            'value_name'    => $campaign ? $campaign->getName() : '',
        ));
        $element->setRenderer(Mage::getBlockSingleton('rule/editable'));

        return $element;
    }
    
    
    
    
    /**
     * Retrieve time unit form element
     * 
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getUnitElement()
    {
        $h = Mage::helper('mzax_emarketing');
        
        $options = array();
        $options[] = array('value' => 'hour',  'label' => $h->__("hour(s)"));
        $options[] = array('value' => 'day',   'label' => $h->__("day(s)"));
        $options[] = array('value' => 'week',  'label' => $h->__("week(s)"));
        $options[] = array('value' => 'month', 'label' => $h->__("month(s)"));
        
        
        if(!($unit = $this->getUnit())) {
             $unit = self::DEFAULT_UNIT;
        }
        
        $name = '';
        foreach($options as $option) {
            if($unit === $option['value']) {
                $name = $option['label'];
                break;
            }
        }
        
        $elementId   = sprintf('%s__%s__unit', $this->getPrefix(), $this->getId());
        $elementName = sprintf('rule[%s][%s][unit]', $this->getPrefix(), $this->getId());
        $element     = $this->getForm()->addField($elementId, 'select', array(
            'name'          => $elementName,
            'values'        => $options,
            'value'         => $unit,
            'value_name'    => $name,
        ));
        $element->setRenderer(Mage::getBlockSingleton('rule/editable'));

        return $element;
    }
    
    
    
    /**
     * Retrieve time limit from value and unit
     * 
     * @return number
     */
    public function getTimeLimit()
    {
        $value = max(0, (int) $this->getValue());
        $unit  = $this->getUnit();
        
        if(!in_array($unit, array('day', 'hour', 'week', 'month'))) {
            $unit = self::DEFAULT_UNIT;
        }
        
        return strtotime(sprintf('-%s %s', $value, $unit));
    }
    
    
    
    
    /**
     * Validate condition
     * 
     * 
     * @see Mage_Rule_Model_Condition_Abstract::validate()
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $recipient = $this->getSession()->getLastRecipient();
        
        // check if we have a recipient
        if(!$recipient) {
            return false;
        }
        
        // check if he came from the campaign
        if($this->getCampaign() != $recipient->getCampaignId()) {
            return false;
        }
        
        return (strtotime($recipient->getSentAt()) > $this->getTimeLimit());
    }

    
    
    

    /**
     * Retrieve session object model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('mzax_emarketing/session');
    }
}
