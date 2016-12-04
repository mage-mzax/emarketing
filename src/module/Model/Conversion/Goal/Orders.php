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
 * Class Mzax_Emarketing_Model_Conversion_Goal_Orders
 */
class Mzax_Emarketing_Model_Conversion_Goal_Orders
    extends Mzax_Emarketing_Model_Conversion_Goal_Abstract
{
    /**
     * Retrieve goal title
     *
     * @return string
     */
    public function getTitle()
    {
        return "Magento Orders";
    }

    /**
     * Set default filters when newly created
     *
     * @return void
     */
    public function setDefaultFilters()
    {
        /* @var $campaignFilter Mzax_Emarketing_Model_Object_Filter_Order_Campaign */
        $campaignFilter = $this->addFilter('order_campaign');
        if ($campaignFilter) {
            $campaignFilter->setCampaign($campaignFilter::DEFAULT_CAMPAIGN);
            $campaignFilter->setJoin($campaignFilter::DEFAULT_JOIN);

            /* @var $goalFilter Mzax_Emarketing_Model_Object_Filter_Campaign_Goal */
            $goalFilter = $campaignFilter->addFilter('campaign_goal');
            if ($goalFilter) {
                $goalFilter->setAction($goalFilter::ACTION_CLICKED);
                $goalFilter->setOffsetValue(5);
                $goalFilter->setOffsetUnit('days');
            }
        }

        /* @var $tableFilter Mzax_Emarketing_Model_Object_Filter_Order_Table */
        $tableFilter = $this->addFilter('order_table');
        if ($tableFilter) {
            $tableFilter->setColumn('status');
            $tableFilter->setOperator('()');
            $tableFilter->setValue(Mage_Sales_Model_Order::STATE_COMPLETE);
        }
    }

    /**
     * Retrieve object
     *
     * @return Mzax_Emarketing_Model_Object_Order
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_order');
    }

    /**
     * Retrieve aggregation select statement
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return Mzax_Emarketing_Db_Select
     */
    public function getAggregationSelect($campaign)
    {
        $this->setParam('campaign', $campaign);

        $query = $this->getObject()->getQuery();
        $query->addBinding('date_filter', 'created_at');
        $query->joinSelect('id', $this->getSelect(), 'filter');
        $query->joinSelectLeft('order_id', $this->getRecipientBinder($campaign, false)->getSelect(), 'recipients');
        $query->group('order_id');
        $query->reset(Zend_Db_Select::COLUMNS);
        $query->setColumn('tracker_id', new Zend_Db_Expr($this->getTracker()->getId()));
        $query->setColumn('campaign_id', new Zend_Db_Expr($campaign->getId()));
        $query->setColumn('goal_id', 'order_id');
        $query->setColumn('goal_time', 'ordered_at');
        $query->setColumn('goal_value', 'base_grand_total');

        $query->setColumn('recipient_id', 'IFNULL(MAX(`filter`.`recipient_id`), MAX(`recipients`.`recipient_id`))');

        return $query;
    }

    /**
     * Get goal <-> recipient binder
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     * @param boolean $direct
     * @param integer|bool $variation
     *
     * @return Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
     */
    public function getRecipientBinder($campaign, $direct = false, $variation = false)
    {
        $select = $this->_select('sales/order', 'order');
        $select->addBinding('order_id', 'entity_id');
        $select->addBinding('order_time', 'created_at');
        $select->addBinding('customer_id', 'customer_id');
        $select->addBinding('email', 'customer_email');
        $select->addBinding('quote_id', 'quote_id');
        $select->addBinding('campaign', new Zend_Db_Expr($campaign->getId()));

        $select->where('{campaign_id} = ?', $campaign->getId());
        $select->where('{order_time} > ?', $campaign->getCreatedAt());
        $select->where('{order_time} > {sent_at}');
        $select->where('{is_mock} = 0');

        $select->setColumn('order_id');
        $select->setColumn('order_time');
        $select->setColumn('recipient_id');

        if ($variation !== false) {
            $select->where('{variation_id} = ?', $variation);
        }

        /* @var $binder Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder */
        $binder = Mage::getResourceModel('mzax_emarketing/recipient_goal_binder');
        $binder->setSelect($select);

        // create a direct binding using the goal table
        $sql = $binder->createBinding();
        $sql->joinTable(
            array(
                'object_type' => Mzax_Emarketing_Model_Goal::TYPE_ORDER,
                'object_id'   => '{order_id}'
            ),
            'goal'
        );
        $sql->joinTable(array('recipient_id' => '{recipient_id}'), 'recipient');
        $sql->addBinding('recipient_id', 'goal.recipient_id');
        $sql->addBinding('sent_at', 'recipient.sent_at');
        $sql->addBinding('variation_id', 'recipient.variation_id');
        $sql->addBinding('campaign_id', 'recipient.campaign_id');
        $sql->addBinding('is_mock', 'recipient.is_mock');

        if (!$direct) {
            // see if we can get any indirect bindings via email etc
            $campaign->bindRecipients($binder);
        }

        Mage::dispatchEvent(
            'mzax_emarketing_order_recipient_binder',
            array(
                'binder'   => $binder,
                'direct'   => $direct,
                'campaign' => $campaign
            )
        );

        return $binder;
    }
}
