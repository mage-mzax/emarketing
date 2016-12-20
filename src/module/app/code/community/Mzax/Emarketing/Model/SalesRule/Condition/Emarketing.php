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
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Emarketing Sales Rule Condition
 *
 * @method string getUnit()
 * @method $this setUnit(string $value)
 *
 * @method string getCampaign()
 * @method $this setCampaign(string $value)
 *
 * @method string getValue()
 * @method $this setValue(string $value)
 *
 * @method string getType()
 * @method $this setType(string $value)
 *
 * @method string getPrefix()
 * @method $this setPrefix(string $value)
 *
 * @method string[] getAttributeOption()
 * @method $this setAttributeOption(string[] $value)
 *
 * @method Varien_Data_Form getForm()
 * @method Varien_Data_Form_Element_Hidden getTypeElement()
 * @method Varien_Data_Form_Element_Abstract getValueElement()
 */
class Mzax_Emarketing_Model_SalesRule_Condition_Emarketing extends Mage_Rule_Model_Condition_Abstract
{
    const DEFAULT_UNIT = 'day';

    /**
     * Session Manager
     *
     * @var Mzax_Emarketing_Model_SessionManager
     */
    protected $_sessionManager;

    /**
     * SalesRule Constructor.
     * Load dependencies.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_sessionManager = Mage::getSingleton('mzax_emarketing/sessionManager');
    }

    /**
     * Load attribute options
     *
     * @return $this
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
     *
     * @param array $arrAttributes
     *
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
     *
     * @return $this
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
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();
        $html .= Mage::helper('mzax_emarketing')->__(
            "If user came via campaign %s which was sent no later than %s %s ago.",
            $this->getCampaignElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getUnitElement()->getHtml()
        );

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

        if (!($unit = $this->getUnit())) {
             $unit = self::DEFAULT_UNIT;
        }

        $name = '';
        foreach ($options as $option) {
            if ($unit === $option['value']) {
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

        if (!in_array($unit, array('day', 'hour', 'week', 'month'))) {
            $unit = self::DEFAULT_UNIT;
        }

        return strtotime(sprintf('-%s %s', $value, $unit));
    }

    /**
     * Validate condition
     *
     * @see Mage_Rule_Model_Condition_Abstract::validate()
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $session = $this->_sessionManager->getSession();

        // check if we have a recipient
        $recipient = $session->getLastRecipient();
        if (!$recipient) {
            return false;
        }

        // check if he came from the campaign
        if ($this->getCampaign() != $recipient->getCampaignId()) {
            return false;
        }

        return (strtotime($recipient->getSentAt()) > $this->getTimeLimit());
    }
}
