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
 * Class Mzax_Emarketing_Model_Resource_Recipient_Address
 */
class Mzax_Emarketing_Model_Resource_Recipient_Address extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initiate resources
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/recipient_address', 'address_id');
    }

    /**
     * Retrieve address by address id
     *
     * @param string $addressId
     * @return string
     */
    public function getAddress($addressId)
    {
        $adapter = $this->_getWriteAdapter();
        $table   = $this->getMainTable();

        $select = $adapter->select()
            ->from($table, 'address')
            ->where('address_id = ?', $addressId);

        return $adapter->fetchOne($select);
    }

    /**
     * Retreive address id from email address
     *
     * This will insert a new record if none was found
     *
     * @param string $address
     *
     * @return int
     */
    public function getAddressId($address)
    {
        $adapter = $this->_getWriteAdapter();
        $table  = $this->getMainTable();

        $address = strtolower($address);

        $select = $adapter->select()
            ->from($table, $this->getIdFieldName())
            ->where('address = ?', $address);

        $id = (int) $adapter->fetchOne($select);

        if (!$id) {
            $adapter->insert($table, array(
                'address' => $address,
                'exists'  => 1
            ));

            $id = (int) $adapter->lastInsertId($table);
        }
        return $id;
    }
}
