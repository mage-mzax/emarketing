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
 * Class Mzax_Emarketing_Model_Resource_Inbox_Email
 */
class Mzax_Emarketing_Model_Resource_Inbox_Email extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/inbox_email', 'email_id');
    }


    /**
     * Retrieve email content file
     *
     * @param $id
     *
     * @return string
     * @throws Exception
     */
    public function getContentFile($id)
    {
        $path[] = Mage::getBaseDir('var');
        $path[] = 'mzax_emarketing';
        $path[] = 'inbox';
        $path[] = $id . '.mail';
        $path = implode(DS, $path);

        if (!Mage::getConfig()->createDirIfNotExists(dirname($path))) {
            throw new Exception("Unable to create dir: ".dirname($path));
        }

        return $path;
    }

    /**
     * Perform actions after object delete
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $file = $this->getContentFile($object->getId());
        if (file_exists($file)) {
            @unlink($file);
        }
        return parent::_afterDelete($object);
    }

    /**
     * @param int[] $mails
     *
     * @return int
     */
    public function massDelete($mails)
    {
        $mails = (array)$mails;
        $rows = $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array('email_id IN(?)' => $mails)
        );

        foreach ($mails as $id) {
            $file = $this->getContentFile($id);
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        return $rows;
    }

    /**
     * @param $emails
     * @param null $type
     *
     * @return int
     */
    public function massTypeChange($emails, $type = null)
    {
        switch ($type) {
            case Mzax_Emarketing_Model_Inbox_Email::AUTOREPLY:
            case Mzax_Emarketing_Model_Inbox_Email::BOUNCE_HARD:
            case Mzax_Emarketing_Model_Inbox_Email::BOUNCE_SOFT:
            case Mzax_Emarketing_Model_Inbox_Email::NO_BOUNCE:
                break;
            default:
                $type = null;
        }

        $rows = $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('type' => $type),
            array(
                'email_id IN(?)' => $emails,
                'type != ?' => $type
            )
        );

        return $rows;
    }

    /**
     * Prepare data for save
     *
     * @param   Mage_Core_Model_Abstract $object
     * @return  array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt(now());
        }
        $object->setUpdatedAt(now());
        $data = parent::_prepareDataForSave($object);
        return $data;
    }

    /**
     * Flag message as parsed
     *
     * @param int $email
     *
     * @return $this
     */
    public function flagAsParsed($email)
    {
        if ($email instanceof Varien_Object) {
            $email = $email->getId();
        }

        $email = (int)$email;
        if ($email) {
            $this->_getWriteAdapter()
                ->update(
                    $this->getMainTable(),
                    array('is_parsed' => 1),
                    "email_id = $email"
                );
        }

        return $this;
    }

    /**
     * @param $headers
     * @param $content
     *
     * @throws Exception
     */
    public function insertEmail($headers, $content)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $adapter->insert($this->getMainTable(), array(
                'created_at' => now(),
                'headers'    => $headers
            ));
            $id = $this->_getWriteAdapter()->lastInsertId($this->getMainTable());
            $file = $this->getContentFile($id);
            if (file_put_contents($file, $content) === false) {
                throw new Exception("Failed to save email content to file '$file'");
            }
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
    }

    /**
     * Remove content of old emails that is not required anymore
     *
     * Once messages are parsed and forwarded we can delete the content
     * as it is not required anymore.
     *
     * However leave the row-entry as it is still relevant for reporting
     *
     * @param int $purgeDays
     *
     * @return $this
     */
    public function purge($purgeDays = 30)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'email_id')
            ->where('purged = 0')
            ->where('created_at <= DATE_SUB(NOW(), INTERVAL ? DAY)', $purgeDays);

        $ids = $this->_getReadAdapter()->fetchCol($select);
        if (empty($ids)) {
            return $this;
        }

        foreach ($ids as $id) {
            $file = $this->getContentFile($id);
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'headers' => null,
                'message' => new Zend_Db_Expr('IF(`type` = "NB", SUBSTRING(`message`, 1, 200), NULL)'),
                'purged'  => 1
            ),
            array(
                'email_id IN(?)' => $ids
            )
        );

        return $this;
    }
}
