<?php
/**
 * Auto generated file by ClassMocker, do not change
 *
 * @author ClassMocker
 * @mock
 */


use JSiefer\MageMock\Mage\Mage_Core_Model_Abstract as Trait_65ef8c1671_Mage_Core_Model_Abstract;

class Mage_Core_Model_Abstract extends Varien_Object
{

    use Trait_65ef8c1671_Mage_Core_Model_Abstract {
        Trait_65ef8c1671_Mage_Core_Model_Abstract::save as __trait_65ef8c1671_Mage_Core_Model_AbstractSave;
        Trait_65ef8c1671_Mage_Core_Model_Abstract::load as __trait_65ef8c1671_Mage_Core_Model_AbstractLoad;
        Trait_65ef8c1671_Mage_Core_Model_Abstract::delete as __trait_65ef8c1671_Mage_Core_Model_AbstractDelete;

    }

    private static $___classMocker_traitMethods = array(
        'save' => array(
            '__trait_65ef8c1671_Mage_Core_Model_AbstractSave',
        ),
        'load' => array(
            '__trait_65ef8c1671_Mage_Core_Model_AbstractLoad',
        ),
        'delete' => array(
            '__trait_65ef8c1671_Mage_Core_Model_AbstractDelete',
        ),
    );

    /**
     * Delicate save() to __call() method
     */
    public function save()
    {
        return $this->___classMocker_call("save", func_get_args());
    }

    /**
     * Delicate load() to __call() method
     */
    public function load($id, $field = null)
    {
        return $this->___classMocker_call("load", func_get_args());
    }

    /**
     * Delicate delete() to __call() method
     */
    public function delete()
    {
        return $this->___classMocker_call("delete", func_get_args());
    }


}

