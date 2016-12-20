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
 * Goal observer helper abstract
 */
class Mzax_Emarketing_Model_Observer_Goal
    extends Mzax_Emarketing_Model_Observer_Abstract
{
    /**
     * A goal type id pre defined
     *
     * 1 = orders
     * 2 = signups
     *
     * If you plan on implementing custom types
     * use a type id greater than 100
     *
     * @var integer
     */
    protected $_goalType;

    /**
     * Carefully handle save events
     *
     * @event sales_order_save_before
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function beforeSave(Varien_Event_Observer $observer)
    {
        try {
            $object = $observer->getEvent()->getDataObject();
            if ($object && method_exists($this, '_beforeSave')) {
                $this->_beforeSave($object);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Carefully handle save events
     *
     * @event sales_order_save_after
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function afterSave(Varien_Event_Observer $observer)
    {
        try {
            $object = $observer->getEvent()->getDataObject();
            if ($object && method_exists($this, '_afterSave')) {
                $this->_afterSave($object);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Check before save is object is new and set a flag
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return void
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->isObjectNew() && $object->getSaveMzaxGoalReferences() === null) {
            // only if object is new
            $object->setSaveMzaxGoalReferences(true);
        }
    }

    /**
     * Save goals for this object
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return void
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getSaveMzaxGoalReferences()) {
            return;
        }

        // only do it once
        $object->setSaveMzaxGoalReferences(false);

        $clicks = $this->getSession()->getClickReferences();

        $data = array();
        foreach ($clicks as $click) {
            $data[] = array(
                'object_type'  => $this->_goalType,
                'object_id'    => $object->getId(),
                'recipient_id' => $click['recipient_id'],
                'click_id'     => $click['click_id'],
            );
        }

        if (!empty($data)) {
            /* @see Mzax_Emarketing_Model_Resource_Goal */
            Mage::getResourceModel('mzax_emarketing/goal')->insertMultiple($data);
        }
    }

    /**
     *
     *
     * @event mzax_emarketing_bind_recipients
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function bindRecipients(Varien_Event_Observer $observer)
    {
        /* @var $binder Mzax_Emarketing_Model_Resource_Recipient_Goal_Binder */
        $binder = $observer->getEvent()->getData('binder');

        if ($binder->hasBinding('ORDER')) {
            $binding = $binder->createBinding();
            $binding->joinTable(
                array(
                    'object_type' => Mzax_Emarketing_Model_Goal::TYPE_ORDER,
                    'object_id'   => '{ORDER}'
                ),
                'goal'
            );
            $binding->joinTable(array('recipient_id' => '{recipient_id}'), 'recipient');
            $binding->addBinding('recipient_id', 'recipient.recipient_id');
            $binding->addBinding('sent_at', 'recipient.sent_at');
            $binding->addBinding('variation_id', 'recipient.variation_id');
            $binding->addBinding('campaign_id', 'recipient.campaign_id');
        }
    }
}
