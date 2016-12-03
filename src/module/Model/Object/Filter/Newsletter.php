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
class Mzax_Emarketing_Model_Object_Filter_Newsletter
    extends Mzax_Emarketing_Model_Object_Filter_Abstract
{
    
    const DEFAULT_STATUS    = Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
    const DEFAULT_CONDITION = 'is';
    

    
    
    public function getTitle()
    {
        return "Newsletter | Subscription Status";
    }
    
    
    
    public function acceptParent(Mzax_Emarketing_Model_Object_Filter_Component $parent)
    {
        return $parent->hasBinding('email', 'customer_id', 'subscriber_id', 'subscriber_status');
    }
    
    
    
    
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {        
        $condition = $this->getDataSetDefault('condition', self::DEFAULT_CONDITION);
        $status    = $this->getDataSetDefault('status',    self::DEFAULT_STATUS);
        
        if (!$query->hasBinding('subscriber_status'))
        {
            if ($query->hasBinding('subscriber_id')) {
                $query->joinTableLeft('subscriber_id', 'newsletter/subscriber', 'subscriber');
            }
            else if ($query->hasBinding('email')) {
                $query->joinTableLeft(array('subscriber_email' => 'email'), 'newsletter/subscriber', 'subscriber');
            }

            else if ($query->hasBinding('customer_id')) {
                $query->joinTableLeft('customer_id', 'newsletter/subscriber', 'subscriber');
            }
            $query->addBinding('subscriber_status', 'subscriber.subscriber_status');
            $query->addBinding('subscriber_store', 'subscriber.store_id');
            $query->group();
        }   

        if ($condition === 'is') {
            $query->where("{subscriber_status} = ?", $status);
        }
        else {
            $query->where("{subscriber_status} != ? OR {subscriber_status} IS NULL", $status);
        }


        if (Mage::getStoreConfigFlag('mzax_emarketing/general/newsletter_multistore'))
        {
            $storeId = (int) $this->getStore();
            if ($storeId && $query->hasBinding('subscriber_store')) {
                $query->where("{subscriber_store} = ?", $storeId);
            }
        }

    }
    
    
    
    
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('newsletter_status', 'subscriber_status');
    }
    
    
    
    
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
    
        $grid->addColumn('newsletter_status', array(
            'header'    => $this->__('Newsletter'),
            'width'     => '80px',
            'index'     => 'newsletter_status',
            'align'     => 'center',
            'type'      => 'options',
            'options'   => array_map('ucwords', $this->getStatusOptions())
        ));
    
    }
    

    
    
    
    
    

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {    
        $conditionElement = $this->getSelectElement('condition', self::DEFAULT_CONDITION);
        $subscribeElement = $this->getSelectElement('status', self::DEFAULT_STATUS);
        $storeElement = $this->getSelectElement('store', '0');
        
        return $this->__('Newsletter subscription status for %s %s %s.',
            $storeElement->toHtml(),
            $conditionElement->toHtml(),
            $subscribeElement->toHtml()
         );
    }
    


    protected function getStoreOptions()
    {
        $options = array(
            '0' => $this->__('any store')
        );

        /* @see Mage_Adminhtml_Model_System_Config_Source_Store */
        $stores = Mage::getSingleton('adminhtml/system_config_source_store')->toOptionArray();
        foreach ($stores as $store) {
            $options[$store['value']] = $store['label'];
        }

        return $options;
    }
    
    
    
    protected function getStatusOptions()
    {
        return array(
            Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED    => $this->__('subscribed'),
            Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED  => $this->__('unsubscribed'),
            Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE    => $this->__('not activated'),
            Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED   => $this->__('unconfirmed')
        );
    }
    
    
    
    protected function getConditionOptions()
    {
        return array(
            'is'      => $this->__('is'),
            'is_not'  => $this->__('is not')
        );
    }






    /**
     * The newsletter table is missing an index for the email
     *
     */
    public function checkIndexes($create = false)
    {
        $adapter = $this->_getWriteAdapter();


        $table = $this->_getTable('newsletter/subscriber');

        $indexList = $adapter->getIndexList($table);

        // check if we already created an index
        if (isset($indexList['MZAX_IDX_EMAIL'])) {
            return true;
        }

        // check for other indexes that can work
        foreach ($indexList as $index) {
            switch(count($index['fields'])) {
                case 1:
                    if ($index['fields'][0] === 'subscriber_email') {
                        return true;
                    }
                    break;
            }
        }


        if ($create && $this->canCreateIndex()) {
            try {
                $adapter->addIndex($table, 'MZAX_IDX_EMAIL', array('subscriber_email'));
                return true;
            }
            catch(Exception $e) {
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
                return $this->__('Failed to create an index for the table "%s". Please check logs.', $table);
            }
        }
        else if ($this->canCreateIndex()) {
            return true;
        }

        return $this->__('It is recommended to set an index on "subscriber_email" for the table "%s" before using this filter.', $table);
    }




}
