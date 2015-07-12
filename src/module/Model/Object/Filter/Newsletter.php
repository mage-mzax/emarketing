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
        return $parent->hasBinding('customer_id', 'subscriber_id', 'subscriber_status');
    }
    
    
    
    
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {        
        $condition = $this->getDataSetDefault('condition', self::DEFAULT_CONDITION);
        $status    = $this->getDataSetDefault('status',    self::DEFAULT_STATUS);
        
        if(!$query->hasBinding('subscriber_status'))
        {
            if($query->hasBinding('subscriber_id')) {
                $query->joinTableLeft('subscriber_id', 'newsletter/subscriber', 'subscriber');
            }
            else if($query->hasBinding('customer_id')) {
                $query->joinTableLeft('customer_id', 'newsletter/subscriber', 'subscriber');
            }
            $query->addBinding('subscriber_status', 'subscriber.subscriber_status');
        }   

        $condition = $condition === 'is' ? '=' : '!=';
        
        $query->where("{subscriber_status} $condition ?", $status);
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
        $conditionElment = $this->getSelectElement('condition', self::DEFAULT_CONDITION);
        $subscribeElment = $this->getSelectElement('status', self::DEFAULT_STATUS);
        
        return $this->__('Newsletter subscription status %s %s.',
            $conditionElment->toHtml(),
            $subscribeElment->toHtml()
         );
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
    
    
    

}
