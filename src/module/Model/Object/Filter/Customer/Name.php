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
 * Class Mzax_Emarketing_Model_Object_Filter_Customer_Name
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Name
    extends Mzax_Emarketing_Model_Object_Filter_Customer_Abstract
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return "Customer | Full Name";
    }

    /**
     * @param Mzax_Emarketing_Db_Select $query
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $firstname = $query->joinAttribute('customer_id', 'customer/firstname');
        $lastname  = $query->joinAttribute('customer_id', 'customer/lastname');

        $query->where($this->getWhereSql('name', "CONCAT_WS(' ', $firstname, $lastname)"));
    }

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return $this->__(
            'Customer name %s.',
            $this->getInputHtml('name')
        );
    }
}
