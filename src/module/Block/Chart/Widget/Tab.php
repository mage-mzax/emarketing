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
 *
 * @method Mzax_Emarketing_Model_Campaign getCampaign()
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Block_Chart_Widget_Tab extends Mage_Adminhtml_Block_Abstract
{

    /**
     *
     * @var Mzax_Emarketing_Block_Chart_Abstract
     */
    protected $_chart;


    /**
     *
     * @var Mzax_Emarketing_Model_Report_Query
     */
    protected $_query;







    protected $_tabs = array();


    protected static $_uid = 1;




    public function getHtmlId()
    {
        $id = $this->getData('html_id');
        if (!$id) {
            $id = 'charttab_' . (self::$_uid++);
            $this->setData('html_id', $id);
        }
        return $id;
    }




    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mzax/emarketing/widget/chart-tab.phtml');
    }



    public function addTab($id, $params)
    {
        $this->_tabs[$id] = $params;
        return $this;
    }

    public function getTabs()
    {
        return $this->_tabs;
    }



    /**
     * Retrieve query
     *
     * @return Mzax_Emarketing_Model_Report_Query
     */
    public function getQuery()
    {
        return $this->_query;
    }


    /**
     * Set query
     *
     * @param Mzax_Emarketing_Model_Report_Query $query
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function setQuery(Mzax_Emarketing_Model_Report_Query $query)
    {
        $this->_query = $query;
        return $this;
    }



    public function getType()
    {
        return $this->getDataSetDefault('type', 'line');
    }




    /**
     *
     * @return Mzax_Emarketing_Block_Chart_Abstract
     */
    public function getChart()
    {
        if (!$this->_chart) {


            $block = 'mzax_emarketing/chart_' . $this->getType();

            /* @var $chart Mzax_Emarketing_Block_Chart_Abstract */
            $chart = $this->_chart = $this->getLayout()->createBlock($block);
            $chart->setBackgroundColor('#f5f5f5');
            $chart->setHAxis(array(
                'baselineColor' => 'white',
                'gridlines' => array(
                    'color' => '#DDD'
                ),
                'textPosition'=> 'in',
                'textStyle' => array(
                    'fontSize' => 11,
                    'color' => '#333'
                )
            ));
            $chart->setVAxis(array(
                'baselineColor' => 'white',
                'gridlines' => array(
                    'color' => '#DDD'
                ),
                'textPosition'=> 'in',
                'textStyle' => array(
                    'fontSize' => 11,
                    'color' => '#333'
                )
            ));
            $chart->setBar(array(
                'groupWidth' => '80%'
            ));
            $chart->setChartArea(array(
                'top' => '5%',
                'right' => '0',
                'bottom' => '0',
                'left' => '0',
                'width' => '100%',
                'height' => '100%'
            ));
            $chart->setLegend('none');
            $chart->setHeight(200);

        }
        return $this->_chart;
    }







    /**
     *
     * @return Mzax_Chart_Table
     */
    public function getTable()
    {
        return $this->getQuery()->getDataTable();
    }




    /**
     * Retrieve query params as JSON
     *
     * @return string
     */
    public function getQueryParams()
    {
        return Zend_Json::encode($this->getQuery()->getParams());
    }



    public function getQueryUrl()
    {
        $url = $this->getData('query_url');
        if (!$url) {
            $url = $this->getUrl('*/*/queryReport', array(
                '_current' => true,
                'form_key'  => Mage::getSingleton('core/session')->getFormKey()
            ));
        }
        return $url;
    }






    public function getTabMetric(array $tab)
    {
        if (isset($tab['metric'])) {
            if (is_array($tab['metric'])) {
                if (isset($tab['default'])) {
                    return $tab['default'];
                }
                return reset(array_keys($tab['metric']));
            }
            return $tab['metric'];
        }
        return '';
    }


    public function getTabLabel(array $tab)
    {
        if (isset($tab['label'])) {
            return $tab['label'];
        }
        return '';
    }


    public function getTabClass(array $tab)
    {
        $class = array();
        if (isset($tab['id'])) {
            $class[] = $tab['id'];
        }
        if (isset($tab['class'])) {
            $class[] = $tab['class'];
        }
        if (isset($tab['metric'])) {
            $class[] = is_string($tab['metric']) ? $tab['metric'] : 'dropdown';
        }


        return implode(' ', array_unique($class));
    }




}
