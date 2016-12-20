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
 * Campaign Collection
 *
 * @method Mzax_Emarketing_Model_Campaign getItemById(mixed $id)
 */
class Mzax_Emarketing_Model_Resource_Campaign_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Collection Constructor
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/campaign');
    }

    /**
     * Id filter
     *
     * @param string[] $campaignIds
     *
     * @return $this
     */
    public function addIdFilter($campaignIds)
    {
        $this->addFieldToFilter('campaign_id', array('in' => $campaignIds));

        return $this;
    }

    /**
     * Filter archived campaigns
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function addArchiveFilter($flag = true)
    {
        $this->addFieldToFilter('archived', $flag ? 1 : 0);

        return $this;
    }

    /**
     * Filter running campaigns
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function addRunningFilter($flag = true)
    {
        $this->addFieldToFilter('running', $flag ? 1 : 0);
        if ($flag) {
            $now = now();
            $this->addFieldToFilter('start_at', array(array('lteq' => $now), array('null' => true)));
            $this->addFieldToFilter('end_at', array(array('gteq' => $now), array('null' => true)));
        }

        return $this;
    }

    /**
     * Filter campaigns that need to check for new recipients
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function addCheckFilter($flag = true)
    {
        $formula = "FLOOR(UNIX_TIMESTAMP(%s)/(60*`main_table`.`check_frequency`))";
        $formula = $flag
            ? "$formula != $formula"
            : "$formula = $formula";

        $this->getSelect()->where(sprintf($formula, '`main_table`.`last_check`', ''));

        return $this;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('campaign_id', 'name');
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('campaign_id', 'name');
    }
}
