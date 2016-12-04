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
 * Aggregate last view for each recipient address which can
 * help later to retrieve stats for sendings for recipients
 * that have not yet viewed the campaign.
 */
class Mzax_Emarketing_Model_Report_Aggregator_Events
    extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    /**
     * @var string
     */
    protected $_reportTable = 'recipient_address';

    /**
     * @return void
     */
    protected function _aggregate()
    {
        $this->aggregateLastViewEvent();
    }

    /**
     * @return void
     */
    protected function aggregateLastViewEvent()
    {
        $select = $this->_select('recipient', 'recipient');
        $select->joinTable(array('recipient_id', 'event_type' => Mzax_Emarketing_Model_Recipient::EVENT_TYPE_VIEW), 'recipient_event', 'event');
        $select->addBinding('date_filter', 'sent_at');
        $select->setColumn('address_id', 'recipient.address_id');
        $select->setColumn('view_id', 'MAX(`event`.`event_id`)');

        $this->insertSelect($select);
    }
}
