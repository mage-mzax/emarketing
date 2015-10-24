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
 * Simple newsletter status filter
 * 
 * @method Mzax_Emarketing_Model_Object_Filter_Newsletter setCondition(string $value)
 * @method Mzax_Emarketing_Model_Object_Filter_Newsletter setStatus(string $value)
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Object_Filter_Newsletter_List
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    
    const DEFAULT_STATUS    = Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
    const DEFAULT_CONDITION = 'is';


    /**
     * @return string
     */
    public function getTitle()
    {
        return "Newsletter | Subscriber belongs to list";
    }


    /**
     * @param Mzax_Emarketing_Model_Object_Filter_Component $parent
     * @return bool
     */
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->hasBinding('customer_id', 'subscriber_id');
    }



    /**
     * @param Mzax_Emarketing_Db_Select $query
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {        
        $condition = $this->getDataSetDefault('condition', self::DEFAULT_CONDITION);
        $status    = $this->getDataSetDefault('status',    self::DEFAULT_STATUS);


        if($query->hasBinding('subscriber_id')) {
            $query->joinTableLeft('subscriber_id', 'mzax_emarketing/newsletter_list_subscriber', 'list_subscriber');
        }
        else if($query->hasBinding('customer_id')) {
            $query->addBinding('subscriber_id', 'subscriber.subscriber_id');
            $query->joinTable('customer_id', 'newsletter/subscriber', 'subscriber');
            $query->joinTable('subscriber_id', 'mzax_emarketing/newsletter_list_subscriber', 'list_subscriber');
        }

        $listIds = $this->_explode($this->getLists());
        $query->where("`list_subscriber`.`list_status` = ?", Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
        $query->where("`list_subscriber`.`list_id` IN(?)", $listIds);
        $query->group();
        $query->addBinding('list_id', 'list_subscriber.list_id');
    }



    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->getQuery()->joinTable('list_id', 'mzax_emarketing/newsletter_list', 'list');
        $collection->addField('newsletter_lists', new Zend_Db_Expr('GROUP_CONCAT(`list`.`name` SEPARATOR ", ")'));
    }


    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     * @throws Exception
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);

        $grid->addColumn('newsletter_lists', array(
            'header'    => $this->__('Lists'),
            'index'     => 'newsletter_lists',
        ));

    }



    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        $listElement      = $this->getMultiSelectElement('lists');

        return $this->__('Subscriber belongs to one of the following lists: %s.',
            $listElement->toHtml()
         );
    }


    /**
     * @return array
     */
    protected function getListsOptions()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Newsletter_List_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
        return $collection->toOptionHash();
    }



}
