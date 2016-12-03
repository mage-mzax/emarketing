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



abstract class Mzax_Emarketing_Model_Conversion_Goal_Abstract
    extends Mzax_Emarketing_Model_Object_Filter_Main
{

    protected $_type;


    /**
     *
     * @var Mzax_Emarketing_Model_Conversion_Tracker
     */
    protected $_tracker;



    abstract public function getAggregationSelect($campaign);


    /**
     * Set default filters when newly created
     *
     * @return void
     */
    public function setDefaultFilters()
    {}


    public function __construct($config)
    {
        parent::__construct();

        if ($config instanceof Mage_Core_Model_Config_Element) {
            $this->_setup($config);
        }
    }


    protected function _setup(Mage_Core_Model_Config_Element $config)
    {
        $this->_type = $config->getName();

        Mage::dispatchEvent("mzax_emarketing_conversion_goal_setup", array('goal' => $this));
        Mage::dispatchEvent("mzax_emarketing_conversion_goal_setup_" . $this->_type, array('goal' => $this));
    }


    public function getType()
    {
        return $this->_type;
    }



    /**
     * Set Tracker
     *
     * @param Mzax_Emarketing_Model_Conversion_Tracker $tracker
     * @param $validateFilters
     * @return Mzax_Emarketing_Model_Conversion_Goal_Abstract
     */
    public function setTracker($tracker, $validateFilters = false)
    {
        $this->_tracker = $tracker;
        $this->load($tracker->getFilterData(), !$validateFilters);
        $this->setParam('tracker', $tracker);

        return $this;
    }



    /**
     * Retrieve Tracker
     *
     * @return Mzax_Emarketing_Model_Conversion_Tracker
     */
    public function getTracker()
    {
        return $this->_tracker;
    }


    public function getFormPrefix()
    {
        return 'conditions';
    }







    public function getQuery()
    {
        $query = parent::getQuery();
        $query->seek('recipient_id');

        return $query;
    }

    /**
     *
     * @return Zend_Db_Select
     */
    protected function _prepareQuery(Mzax_Emarketing_Db_Select $query)
    {
        parent::_prepareQuery($query);
        $query->addBinding('recipient_id', new Zend_Db_Expr('MIN(`filter`.`recipient_id`)'));
        $query->setColumn('recipient_id');
    }



    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        parent::prepareGridColumns($grid);
        $grid->addColumn('recipient_id', array(
            'header'    => $this->__('Recipient Id'),
            'width'     => '100px',
            'index'     => 'recipient_id'
        ));
    }



}
