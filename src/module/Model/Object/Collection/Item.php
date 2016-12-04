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
 * Collection item
 *
 * @see Mzax_Emarketing_Model_Object_Collection::getNewEmptyItem()
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Model_Object_Collection_Item extends Varien_Object
{

    /**
     *
     * @var Mzax_Emarketing_Model_Object_Abstract
     */
    protected $_object;


    /**
     * Set Object
     *
     * @param Mzax_Emarketing_Model_Object_Abstract $object
     * @return Mzax_Emarketing_Model_Recipient_Object
     */
    public function setObject($object)
    {
        $this->_object = $object;
        return $this;
    }



    /**
     * Retrieve Object
     *
     * @return Mzax_Emarketing_Model_Object_Abstract
     */
    public function getObject()
    {
        return $this->_object;
    }

}
