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
 * Class Mzax_Emarketing_Model_Inbox_Email_Pull_Storage
 */
class Mzax_Emarketing_Model_Inbox_Email_Pull_Storage
    extends Mzax_Emarketing_Model_Inbox_Email_Pull_Abstract
{
    /**
     * @var string
     */
    protected $_storageAdapter = 'Zend_Mail_Storage_Pop3';

    /**
     *
     * @var Zend_Mail_Storage_Abstract
     */
    protected $_storage;

    /**
     * Retrieve storage config settings
     *
     * @return array
     */
    public function getConfig()
    {
        $config = array();
        $config['host']     = Mage::getStoreConfig('mzax_emarketing/inbox/hostname');
        $config['user']     = Mage::getStoreConfig('mzax_emarketing/inbox/username');
        $config['password'] = Mage::getStoreConfig('mzax_emarketing/inbox/password');
        $config['port']     = Mage::getStoreConfig('mzax_emarketing/inbox/port');
        $config['ssl']      = Mage::getStoreConfigFlag('mzax_emarketing/inbox/ssl');

        return $config;
    }

    /**
     * Test connection
     *
     * @return bool
     */
    public function test()
    {
        try {
            $adapter = $this->getStorage();
            $adapter->countMessages();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Set storage adapter class
     *
     * @param string $adapter
     *
     * @return $this
     */
    public function setStorageAdapter($adapter)
    {
        if ($this->_storageAdapter !== $adapter) {
            $this->_storageAdapter = $adapter;
            $this->_storage = null;
        }

        return $this;
    }

    /**
     * Retrieve storage adapter
     *
     * @return Zend_Mail_Storage_Abstract
     * @throws Exception
     */
    public function getStorage()
    {
        if (!$this->_storage) {
            $adapter = Mage::getStoreConfig('mzax_emarketing/inbox/storage_type');
            if (!$adapter) {
                $adapter = $this->_storageAdapter;
            }

            if (empty($adapter) || !class_exists($adapter)) {
                throw new Exception("No such email storage adapter ($adapter)");
            }
            $this->_storage = new $adapter($this->getConfig());
            if (!$this->_storage instanceof Zend_Mail_Storage_Abstract) {
                throw new Exception("Storage adapter '$adapter' does not extend from 'Zend_Mail_Storage_Abstract'");
            }
        }
        return $this->_storage;
    }

    /**
     * Maximum size of email for retrieving.
     *
     * Prevent large emails from crashing the php script
     *
     * @return number
     */
    public function getMaxMessageSize()
    {
        $size = (float) Mage::getStoreConfig('mzax_emarketing/inbox/max_download_size');
        $size = max($size, 0.5);

        return min(1024*1024 * $size, 16777000 /* db limit */);
    }

    /**
     * Connect to storage and retrieve emails
     *
     * @param Mzax_Emarketing_Model_Inbox_Email_Collector $collector
     *
     * @return int
     * @throws Exception
     */
    public function pull(Mzax_Emarketing_Model_Inbox_Email_Collector $collector)
    {
        $adapter = $this->getStorage();
        $maxSize = $this->getMaxMessageSize();
        $messages = 0;

        foreach ($adapter->getUniqueId() as $uid) {
            try {
                $id = $adapter->getNumberByUniqueId($uid);

                $size    = $adapter->getSize($id);
                $header  = $adapter->getRawHeader($id);

                if ($size > $maxSize) {
                    $content = sprintf(
                        "Email content size (%s) exceeded the maximum content size of %s.",
                        $size,
                        $maxSize
                    );
                    Mage::helper('mzax_emarketing')->log($content);
                } else {
                    $content = $adapter->getRawContent($id);
                }

                $messages++;
                $collector->add($header, $content);
                $adapter->removeMessage($adapter->getNumberByUniqueId($uid));
            } catch (Exception $e) {
                if (Mage::getIsDeveloperMode()) {
                    throw $e;
                }
                Mage::logException($e);
            }
        }

        return $messages;
    }
}
