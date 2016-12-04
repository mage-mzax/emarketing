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
 * Class Mzax_Emarketing_Model_Object_Filter_Order_Campaign
 *
 * @method $this setJoin(string $value)
 * @method $this setCampaign(Mzax_Emarketing_Model_Campaign|string $value)
 *
 */
class Mzax_Emarketing_Model_Object_Filter_Order_Campaign
    extends Mzax_Emarketing_Model_Object_Filter_Order_Abstract
{

    const DEFAULT_JOIN = 'direct';

    const DEFAULT_CAMPAIGN = 'current';

    const DEFAULT_AGGREGATOR = 'all';

    const DEFAULT_EXPECTATION = 'true';


    protected $_allowChildren = true;


    /**
     *
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;


    public function getTitle()
    {
        return "Order | Link directly/indirectly to emarketing campaign";
    }



    /**
     *
     * @return Mzax_Emarketing_Model_Object_Recipient
     */
    public function getObject()
    {
        return Mage::getSingleton('mzax_emarketing/object_recipient');
    }




    public function getQuery()
    {
        $campaign = $this->getCampaign();

        $query = $this->getObject()->getQuery();
        if ($campaign) {
            $query->joinSelect('recipient_id', $this->getRecipientsByOrder(), 'recipient_order');
            $query->where('{campaign_id} = ?', $campaign->getId());
        }
        $query->addBinding('goal_id', 'order_id', 'recipient_order');
        $query->addBinding('goal_time', 'order_time', 'recipient_order');
        $query->setColumn('recipient_id');
        $query->setColumn('goal_id');

        if (($variationId = $this->getVariation()) !== null) {
            $query->where('{variation_id} = ?', $variationId);
        }

        return $query;

    }




    /**
     *
     * @return Zend_Db_Select
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $conditions  = $this->_getConditions();
        $aggregator  = $this->getDataSetDefault('aggregator',  self::DEFAULT_AGGREGATOR);
        $expectation = $this->getDataSetDefault('expectation', self::DEFAULT_EXPECTATION);

        $select = $this->_combineConditions($conditions, $aggregator, $expectation);

        $query->joinSelect(array('goal_id' => '{order_id}'), $select, 'recipients');
        $query->group();

        $query->provide('recipient_id', new Zend_Db_Expr('MAX(`recipients`.`id`)'));
    }



    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('recipient_ids', new Zend_Db_Expr('GROUP_CONCAT(`recipients`.`id` SEPARATOR ", ")'));
    }





    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);

        $grid->addColumn('recipient_ids', array(
            'header' => $this->__('Link Recipients'),
            'index'  => 'recipient_ids',
        ));
    }









    /**
     * Retrieve recipients by order select
     *
     * @return Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
     */
    public function getRecipientsByOrder()
    {
        $campaign = $this->getCampaign();
        $type = $this->getDataSetDefault('join');

        $binder = Mage::getSingleton('mzax_emarketing/conversion_goal_orders')->getRecipientBinder($campaign, $type === 'direct');

        return $binder->getSelect();
    }








    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return $this->__('Order is linked %s to emarketing campaign %s',
            $this->getSelectElement('join')->toHtml(),
            $this->getSelectElement('campaign')->toHtml()
         );
    }




    public function getJoinOptions()
    {
        return array(
            'direct'   => $this->__('directly'),
            'indirect' => $this->__('indirectly'),
        );
    }



    public function getCampaignOptions()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $collection->addArchiveFilter(false);

        $options = array();
        if ($this->getParam('tracker') instanceof Mzax_Emarketing_Model_Conversion_Tracker) {
            $options['current'] = $this->__('beeing tracked');
        }
        $options += $collection->toOptionHash();

        return $options;
    }





    /**
     * Retreive current campaign
     *
     * We must always return a campaign object!
     * getQuery() needs to work with no data set
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {

            $id = $this->getDataSetDefault('campaign');

            if ($id === 'current') {
                return $this->getParam('campaign');
            }
            $campaign = Mage::getModel('mzax_emarketing/campaign');
            if (!$id) {
                return null;
            }
            $campaign->load($id);
            if (!$campaign->getId()) {
                return null;
            }

            $this->_campaign = $campaign;
        }
        return $this->_campaign;
    }


    /**
     * Retreive current campaign variation which we aggregate
     *
     * @return null|integer
     */
    public function getVariation()
    {
        $id =  Mage::registry('mzax_aggregate_variation');
        if ($id !== null && $id > -1) {
            return (int) $id;
        }
        return null;
    }






}
