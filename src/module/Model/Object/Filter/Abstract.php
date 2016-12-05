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
 * Class Mzax_Emarketing_Model_Object_Filter_Abstract
 */
abstract class Mzax_Emarketing_Model_Object_Filter_Abstract extends Mzax_Emarketing_Model_Object_Filter_Component
{
    /**
     * Child Filters
     *
     * @var Mzax_Emarketing_Model_Object_Filter_Abstract[]
     */
    protected $_filters = array();

    /**
     *
     * @var string
     */
    protected $_formHtml;

    /**
     * @var Varien_Data_Form
     */
    protected $_form;

    /**
     * Mzax_Emarketing_Model_Object_Filter_Abstract constructor.
     *
     * @param null $config
     */
    public function __construct($config = null)
    {
        parent::__construct();

        if ($config instanceof Mage_Core_Model_Config_Element) {
            $this->_prepareFilter($config);
        }
    }

    /**
     * Prepare filter
     *
     * @param Mage_Core_Model_Config_Element $config
     *
     * @return void
     */
    protected function _prepareFilter(Mage_Core_Model_Config_Element $config)
    {
        $this->_type = $config->getName();

        Mage::dispatchEvent("mzax_emarketing_email_filter_prepare", array('filter' => $this));
        Mage::dispatchEvent("mzax_emarketing_email_filter_prepare_" . $this->_type, array('filter' => $this));
    }

    /**
     * Retrieve object
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getObject()
    {
        return $this->getParentObject();
    }

    /**
     * Set ID
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        if ($this->_filters) {
            foreach ($this->_filters as $i => $filter) {
                $filter->setId($this->_id . '--' . ($i+1));
            }
        }

        return $this;
    }

    /**
     * Add child filter
     *
     * @param string|array|Mzax_Emarketing_Model_Object_Filter_Abstract $param
     * @param bool $quite
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract
     * @throws Mage_Exception
     */
    public function addFilter($param, $quite = true)
    {
        if (is_string($param)) {
            $filter = self::getFilterFactory()->factory($param);
            if (!$filter) {
                if ($quite) {
                    return null;
                }

                throw new Mage_Exception(
                    Mage::helper('mzax_emarketing')->__(
                        'Failed to initialise filter with type “%s”. This filter might not be installed on your system.',
                        $param
                    )
                );
            }
        } elseif (is_array($param) && isset($param['type'])) {
            $filter = self::getFilterFactory()->factory($param['type']);
            if (!$filter) {
                if ($quite) {
                    return null;
                }

                throw new Mage_Exception(
                    Mage::helper('mzax_emarketing')->__(
                        'Failed to initialise filter with type “%s”. This filter might not be installed on your system.',
                        $param['type']
                    )
                );
            }
        } else {
            $filter = $param;
        }

        if (!$filter instanceof Mzax_Emarketing_Model_Object_Filter_Abstract) {
            return null;
        }

        if (!$this->acceptFilter($filter)) {
            if ($quite) {
                return null;
            }
            throw new Mage_Exception(
                Mage::helper('mzax_emarketing')->__(
                    'Filter of type “%s” does not allow child of type %s.',
                    $this->getType(),
                    $filter->getType()
                )
            );
        }

        $this->_filters[] = $filter->setParent($this);
        $filter->setId($this->getId() . '--' . count($this->_filters));

        if (is_array($param)) {
            $filter->load($param);
        }

        return $filter;
    }

    /**
     * Retrieve all child filters
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract[]
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Retrieve filter by index
     *
     * @param int $index
     *
     * @return Mzax_Emarketing_Model_Object_Filter_Abstract
     */
    public function getFilterByIndex($index)
    {
        if (isset($this->_filters[$index])) {
            return $this->_filters[$index];
        }

        return null;
    }

    /**
     * Retrieve all filter options for this filter
     *
     * @return array
     */
    public function getOptions()
    {
        return array($this->getType() => $this->getTitle());
    }

    /**
     * Retrieve Varien Data Form
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        /** @var Mzax_Emarketing_Block_Editable $elementRenderer */
        $elementRenderer = Mage::getBlockSingleton('mzax_emarketing/editable');

        $prefix = $this->getRoot()->getFormPrefix();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix("{$prefix}__{$this->getId()}__");
        $form->setFieldNameSuffix("{$prefix}[{$this->getId()}]");
        $form->setElementRenderer($elementRenderer);

