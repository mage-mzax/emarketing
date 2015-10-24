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
 * 
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getName()
 * @method string getDescription()
 * @method string getIsPrivate()
 * @method string getAutoSubscribe()
 *
 * @method Mzax_Emarketing_Model_Resource_Newsletter_List getResource()
 * 
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Newsletter_List
    extends Mage_Core_Model_Abstract 
{
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_newsletter_list';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getList() in this case
     *
     * @var string
     */
    protected $_eventObject = 'list';
    
    
    
    

    protected function _construct()
    {
        $this->_init('mzax_emarketing/newsletter_list');
    }


    /**
     * @return Mzax_Emarketing_Model_Newsletter_List
     */
    protected function _afterSave()
    {
        if($this->isAutoSubscribe() && $this->isObjectNew()) {
            $this->addAllSubscribers();
        }

        return parent::_afterSave();
    }


    /**
     * Add all current subscribers to this list
     *
     * @return int
     */
    public function addAllSubscribers()
    {
        return $this->getResource()->addAllSubscribers($this);
    }


    /**
     * Remove all subscribers from this list
     *
     * @return int
     */
    public function removeAllSubscribers()
    {
        return $this->getResource()->removeAllSubscribers($this);
    }


    /**
     * Add subscribers to list
     *
     * @param $subscribers
     * @return int
     */
    public function addSubscribers($subscribers)
    {
        $subscribers = (array) $subscribers;
        return $this->getResource()->addSubscribers($this, $subscribers);
    }


    /**
     * Remove subscribers from list
     *
     * @param $subscribers
     * @return int
     */
    public function removeSubscribers($subscribers)
    {
        $subscribers = (array) $subscribers;
        return $this->getResource()->removeSubscribers($this, $subscribers);
    }


    
    /**
     * Check if list is private
     *
     * A private list should not be visible to the customer unless
     * they are subscribed.
     *
     * Only an admin can subscribe a subscriber to this list
     * 
     * @param string $value
     * @return boolean
     */
    protected function isPrivate($value = null)
    {
        if(is_bool($value)) {
            $this->setIsPrivate($value ? 1 : 0);
        }
        return (bool) $this->getIsPrivate();
    }



    /**
     * If auto subscribed all new subscribers will get added
     * to this list automatically.
     *
     * Also when list is created, all existing subscriber will
     * get added
     *
     * @param null $value
     * @return bool
     */
    protected function isAutoSubscribe($value = null)
    {
        if(is_bool($value)) {
            $this->setAutoSubscribe($value ? 1 : 0);
        }
        return (bool) $this->getAutoSubscribe();
    }



}
