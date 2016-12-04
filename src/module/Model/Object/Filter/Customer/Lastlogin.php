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
 * Class Mzax_Emarketing_Model_Object_Filter_Customer_Lastlogin
 */
class Mzax_Emarketing_Model_Object_Filter_Customer_Lastlogin
    extends Mzax_Emarketing_Model_Object_Filter_Customer_Abstract
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return "Customer | Last login";
    }

    /**
     * @param Mzax_Emarketing_Db_Select $query
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        $query->joinTable('customer_id', 'log/customer', 'log');

        if ($storeId = $this->getParam('store_id')) {
            $query->where('`log`.`store_id` = ?', $storeId);
        }

        $query->group();
        $query->having($this->getTimeRangeExpr('MAX(`log`.`login_at`)', 'login', false));
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     */
    protected function _prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::_prepareCollection($collection);
        $collection->addField('last_login', new Zend_Db_Expr('MAX(`log`.`login_at`)'));
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);

        $grid->addColumn('last_login', array(
            'header'    => $this->__('Last Login'),
            'width'     => '180px',
            'index'     => 'last_login',
            'gmtoffset' => true,
            'type'      =>'datetime'
        ));
    }

    /**
     * html for settings in option form
     *
     * @return string
     */
    protected function prepareForm()
    {
        return $this->__(
            'Customers last login was %s ago.',
            $this->getTimeRangeHtml('login')
        );
    }
}
