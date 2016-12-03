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



class Mzax_Emarketing_Model_Report_Aggregator_Dimension extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    
    const TABLE_REPORT = 'report_dimension';
    const TABLE_CONVERSION = 'report_dimension_conversion';
    
    
    protected $_dimensions = array(
        'dayofweek',
        'hour',
        'useragent',
        'deviceType',
        'deviceBrand',
        'os',
        'country',
        'region',
    );
    
    
    
    protected function _aggregate()
    {
        $filter = $this->_options->getDimension();
        
        if ($this->getOption('full', false)) {
            $this->truncateTable(self::TABLE_REPORT);
            $this->truncateTable(self::TABLE_CONVERSION);
        }
        else {
            if ($trackerId = $this->getOption('tracker_id')) {
                $this->delete(array('`tracker_id` IN(?)' => $trackerId), self::TABLE_CONVERSION);
            }
            if ($campaignId = $this->getOption('campaign_id')) {
                $this->delete(array('`campaign_id` IN(?)' => $campaignId), self::TABLE_REPORT);
                $this->delete(array('`campaign_id` IN(?)' => $campaignId), self::TABLE_CONVERSION);
            }
            if ($incremental = abs($this->getOption('incremental'))) {
                $where = "`date` >= DATE_SUB(NOW(), INTERVAL $incremental DAY)";
                $this->delete($where, self::TABLE_REPORT);
                $this->delete($where, self::TABLE_CONVERSION);
            }
        }
        foreach ($this->_dimensions as $type) {
            if (empty($filter) || in_array($type, $filter)) {
                $dimension = $this->getDimension($type);
                $dimension->aggregate($this->_options);
                $this->_options->getLock()->touch();
            }
        }
    }
    
    /**
     * 
     * @param string $type
     * @return Mzax_Emarketing_Model_Report_Aggregator_Dimension_Abstract
     */
    public function getDimension($type)
    {
        return Mage::getSingleton('mzax_emarketing/report_aggregator_dimension_' . $type);
    }
    
    
    
}