        return $form;
    }

    /**
     * Reset filter
     *
     * @return $this
     */
    public function reset()
    {
        $this->unsetData();
        $this->_filters = array();
        $this->_formHtml = null;

        return $this;
    }

    /**
     * Retrieve data
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function getDataSetDefault($key, $default = null)
    {
        // check for default class constant
        if ($default === null) {
            $const = get_class($this) . '::DEFAULT_' . strtoupper($key);
            if (defined($const)) {
                $default = constant($const);
            }
        }

        return parent::getDataSetDefault($key, $default);
    }

    /**
     * Prepare parameters
     *
     * @return void
     */
    public function prepareParams()
    {
    }

    /**
     * Load filters from data
     *
     * @param mixed $data
     * @param boolean $quite Throw en exception if a filter was not available
     *
     * @return $this
     * @throws Mage_Exception
     * @throws Exception
     */
    public function load($data, $quite = true)
    {
        $this->reset();

        if (empty($data)) {
            return $this;
        }

        // string should be a JSON
        if (is_string($data)) {
            try {
                $data = Zend_Json::decode($data);
            } catch (Zend_Json_Exception $e) {
                throw new Exception("Failed to decode filter json: {$e->getMessage()}", 0, $e);
            }
        }

        if (is_array($data)) {
            if (isset($data['filters'])) {
                foreach ($data['filters'] as $filter) {
                    $this->addFilter($filter, $quite);
                }
                unset($data['filters']);
            }
            $this->setData($data);
            $this->prepareParams();
            $this->setDataChanges(false);
        }

        return $this;
    }

    /**
     * Load data from flat post array
     *
     * e.g.
     * array(4) {
     *    [1]=>
     *    array(4) {
     *      ["type"]=>
     *      string(7) "combine"
     *      ["binder"]=>
     *      string(3) "all"
     *      ["expectation"]=>
     *      string(4) "true"
     *    }
     *    ["1--1"]=>
     *    array(4) {
     *      ["type"]=>
     *      string(7) "combine"
     *      ["binder"]=>
     *      string(3) "any"
     *      ["expectation"]=>
     *      string(4) "true"
     *    }
     *    ["1--1--1"]=>
     *    array(2) {
     *      ["type"]=>
     *      string(10) "newsletter"
     *      ["subscripted"]=>
     *      string(9) "subscript"
     *    }
     *    ["1--1--2"]=>
     *    array(2) {
     *      ["type"]=>
     *      string(10) "newsletter"
     *      ["subscripted"]=>
     *       string(15) "not_unsubscript"
     *     }
     * }
     *
     * @param array $data
     *
     * @return $this
     */
    public function loadFlatArray(array $data)
    {
        $filters = array();

        foreach ($data as $id => $filterData) {
            $path = explode('--', $id);
            $list =& $filters;

            while (count($path) > 1) {
                $id = array_shift($path);
                if (!isset($list[$id])) {
                    continue 2;
                }
                if (!isset($list[$id]['filters'])) {
                    $list[$id]['filters'] = array();
                }
                $list =& $list[$id]['filters'];
            }

            $id = array_shift($path);
            $list[$id] = $filterData;
        }

        if (!empty($filters)) {
            $this->load(array_shift($filters));
        } else {
            $this->setData(array());
            $this->prepareParams();
        }

        return $this;
    }

    /**
     * Retrieve filter data as array
     *
     * @return array
     */
    public function asArray()
    {
        $data = $this->getData();
        if ($this->_filters) {
            $data['filters'] = array();
            foreach ($this->_filters as $filter) {
                $data['filters'][] = $filter->asArray();
            }
        }

        // not required for save
        unset($data['new_child']);

        return $data;
    }

    /**
     * Retrieve filter data as json
     *
     * @return array
     */
    public function asJson()
    {
        return Zend_Json::encode($this->asArray());
    }

    /**
     * Retrieve data for export
     *
     * @return array
     */
    public function export()
    {
        return $this->asArray();
    }

    /**
     * Retrieve human readable filter text
     *
     * @param string $format
     *
     * @return string
     */
    public function asString($format = 'html')
    {
        $form = $this->getForm();
        $renderer = $form->getElementRenderer();

        if ($renderer instanceof Mzax_Emarketing_Block_Editable) {
            $renderer->setFormat($format);
        }

        $html = $this->_getFormHtml($form);

        return $html;
    }

    /**
     * Retrieve filter as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getFormHtml();

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        $result = $this->checkIndexes(false);
        if (is_string($result)) {
            $html .= '<div class="maax-index-check">' . $result . '</div>';
        }

        $html .= $this->getChooserContainerHtml();

        $prefix = $this->getRoot()->getFormPrefix();

        if (count($this->getAvailableFilters())) {
            $html .= '<ul id="'.$prefix.'__'.$this->getId().'__children" class="rule-param-children">';
            foreach ($this->_filters as $filter) {
                $html .= '<li>'.$filter->asHtml().'</li>';
            }
            $html .= '<li>'.$this->getNewChildElement()->getHtml().'</li></ul>';
        }

        return $html;
    }

    /**
     * Retrieve form html
     *
     * @return string
     */
    public function getFormHtml()
    {
        $form = $this->getForm();

        $typeField = $form->addField('type', 'hidden', array(
            'name'    => 'type',
            'class'   => 'hidden',
            'no_span' => true,
            'is_meta' => true,
            'value'   => $this->getType()
        ));

        $html  = $typeField->toHtml();
        $html .= $this->_getFormHtml($form);

        return $html;
    }

    /**
     * @param Varien_Data_Form $form
     *
     * @return string
     */
    protected function _getFormHtml(Varien_Data_Form $form)
    {
        $this->_form = $form;

        return $this->prepareForm();
    }

    /**
     * Prepare form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return '';
    }

    /**
     * Retrieve chooser container html
     *
     * @return string
     */
    public function getChooserContainerHtml()
    {
        $url = $this->getData('chooser_url');
        if ($url) {
            /** @var Mage_Adminhtml_Helper_Data $helper */
            $helper = Mage::helper('adminhtml');
            $url = $helper->getUrl($url);

            return '<div class="rule-chooser" url="' . $url . '"></div>';
        }

        return '';
    }

    /**
     * Retrieve title
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Run filter query and return result
     *
     * This method should only used for testing
     * and debugging
     *
     * @return array
     */
    public function runFilterQuery()
    {
        $select = $this->getSelect();

        $sql = $select->assembleAll();
        $result = $this->_getReadAdapter()->query($sql);

        return $result;
    }

    /**
     * @param $provider
     */
    public function beforeLoad($provider)
    {
    }

    /**
     * @param $provider
     */
    public function afterLoad($provider)
    {
    }

    /**
     * Get add sub-filter link html
     *
     * @return string
     */
    protected function getAddLinkHtml()
    {
        $src = Mage::getDesign()->getSkinUrl('mzax/images/add-tiny.png');
        $html = '<img src="' . $src . '" class="rule-param-add v-middle" alt="" title="' . $this->__('Add Filter') . '"/>';

        return $html;
    }

    /**
     * Get remove filter link html
     *
     * @return string
     */
    protected function getRemoveLinkHtml()
    {
        $src = Mage::getDesign()->getSkinUrl('mzax/images/delete-tiny.png');
        $html = ' <span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove" title="'.$this->__('Remove Filter').'"><img src="'.$src.'"  alt="" class="v-middle" /></a></span>';
        return $html;
    }

    /**
     * Add choose filter link html
     *
     * @return string
     */
    public function getChooserTriggerHtml()
    {
        $src = Mage::getDesign()->getSkinUrl('images/rule_chooser_trigger.gif');
        $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' . $src . '" alt="" class="v-middle rule-chooser-trigger" title="' . $this->__('Open Chooser') . '" /></a>';
        return $html;
    }

    /**
     * Prepare recipient collection
     *
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     *
     * @return void
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
    }

    //--------------------------------------------------------------------------
    //
    //  Quick Helpers
    //
    //--------------------------------------------------------------------------

    /**
     * Helper for new child filter element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function getNewChildElement()
    {
        $path    = $this->getTypePath() . '-';

        $filters = array();
        foreach ($this->getAvailableFilters() as $type => $title) {
            $title = $this->_explode($title, '|');

            $list =& $filters;

            while (count($title) > 1) {
                $n = array_shift($title);
                if (!isset($list[$n])) {
                    $list[$n] = array('label' => $n, 'value' => array());
                }
                $list =& $list[$n]['value'];
            }

            $list[] = array('value' => $path . $type, 'label' => array_shift($title));
        }

        array_unshift($filters, array('value'=>'', 'label' => $this->__('Please choose a filter to add...')));

        return $this->getForm()->addField('new_child', 'select', array(
            'name'       => 'new_child',
            'values'     => $filters,
            'value_name' => $this->getAddLinkHtml(),
        ))->setRenderer(Mage::getBlockSingleton('rule/newchild'));
    }

    /**
     * Helper for simple input element
     *
     * @param string $key
     * @param null $default
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function getInputElement($key, $default = null)
    {
        $value = (string) $this->getDataSetDefault($key, $default);

        return $this->getForm()->addField($key, 'text', array(
            'name'       => $key,
            'value_name' => $value,
            'value'      => $value
        ));
    }

    /**
     * Add hidden input field
     *
     * @param string $name
     * @param string $value
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function getHiddenField($name, $value)
    {
        return $this->getForm()->addField(
            $name,
            'hidden',
            array(
                'name'    => $name,
                'class'   => 'hidden',
                'no_span' => true,
                'is_meta' => true,
                'value'   => $value
            )
        );
    }

    /**
     * Helper for simple select element
     *
     * @param string $key
     * @param null $default
     * @param array $options
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function getSelectElement($key, $default = null, $options = null)
    {
        if (empty($options)) {
            $options = $this->getDataUsingMethod($key . '_options');
        }

        $value = $this->getDataSetDefault($key, $default);
        if (is_array($value)) {
            $value = $value[0];
        }
        $valueName = '';
        if (isset($options[$value])) {
            $valueName = $options[$value];
        }

        return $this->getForm()->addField($key, 'select', array(
            'name'       => $key,
            'value_name' => ($valueName ? $valueName : '...'),
            'value'      => $value,
            'values'     => $options
        ));
    }

    /**
     * Helper for simple select element
     *
     * @param string $key
     * @param array $default
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getDateElement($key, $default = null)
    {
        $format = Varien_Date::DATE_INTERNAL_FORMAT;
        $value  = (string) $this->getDataSetDefault($key, $default);

        if (!$value) {
            $value = Zend_Date::now()->toString($format);
        }

        $value = Mage::app()->getLocale()->date($value, $format, null, false)->toString($format);

        return $this->getForm()->addField($key, 'date', array(
            'name'           => $key,
            'value_name'     => $value,
            'value'          => $value,
            'explicit_apply' => true,
            'image'          => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'input_format'   => $format,
            'format'         => $format
        ));
    }

    /**
     * Helper for simple select element
     *
     * @param string $key
     * @param null $default
     * @param array $optionHash
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function getMultiSelectElement($key, $default = null, $optionHash = null)
    {
        if (empty($optionHash)) {
            $optionHash = $this->getDataUsingMethod($key . '_options');
        }

        $values = $this->getDataSetDefault($key, $default);
        if (is_string($values)) {
            $values = $this->_explode($values);
        }
        if (empty($values)) {
            $values = array();
        }

        $valueName = array();
        $options = array();
        foreach ($optionHash as $value => $option) {
            $options[] = array('value' => $value, 'label' => $option);
            if (in_array($value, $values)) {
                $valueName[] = $option;
            }
        }

        return $this->getForm()->addField($key, 'multiselect', array(
            'name'       => $key,
            'value_name' => implode(', ', $valueName),
            'value'      => $values,
            'values'     => $options
        ));
    }

    /**
     * Helper for simple time html
     *
     * The time html consists of two form fields
     * a select for the unit and the value (e.g. 5 days, 6 weeks)
     *
     * @param string $key
     * @param string $html
     *
     * @return string
     */
    protected function getTimeHtml($key, $html = "%s %s")
    {
        $valueKey = "{$key}_value";
        $unitKey  = "{$key}_unit";

        $unitElement = $this->getSelectElement($unitKey, 'days', $this->helper()->getTimeUnitOptions());
        $valueElement = $this->getInputElement($valueKey);

        return $this->__(
            $html,
            $valueElement->toHtml(),
            $unitElement->toHtml()
        );
    }

    /**
     * Helper for simple time html
     *
     * The time html consists of two form fields
     * a select for the unit and the value (e.g. 5 days, 6 weeks)
     *
     * @param string $key
     * @param string $html
     *
     * @return string
     */
    protected function getTimeRangeHtml($key, $html = "%s to %s %s")
    {
        $fromKey = "{$key}_from";
        $toKey   = "{$key}_to";
        $unitKey = "{$key}_unit";

        $unitElement = $this->getSelectElement($unitKey, 'days', $this->helper()->getTimeUnitOptions());
        $fromElement = $this->getInputElement($fromKey, 2);
        $toElement   = $this->getInputElement($toKey, 5);

        return $this->__(
            $html,
            $fromElement->toHtml(),
            $toElement->toHtml(),
            $unitElement->toHtml()
        );
    }

    /**
     * Retrieve time direction as boolean value
     * true  => for future
     * false => for past
     *
     * @param string $key
     * @param string $default
     *
     * @return boolean
     */
    protected function getTimeDirection($key, $default = null)
    {
        $dir = (string) $this->getDataSetDefault("{$key}_dir", $default);

        return ($dir === 'future');
    }

    /**
     * @param string $key
     *
     * @return mixed|string
     */
    protected function getTimeDirectionHtml($key)
    {
        $dirKey = "{$key}_dir";

        $unitElement = $this->getSelectElement($dirKey, 'future', $this->getTimeDirectionOptions());

        return $unitElement->getHtml();
    }

    /**
     * Time direction options
     *
     * @return string[]
     */
    protected function getTimeDirectionOptions()
    {
        return array(
            'future' => $this->__('is in'),
            'past'   => $this->__('is past'),
        );
    }

    /**
     * Retrieve time range expression from two time expr values
     *
     *           value      unit            value      unit
     *             |         |                |         |
     * Last login [5] [days|weeks|months] to [7] [days|weeks|months] ago
     *            \________FROM_________/    \_________TO__________/
     *
     *
     * ...WHERE `field` BETWEEN `from` AND `to`...
     *
     * @param string|Zend_Db_Expr $field
     * @param $key
     * @param boolean $future
     * @param bool $dateOnly
     * @param bool $useLocalTime
     *
     * @return Zend_Db_Expr
     * @throws Exception
     */
    protected function getTimeRangeExpr($field, $key, $future = null, $dateOnly = false, $useLocalTime = false)
    {
        $now = $this->getCurrentTime(!$useLocalTime);

        $fromKey = "{$key}_from";
        $toKey   = "{$key}_to";
        $unitKey = "{$key}_unit";

        if ($future === null) {
            $future = $this->getTimeDirection($key);
        }

        $from = (float)  $this->getData($fromKey);
        $to   = (float)  $this->getData($toKey);
        $unit = (string) $this->getData($unitKey);
        // days => DAY, weeks => WEEK,...
        $unit = substr(strtoupper($unit), 0, -1);

        if (!preg_match('/^[A-Z]+$/', $unit)) {
            throw new Exception("Invalid time unit ($unit)");
        }

        $func = $future ? 'DATE_ADD' : 'DATE_SUB';

        $limits = array();
        if (is_array($now)) {
            foreach ($now as $date) {
               // $date = $gmtOffset ? $this->addGmtOffset($date) : $date;
                $limits[] = "$func($date, INTERVAL $from $unit)";
                $limits[] = "$func($date, INTERVAL $to $unit)";
            }
        } else {
            //$now = $gmtOffset ? $this->addGmtOffset($now) : $date;
            $limits[] = "$func($now, INTERVAL $from $unit)";
            $limits[] = "$func($now, INTERVAL $to $unit)";
        }

        $limits = implode(', ', $limits);

        if ($dateOnly) {
            return new Zend_Db_Expr("$field BETWEEN DATE(LEAST($limits)) AND DATE(GREATEST($limits))");
        }

        return new Zend_Db_Expr("$field BETWEEN LEAST($limits) AND GREATEST($limits)");
    }

    /**
     * Retrieve anniversary expression
     *
     * @param string $field
     * @param string $key
     * @param boolean $future
     * @param boolean $fieldIsLocalTime
     *
     * @return Zend_Db_Expr
     */
    protected function getAnniversaryTimeExpr($field, $key, $future = false, $fieldIsLocalTime = false)
    {
        // if data field is in local time, don't do any conversion
        if (!$fieldIsLocalTime) {
            $localField = $this->toLocalTime($field);
        } else {
            $localField = $field;
        }

        $currentTime = $this->getCurrentTimeExpr(false, '%s', !$future);

        $age = "YEAR($currentTime) - YEAR($localField)";

        if ($future) {
            // if we look into the future we need to add one year if the day of the year is already past
            $age.= "+IF(DAYOFYEAR($currentTime) > DAYOFYEAR($localField),1,0)";
        } else {
            // if we look into the past we need to substract a year if the day of the year is in front
            $age.= "-IF(DAYOFYEAR($currentTime) < DAYOFYEAR($localField),1,0)";
        }

        $anniversary = "DATE_ADD($localField, INTERVAL $age YEAR)";
        $anniversary = "DATE($anniversary)";

        // it needs to be at least one year old
        $lastYear = $this->getCurrentTimeExpr(!$fieldIsLocalTime, 'DATE_SUB(%s, INTERVAL 6 MONTH)', $future);


        $expr[] = "$field <= DATE($lastYear)";
        $expr[] = $this->getTimeRangeExpr($anniversary, $key, $future, true, true);

        return new Zend_Db_Expr(implode(' AND ', $expr));
    }

    /**
     * Retrieve current time expression
     *
     * @param bool $gmt
     * @param string $format
     * @param bool $max
     *
     * @return mixed|Zend_Db_Expr
     */
    protected function getCurrentTimeExpr($gmt = true, $format = '%s', $max = false)
    {
        $results = array();
        foreach ($this->getCurrentTime($gmt) as $date) {
            $results[] = sprintf($format, $date);
        }
        if ($max) {
            return $this->getGreatestSql($results);
        }
        return $this->getLeastSql($results);
    }

    /**
     * @param array $data
     *
     * @return mixed|Zend_Db_Expr
     */
    protected function getLeastSql(array $data)
    {
        if (count($data) > 1) {
            return $this->_getReadAdapter()->getLeastSql($data);
        }
        return $data[0];
    }

    /**
     * @param array $data
     *
     * @return mixed|Zend_Db_Expr
     */
    protected function getGreatestSql(array $data)
    {
        if (count($data) > 1) {
            return $this->_getReadAdapter()->getGreatestSql($data);
        }
        return $data[0];
    }

    /**
     * Add gmt offset if available
     *
     * @param string $dateExpr
     * @return string
     */
    protected function addGmtOffset($dateExpr)
    {
        $gmtOffset = (int) $this->getParam('gmt_offset', 0);
        if ($gmtOffset) {
            $dateExpr = "DATE_SUB($dateExpr, INTERVAL $gmtOffset MINUTE)";
        }
        return $dateExpr;
    }

    /**
     * Retrieve date value
     *
     * @param string $key
     * @param int $roundDate
     *
     * @return Zend_Date
     */
    protected function getTimeValue($key, $roundDate = 0)
    {
        return $this->helper()->calcDate(
            (int)$this->getData("{$key}_value"),
            (string)$this->getData("{$key}_unit"),
            $roundDate
        );
    }

    /**
     * @param string $key
     * @param string $field
     * @param bool $subtract
     *
     * @return Zend_Db_Expr
     */
    protected function getTimeExpr($key, $field, $subtract = false)
    {
        $value = (float)$this->getData("{$key}_value");
        $unit  = (string)$this->getData("{$key}_unit");

        $unit = substr(strtoupper($unit), 0, -1);

        $func = $subtract ? 'DATE_SUB' : 'DATE_ADD';

        return new Zend_Db_Expr("$func($field, INTERVAL $value $unit)");
    }

    /**
     * @param $key
     * @param string $type
     * @param null $default
     * @param null $html
     *
     * @return string
     */
    protected function getInputHtml($key, $type = "string", $default = null, $html = null)
    {
        $operations = $this->helper()->getOperatorOptionsByType($type);

        $defaultOperator = '{}';
        if ($type === 'numeric') {
            $defaultOperator = '==';
            if ($default === null) {
                $default = '1';
            }
            if ($html === null) {
                $html = '%s %s';
            }
        }

        if ($html === null) {
            $html = '%s "%s"';
        }

        $operatorElement = $this->getSelectElement($key . '_operator', $defaultOperator, $operations);
        $inputElement = $this->getInputElement($key, $default);

        return $this->__(
            $html,
            $operatorElement->toHtml(),
            $inputElement->toHtml()
        );
    }

    /**
     * @param string $key
     * @param string $field
     * @param null $quoteField
     *
     * @return string
     */
    protected function getWhereSql($key, $field, $quoteField = null)
    {
        $adapter  = $this->_getReadAdapter();

        if ($quoteField) {
            $field = str_replace('?', $adapter->quoteIdentifier($quoteField), $field);
        }

        $value    = $this->getData($key);
        $operator = $this->getDataSetDefault($key . '_operator', '{}');

        switch ($operator) {
            case '!=':
            case '>=':
            case '<=':
            case '>':
            case '<':
                return $adapter->quoteInto("{$field} {$operator} ?", $value);
            case '{}':
                return $adapter->quoteInto("{$field} LIKE ?", "%$value%");
            case '!{}':
                return $adapter->quoteInto("{$field} NOT LIKE ?", "%$value%");
            case '()':
                return $adapter->quoteInto("{$field} IN (?)", $this->_explode($value));
            case '!()':
                return $adapter->quoteInto("{$field} NOT IN (?)", $this->_explode($value));
            default:
                return $adapter->quoteInto("{$field} = ?", $value);
        }
    }

    /**
     * Check if where select would match zero
     *
     * @param string $key
     * @return boolean
     */
    protected function checkIfMatchZero($key)
    {
        return (bool) $this->_getReadAdapter()->fetchOne("SELECT ".$this->getWhereSql($key, '0'));
    }

    /**
     * @param $string
     * @param string $delimiter
     *
     * @return array
     */
    protected function _explode($string, $delimiter = ',')
    {
        if (is_array($string)) {
            return $string;
        }
        return array_map('trim', explode($delimiter, $string));
    }

    /**
     * @param $string
     * @param string $glue
     *
     * @return string
     */
    protected function _implode($string, $glue = ',')
    {
        if (is_array($string)) {
            return implode($glue, $string);
        }
        return $string;
    }

    /**
     * @return string[]
     */
    protected function getAggregatorOptions()
    {
        return array(
            'any'  => $this->__('ANY'),
            'all'  => $this->__('ALL'),
        );
    }

    /**
     * @return string[]
     */
    protected function getExpectationOptions()
    {
        return array(
            'true'  => $this->__('TRUE'),
            'false' => $this->__('FALSE'),
        );
    }

    /**
     * Use SQL UNION to combine all conditions into one
     * Select SQL
     *
     * @param array $conditions
     * @param string $aggregator
     * @param string $expectation
     *
     * @return Mzax_Emarketing_Db_Select
     */
    protected function _combineConditions($conditions, $aggregator, $expectation)
    {
        if (empty($conditions)) {
            return $this->getQuery()
                ->setColumn('matches', new Zend_Db_Expr('0'));
        }

        $negate = ($expectation === 'false');

        // if negate, add all options to query
        if ($negate) {
            $conditions[] = $this->getQuery();
        }

        $conditionCount = count($conditions);

        if ($conditionCount === 1) {
            $select = $conditions[0];
        } else {
            $select = $this->_select()->union($conditions, Zend_Db_Select::SQL_UNION_ALL);
        }

        $select = $this->_select($select, 'combine_union', $select::SQL_WILDCARD);
        $select->group();

        if ($negate) {
            $select->columns(array('matches' => "$conditionCount - COUNT(*)"));
        } else {
            $select->columns(array('matches' => 'COUNT(*)'));
        }

        if ($aggregator === 'all') {
            if ($negate) { // ALL ARE FALSE
                $select->having("COUNT(*) = ?", 1);
            } else {// ALL ARE TRUE
                $select->having("COUNT(*) = ?", $conditionCount);
            }
        } else {
            if ($negate) { // ANY ARE FALSE
                $select->having("COUNT(*) < ?", $conditionCount);
            } else { // ANY ARE TRUE
                //$select->having("COUNT(*) > ?", 0);
            }
        }

        return $select;
    }

    /**
     * Retrieve all filter selects from
     * all children
     *
     * @return array
     */
    protected function _getConditions()
    {
        $conditions = array();

        /* @var $filter Mzax_Emarketing_Model_Object_Filter_Abstract */
        foreach ($this->_filters as $filter) {
            if ($select = $filter->getSelect()) {
                $conditions[] = $select;
            }
        }
        return $conditions;
    }

    /**
     * Check database for indexes that the filter requires and
     * if possible create any missing indexes if
     * canCreateIndex() allows to do so.
     *
     * @param boolean $create Whether to try to create an index or not
     * @return true|string
     */
    public function checkIndexes($create = false)
    {
        return true;
    }

    /**
     * Check if filter can create indexes
     *
     * @return boolean
     */
    public function canCreateIndex()
    {
        return Mage::getStoreConfigFlag('mzax_emarketing/general/can_create_indexes');
    }
}

