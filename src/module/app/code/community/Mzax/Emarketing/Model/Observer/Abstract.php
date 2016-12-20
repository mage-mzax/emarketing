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
 * Class Mzax_Emarketing_Model_Observer_Abstract
 */
abstract class Mzax_Emarketing_Model_Observer_Abstract
{
    /**
     * Session Manager
     *
     * @var Mzax_Emarketing_Model_SessionManager
     */
    protected $_sessionManager;

    /**
     * Store configuration
     *
     * @var Mzax_Emarketing_Model_Config
     */
    protected $_config;

    /**
     * Model factory
     *
     * @var Mzax_Emarketing_Model_Factory
     */
    protected $_factory;

    /**
     * Mzax_Emarketing_Model_Observer_Abstract constructor.
     */
    public function __construct()
    {
        $this->_sessionManager = Mage::getSingleton('mzax_emarketing/sessionManager');
        $this->_factory = Mage::getSingleton('mzax_emarketing/factory');
        $this->_config = Mage::getSingleton('mzax_emarketing/config');
    }

    /**
     * Retrieve session object model
     *
     * @return Mzax_Emarketing_Model_Session
     */
    public function getSession()
    {
        return $this->_sessionManager->getSession();
    }
}
