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
 * Class Mzax_Emarketing_Model_Report_Aggregator_Dimension_Dayofweek
 */
class Mzax_Emarketing_Model_Report_Aggregator_Dimension_Dayofweek
    extends Mzax_Emarketing_Model_Report_Aggregator_Dimension_Abstract
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return "dayofweek";
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return array('Monday', 'Thuesday', 'Wednesday',
                     'Thursday', 'Friday', 'Saturday', 'Sunday');
    }

    /**
     * @return string[]
     */
    public function getSqlValues()
    {
        return array(
            '2' => 'Monday',
            '3' => 'Thuesday',
            '4' => 'Wednesday',
            '5' => 'Thursday',
            '6' => 'Friday',
            '7' => 'Saturday',
            '1' => 'Sunday'
        );
    }

    /**
     * @param Mzax_Emarketing_Db_Select $select
     */
    protected function prepareAggregationSelect(Mzax_Emarketing_Db_Select $select)
    {
        $select->addBinding('value', 'DAYOFWEEK({local_date})');
    }
}
