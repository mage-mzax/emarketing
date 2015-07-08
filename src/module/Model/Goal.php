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
 * @method Mzax_Emarketing_Model_Link setObjectId(string)
 * @method Mzax_Emarketing_Model_Link setObjectType(string)
 * @method Mzax_Emarketing_Model_Link setRecipientId(string)
 * @method Mzax_Emarketing_Model_Link setClickId(string)
 * 
 * @method string getObjectId()
 * @method string getObjectType()
 * @method string getRecipientId()
 * @method string getClickId()
 * 
 * @method Mzax_Emarketing_Model_Resource_Goal getResource()
 * 
 * 
 * If you plan on implmenting custom events, use a type id greater than 100
 * 
 * 
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Goal extends Mage_Core_Model_Abstract 
{
    
    const TYPE_ORDER  = 1;
    const TYPE_SIGNUP = 2;
    
    
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_goal';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'goal';
    
    
    
    
    protected function _construct()
    {
        $this->_init('mzax_emarketing/goal');
    }
    
    
    
    
    
}
