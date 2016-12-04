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
 * Class Mzax_Emarketing_Model_Resource_Link
 */
class Mzax_Emarketing_Model_Resource_Link extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initiate resources
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/link', 'link_id');
    }

    /**
     * Prepare data for save
     *
     * @param   Mage_Core_Model_Abstract $object
     * @return  array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $object->setLinkHash($this->_mkHash($object->getUrl(), $object->getAnchor()));

        $data = parent::_prepareDataForSave($object);
        return $data;
    }

    /**
     * Update optout flag using array(id=>flag)
     *
     * @param array $data
     * @throws Exception
     *
     * @return Mzax_Emarketing_Model_Resource_Link
     */
    public function updateOptoutFlag(array $data)
    {
        $enable = array();
        $disable = array();

        foreach ($data as $id => $flag) {
            $id = (int) $id;
            if ($id) {
                if ($flag) {
                    $enable[] = $id;
                } else {
                    $disable[] = $id;
                }
            }
        }
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            if (!empty($enable)) {
                $this->setOptoutFlag($enable, true);
            }
            if (!empty($disable)) {
                $this->setOptoutFlag($disable, false);
            }
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Set optout flag on given link ids
     *
     * @param array $ids
     * @param bool $flag
     *
     * @return $this
     */
    public function setOptoutFlag($ids, $flag)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('optout' => $flag?1:0),
            $this->_getWriteAdapter()->quoteInto('link_id IN(?)', $ids)
        );

        return $this;
    }

    /**
     * Load Link by url and anchor text
     *
     * @param Mzax_Emarketing_Model_Link $object
     * @param string $url
     * @param $anchor
     *
     * @return $this
     */
    public function loadByUrl(Mzax_Emarketing_Model_Link $object, $url, $anchor)
    {
        $select = $this->_getLoadSelect('link_hash', $this->_mkHash($url, $anchor), $object);

        $data = $this->_getReadAdapter()->fetchRow($select);
        if ($data) {
            $object->setData($data);
        } else {
            $object->setUrl($url);
            $object->setAnchor($anchor);
        }
        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Make link hash
     *
     * @param string $url
     * @param string $anchor
     *
     * @return string
     */
    protected function _mkHash($url, $anchor)
    {
        return md5($url . $anchor);
    }
}
