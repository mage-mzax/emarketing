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
 * Class Mzax_Emarketing_Model_Medium_Email_FullCache
 */
class Mzax_Emarketing_Model_Medium_Email_FullCache
    implements Mzax_Emarketing_Model_Campaign_Content
{
    /**
     * @var string
     */
    protected $_cacheId;

    /**
     * @var Varien_Object
     */
    protected $_mediumData;

    /**
     * Retrieve cache id
     *
     * return string
     */
    public function getContentCacheId()
    {
        return $this->_cacheId;
    }

    /**
     * Set cache id
     *
     * @param $id
     *
     * @return $this
     */
    public function setContentCacheId($id)
    {
        $this->_cacheId = $id;

        return $this;
    }

    /**
     * Retrieve content data
     *
     * @return Varien_Object
     */
    public function getMediumData()
    {
        return $this->_mediumData;
    }

    /**
     * Set medium data
     *
     * @param Varien_Object $data
     *
     * @return $this
     */
    public function setMediumData(Varien_Object $data)
    {
        $this->_mediumData = $data;

        return $this;
    }
}
