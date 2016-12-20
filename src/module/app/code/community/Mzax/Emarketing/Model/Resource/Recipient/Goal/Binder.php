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
 * Class Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
 */
class Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
{
    /**
     *
     * @var Mzax_Emarketing_Db_Select
     */
    protected $_select;

    /**
     * List of all registered selects
     *
     * @var array
     */
    protected $_bindings = array();

    /**
     * Set base select
     *
     * @param Mzax_Emarketing_Db_Select $select
     * @return Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder
     */
    public function setSelect(Mzax_Emarketing_Db_Select $select)
    {
        $this->_select = $select;
        return $this;
    }

    /**
     * Check if binder has the specified binding
     *
     * @param string $name
     * @return boolean
     */
    public function hasBinding($name)
    {
        if ($this->_select) {
            return $this->_select->hasBinding($name);
        }
        return false;
    }

    /**
     * Create new binding select
     *
     * @return Mzax_Emarketing_Db_Select
     * @throws Exception
     */
    public function createBinding()
    {
        if (!$this->_select) {
            throw new Exception("Unable to create binding, no select defined");
        }
        $select = clone $this->_select;
        $this->_bindings[] = $select->lock();

        return $select;
    }

    /**
     * Create new binding select
     *
     * @return Mzax_Emarketing_Db_Select
     * @throws Exception
     * @throws Mzax_Db_Select_Exception
     */
    public function getSelect()
    {
        $unions = array();

        /* @var $select Mzax_Emarketing_Db_Select */
        foreach ($this->_bindings as $select) {
            // skip if not locked
            if (!$select->locked()) {
                continue;
            }
            // skip if assemble fails
            try {
                $select->assemble();
            } catch (Mzax_Db_Select_Exception $e) {
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
            } catch (Exception $e) {
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }

                $message = "Failed to assamble goal binder select:";
                $message.= $e->getMessage() . "\n";
                $message.= $e->getTraceAsString();

                Mage::log($message, Zend_Log::WARN);
                Mage::logException($e);
                continue;
            }

            $unions[] = $select;
        }

        switch (count($unions)) {
            case 0:
                return null;
            case 1:
                return $unions[0];
        }

        $select = $this->getResourceHelper()->select();
        $select->union($unions, Zend_Db_Select::SQL_UNION);

        return $select;
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Resource_Helper
     */
    protected function getResourceHelper()
    {
        return Mage::getResourceSingleton('mzax_emarketing/helper');
    }
}
