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
 * Listen to 'mzax_emarketing_email_filter_prepare_customer_events' to add
 * your own customer event filters
 *
 * @method string getName()
 * @method $this setName(string $value)
 * @method $this setAggregator(string $value)
 * @method $this setExpectation(string $value)
 * @method $this setEventDateTo(string $value)
 * @method $this setEventDateFrom(string $value)
 * @method $this setEventDateUnit(string $value)
 *
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Event
    extends Mzax_Emarketing_Model_Object_Filter_Customer_Abstract
{
    const DEFAULT_AGGREGATOR = 'all';
    const DEFAULT_EXPECTATION = 'true';

    /**
     * @var stdClass
     */
    protected $_event;

    /**
     * @var array
     */
    protected $_events = array();

    /**
     * @var array
     */
    protected $_eventTypeIds;

    /**
     * @var boolean
     */
    protected $_allowChildren = true;

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Customer";
    }

    /**
     * @param Mage_Core_Model_Config_Element $config
     *
     * @return void
     */
    protected function _prepareFilter(Mage_Core_Model_Config_Element $config)
    {
        parent::_prepareFilter($config);
        $this->_registerEvents();
        Mage::dispatchEvent("mzax_emarketing_email_filter_prepare_customer_events", array('filter' => $this));
    }

    /**
     * @return void
     */
    public function _registerEvents()
    {
        $view = $this->addEvent('catalog_product_view');
        $view->label = $this->__('Customer viewed product ... ago.');
        $view->form  = $this->__('Customer viewed product %s ago that matches %s of these conditions %s:');

        $friend = $this->addEvent('sendfriend_product');
        $friend->label = $this->__('Customer sent product to a friend ... ago.');
        $friend->form  = $this->__('Customer sent product to a friend %s ago that matches %s of these conditions %s:');

        $cart = $this->addEvent('checkout_cart_add_product');
        $cart->label = $this->__('Customer added product to cart ... ago.');
        $cart->form  = $this->__('Customer added product to cart %s ago that matches %s of these conditions %s:');

        $wishlist = $this->addEvent('wishlist_add_product');
        $wishlist->label = $this->__('Customer added product to wishlist ... ago.');
        $wishlist->form  = $this->__('Customer added product to wishlist %s ago that matches %s of these conditions %s:');

    }

    /**
     * Use product object
     *
     * @return Mzax_Emarketing_Model_Object_Product
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_product');
    }

    /**
     * @param Mzax_Emarketing_Db_Select $query
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $event = $this->getEvent();

        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator', self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);


        $query->useTemporaryTable($this->getTempTableName());
        $query->joinTable(array('subject_id' => '{customer_id}'), 'reports/event', 'event');
        if (!empty($conditions)) {
            $select = $this->_combineConditions($conditions, $aggregator, $expectation);
            $query->joinSelect(array('id' => '`event`.`object_id`'), $select, 'filter');
        }
        $query->where('`event`.`subtype` = 0'); // subject type = customer
        $query->where('`event`.`event_type_id` = ?', $event->typeId);
        $query->where($this->getTimeRangeExpr('`event`.`logged_at`', 'event_date', false));

        if ($storeId = $this->getParam('store_id')) {
            $query->where('`event`.`store_id` = ?', $storeId);
        }

        $query->group();
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('event_date', new Zend_Db_Expr('MAX(`event`.`logged_at`)'));
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);

        $grid->addColumn('event_date', array(
            'header'    => $this->__('Event Date'),
            'width'     => '180px',
            'index'     => 'event_date',
            'gmtoffset' => true,
            'type'      =>'datetime'
        ));
    }

    /**
     * Add new event
     *
     * @param string $eventName
     * @return stdClass
     */
    public function addEvent($eventName)
    {
        $event = new stdClass();
        $event->name = $eventName;
        $event->typeId = $this->getEventTypeId($eventName);
        $this->_events[$eventName] = $event;
        return $event;
    }

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $event = $this->getEvent();

        if (!$event) {
            return $this->__("No such event defined (%s)", $this->getName());
        }

        $aggregatorElment  = $this->getSelectElement('aggregator', self::DEFAULT_AGGREGATOR);
        $expectationElment = $this->getSelectElement('expectation', self::DEFAULT_EXPECTATION);

        $html = $this->getHiddenField('name', $event->name)->toHtml();

        return $html . $this->__(
            $event->form,
            $this->getTimeRangeHtml('event_date'),
            $aggregatorElment->toHtml(),
            $expectationElment->toHtml()
        );
    }

    /**
     * Retrieve current event
     *
     * @return stdClass
     */
    public function getEvent()
    {
        if ($this->_event === null) {
            $eventName = $this->getName();
            if (isset($this->_events[$eventName])) {
                $this->_event = $this->_events[$eventName];
            }
        }
        return $this->_event;
    }

    /**
     *
     * (non-PHPdoc)
     * @see Mzax_Emarketing_Model_Object_Filter_Abstract::getOptions()
     */
    public function getOptions()
    {
        $title = $this->getTitle();
        $type  = $this->getType();
        $options = array();

        foreach ($this->_events as $event) {
            $options[$type.'?name=' . $event->name] = "{$title} | {$event->label}";
        }
        asort($options);

        return $options;
    }

    /**
     * Retrieve event type id by event name
     *
     * @param string $name
     *
     * @return int
     */
    protected function getEventTypeId($name)
    {
        $typeIds = $this->getEventTypeIds();

        if (isset($typeIds[$name])) {
            return (int) $typeIds[$name];
        }

        return 0;
    }

    /**
     * Retrieve event name => id hash
     *
     * @return array
     */
    protected function getEventTypeIds()
    {
        if ($this->_eventTypeIds === null) {
            $select = $this->_select('reports/event_type', null, array('event_name', 'event_type_id'));
            $this->_eventTypeIds = $this->_getReadAdapter()->fetchPairs($select);
        }

        return $this->_eventTypeIds;
    }
}
