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
 * Class Mzax_Emarketing_Model_Link
 *
 * @method $this setLinkGroupId(string $value)
 * @method $this setLinkHash(string $value)
 * @method $this setUrl(string $value)
 * @method $this setAnchor(string $value)
 * @method $this setOptout(bool $value)
 *
 * @method string getLinkGroupId()
 * @method string getLinkHash()
 * @method string getUrl()
 * @method string getAnchor()
 * @method string getOptout()
 *
 * @method Mzax_Emarketing_Model_Resource_Link getResource()
 */
class Mzax_Emarketing_Model_Link extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_link';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'link';

    /**
     * Model Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/link');
    }

    /**
     * Try loading url model by url
     *
     * @param string $url
     * @param string $anchor
     *
     * @return $this
     */
    public function loadByUrl($url, $anchor)
    {
        $this->getResource()->loadByUrl($this, $url, $anchor);

        return $this;
    }

    /**
     * Load or initialize new link instance
     *
     * @param string $url
     * @param string $anchor
     *
     * @return $this
     */
    public function init($url, $anchor)
    {
        $this->loadByUrl($url, $anchor);

        if (!$this->getId()) {
            if (preg_match('!unsubscribe!i', $url.$anchor)) {
                $this->setOptout(true);
            }
            $this->save();
        }

        return $this;
    }
}
