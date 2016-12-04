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
 * Class Mzax_Emarketing_Model_Report_Aggregator_Dimension_Useragent
 */
class Mzax_Emarketing_Model_Report_Aggregator_Dimension_Useragent
    extends Mzax_Emarketing_Model_Report_Aggregator_Dimension_Abstract
{
    /**
     * @var
     */
    protected $_values;

    /**
     * @var string
     */
    protected $_column = 'ua';

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_column;
    }

    /**
     * @return void
     */
    protected function _aggregate()
    {
        if (!$this->_options->getTrackerId()) {
            $this->aggregateSendings();
            $this->aggregateEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_VIEW, 'views');
            $this->aggregateEvent(Mzax_Emarketing_Model_Recipient::EVENT_TYPE_CLICK, 'clicks');
        }
    }

    /**
     * Add last available view event_id
     * We don't have any useragent data available just by sending out
     * an email. However we might have data from previous campaigns that we can borrow.
     *
     * We use the address ID to search crosswide around all address
     *
     * @todo Add a time limit so we don't use to old data
     * @param Mzax_Emarketing_Db_Select $select
     */
    protected function joinLastViewEvent(Mzax_Emarketing_Db_Select $select)
    {
        if ($select->hasAnyBindings('event_id', 'useragent_id')) {
            return;
        }

        $select->joinTable('address_id', 'recipient_address', 'address');
        $select->joinTable(array('event_id' => '`address`.`view_id`'), 'recipient_event', 'last_view_event');

        $select->addBinding('event_id', 'last_view_event.event_id');
        $select->addBinding('useragent_id', 'last_view_event.useragent_id');
    }

    /**
     * @return Mzax_Emarketing_Db_Select
     */
    protected function getAggregateSendingsSelect()
    {
        // @todo this needs to be tested
        $select = parent::getAggregateSendingsSelect();

        $this->joinLastViewEvent($select);

        $select->joinTable(array('useragent_id' => '{useragent_id}'), 'useragent', 'ua');

        return $select;
    }

    /**
     * @param int $eventType
     * @param string $fieldName
     *
     * @return Mzax_Emarketing_Db_Select
     */
    protected function getAggregateEventSelect($eventType, $fieldName)
    {
        $select = parent::getAggregateEventSelect($eventType, $fieldName);
        $select->joinTable(array('useragent_id' => 'event.useragent_id'), 'useragent', 'ua');
        $select->where("`ua`.`{$this->_column}` IS NOT NULL");

        return $select;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        if (!$this->_values) {
            $select = $this->_select('useragent', null, $this->_column)->distinct();
            $select->where('ua IS NOT NULL');
            $this->_values = $this->_getWriteAdapter()->fetchCol($select);
            $this->_values = array_filter($this->_values);
        }
        return $this->_values;
    }

    /**
     * @param Mzax_Emarketing_Db_Select $select
     */
    protected function prepareAggregationSelect(Mzax_Emarketing_Db_Select $select)
    {
        $select->addBinding('value', $this->_column, 'ua');
    }
}
