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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_Report
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_Report extends Mage_Adminhtml_Block_Widget
{
    const COLOR_BLANK       = 'E7EFEF';
    const COLOR_SENDINGS    = '899DA8';
    const COLOR_VIEWS       = '159FC4';
    const COLOR_CLICKS      = 'D7D020';
    const COLOR_ORDERS      = 'A9C200';
    const COLOR_CONVERSIONS = 'A9C200';
    const COLOR_OPTOUT      = 'BF3A3A';
    const COLOR_BOUNDS      = '712B2B';

    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;

    /**
     * @var
     */
    protected $_dateRange;

    /**
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('campaign_report_tab');
        $this->setTemplate('mzax/emarketing/campaign/report.phtml');
    }

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {
            $this->_campaign = Mage::registry('current_campaign');
        }
        return $this->_campaign;
    }

    /**
     * Set campaign
     *
     * @param Mzax_Emarketing_Model_Campaign $campaign
     *
     * @return Mzax_Emarketing_Block_Campaign_Edit_Tab_Report
     */
    public function setCampaign(Mzax_Emarketing_Model_Campaign $campaign)
    {
        $this->_campaign = $campaign;
        return $this;
    }

    /**
     * Has campaign any trackers
     *
     * @return boolean
     */
    public function hasTracker()
    {
        return (bool) count($this->getCampaign()->getTrackers());
    }

    /**
     * Query report data tables
     *
     * @param string $dimension
     * @param array $metrics
     * @param bool $variations
     * @param string $order
     *
     * @return Mzax_Emarketing_Model_Report_Query
     */
    public function queryReport($dimension, $metrics, $variations, $order = null)
    {
        $query = $this->getCampaign()->queryReport($dimension, $metrics, $variations, $order);
        $query->setParam('date_range', $this->getDateRange());

        return $query;
    }

    /**
     * Retrieve date range from request
     *
     * @return boolean|array
     */
    public function getDateRange()
    {
        if ($this->_dateRange === null) {
            $validator = new Zend_Validate_Date;

            $from = $this->getRequest()->getParam('from');
            $to   = $this->getRequest()->getParam('to');

            if ($validator->isValid($from) && $validator->isValid($to)) {
                $this->_dateRange = array($from, $to);
            } else {
                $this->_dateRange = false;
            }
        }

        return $this->_dateRange;
    }

    /**
     * Retrieve query from local cache or create new
     *
     * @param string $key
     * @param string $dimension
     * @param array $metrics
     * @param bool $variations
     * @param string $order
     *
     * @return Mzax_Emarketing_Model_Report_Query
     */
    public function getCachedQuery($key, $dimension, $metrics, $variations, $order = null)
    {
        $query = $this->getData('query_' . $key);
        if (!$query) {
            $query = $this->queryReport($dimension, $metrics, $variations, $order);
            $this->setData('query_' . $key, $query);
        }

        return $query;
    }

    /**
     *
     * @return Mzax_Emarketing_Model_Report_Query
     */
    public function getTotals()
    {
        return $this->getCachedQuery(
            'totals',
            'campaign',
            array('sendings', 'views', 'clicks', 'bounces', 'optouts', 'conversion' => '#?'),
            false
        );
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return (int) $this->getTotals()->getCell('sendings');
    }

    /**
     * Prepare chart data table by providing correct
     * column lables and colors
     *
     * @param Mzax_Chart_Table $table
     *
     * @return void
     */
    public function prepareTable(Mzax_Chart_Table $table)
    {
        $variations = $table->getTableProperty('variations');
        $dimension  = $table->getTableProperty('dimension');

        foreach ($table->getColumns() as $column) {
            if (isset($column->p->metric)) {
                switch ($column->p->metric) {
                    case 'sendings':
                        $column->label = $this->__('Recipients');
                        $column->p->color = self::COLOR_SENDINGS;
                        break;
                    case 'views':
                    case 'view_rate':
                        $column->label = $this->__('Views');
                        $column->p->color = self::COLOR_VIEWS;
                        break;
                    case 'clicks':
                    case 'click_rate':
                        $column->label = $this->__('Clicks');
                        $column->p->color = self::COLOR_CLICKS;
                        break;
                    case 'bounces':
                    case 'bounce_rate':
                        $column->label = $this->__('Bounces');
                        $column->p->color = self::COLOR_BOUNDS;
                        break;
                    case 'optouts':
                    case 'optout_rate':
                        $column->label = $this->__('Optouts');
                        $column->p->color = self::COLOR_OPTOUT;
                        break;
                }
            }
            if (isset($column->p->tracker_id)) {
                $tracker = $this->getCampaign()->getTracker($column->p->tracker_id);
                if ($tracker) {
                    $column->label = $tracker->getTitle();
                } else {
                    $column->label = $this->__('Tracker (%s)', $column->p->tracker_id);
                }
                $column->p->color = self::COLOR_CLICKS;
            }

            if (isset($column->p->color)) {
                $column->p->color_axis = array($this->brightness($column->p->color, 80), $this->brightness($column->p->color, -80));
            }

            if (isset($column->p->variation_id)) {
                $vid = $column->p->variation_id;

                $index = array_search($vid, $variations);

                $gradient = $this->gradient(
                    $this->brightness($column->p->color, -100),
                    $this->brightness($column->p->color, 20),
                    count($variations)
                );

                $column->p->color = $gradient[$index];

                $variation = $this->getCampaign()->getVariation($vid);
                if ($variation) {
                    $column->label = $variation->getName();
                } else {
                    $column->label = $this->__('Variation (%s)', $vid);
                }
            }
        }

        if ($dimension !== 'date') {
            $colors = array(
                '627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4',
                'A559BF', '14D277', '627379', 'FC7A00', 'BF4848', '87969E',
                'D7D020', '00A6D4', 'A559BF', '14D277', '627379', 'FC7A00',
                'BF4848', '87969E', 'D7D020', '00A6D4', 'A559BF', '14D277',
                '627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4',
                'A559BF', '14D277', '627379', 'FC7A00', 'BF4848', '87969E',
                'D7D020', '00A6D4', 'A559BF', '14D277'
            );

            //$table->addColumn('style', Mzax_Chart_Table::TYPE_STRING, 'style', array('role' => 'style'));
            foreach ($colors as $row => $color) {
                $table->setRowProperty($row, 'color', $color);
            }
            $table->setTableProperty('dye', true);
        }

        switch (strtolower($dimension)) {
            case 'hour':
                $table->setColumnType(0, Mzax_Chart_Table::TYPE_TIME);
                $table->setTableProperty('stacked', true);
                $table->setTableProperty('dye', false);
                break;
            case 'dayofweek':
                $table->setTableProperty('stacked', false);
                $table->setTableProperty('dye', false);
                break;
        }
    }

    /**
     * @param string $start
     * @param string $end
     * @param int $steps
     *
     * @return string[]
     */
    protected function gradient($start, $end, $steps)
    {
        $steps = max($steps, 2);
        $start = array_map('hexdec', str_split($start, 2));
        $end   = array_map('hexdec', str_split($end, 2));

        $step = array();
        foreach ($start as $j => $v) {
            $step[$j] = ($v - $end[$j]) / ($steps-1);
        }

        $colors = array();
        for ($i = 0; $i <= $steps; $i++) {
            $rgb = array();
            foreach ($step as $j => $v) {
                $rgb[$j] = sprintf('%02x', floor($start[$j] - $v * $i));
            }
            $colors[] = implode('', $rgb);
        }

        return $colors;
    }

    /**
     * @param string $color
     * @param float $adjust
     *
     * @return string
     */
    protected function brightness($color, $adjust)
    {
        $rgb = array_map('hexdec', str_split($color, 2));
        foreach ($rgb as &$v) {
            $v = sprintf('%02x', max(0, min(255, $v + $adjust)));
        }
        return implode('', $rgb);
    }

    /**
     * @return Mage_Core_Block_Text
     */
    protected function getGoogleJs()
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();

        $js = <<<JS
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"], language:'$locale'});
</script>
JS;
        return $this->getLayout()->createBlock('core/text')->setText($js);
    }

    /**
     * @return void
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $views  = $this->createCircle('viewes', $this->getTotals()->getCell('views'), self::COLOR_VIEWS);
        $clicks = $this->createCircle('clicks', $this->getTotals()->getCell('clicks'), self::COLOR_CLICKS);
        $orders = $this->createCircle('orders', $this->getTotals()->getCell('conversion'), self::COLOR_CONVERSIONS);

        $optout = $this->createCircle('optouts', $this->getTotals()->getSum('bounces', 'optouts'));
        $optout->setColors(array(self::COLOR_OPTOUT, self::COLOR_BOUNDS, self::COLOR_BLANK));
        $optout->clearRows();
        $optout->addRow(array('optout', $this->getTotals()->getCell('optouts')));
        $optout->addRow(array('bounce', $this->getTotals()->getCell('bounces')));
        $optout->addRow(array('', $this->getTotal() - $this->getTotals()->getSum('bounces', 'optouts')));

        $this->setViewsCircle($views);
        $this->setClicksCircle($clicks);
        $this->setOrdersCircle($orders);
        $this->setOptoutCircle($optout);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        /* @var $content Mage_Core_Block_Text_List */
        $content = $this->getLayout()->getBlock('content');
        if ($content) {
            $content->append($this->getGoogleJs());
        }
    }

    /**
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function getConversionBlock()
    {
        $query = $this->getCachedQuery('conversion', 'date', 'view_rate', false);

        /* @var $block Mzax_Emarketing_Block_Chart_Widget_Tab */
        $block = $this->getLayout()->createBlock('mzax_emarketing/chart_widget_tab');
        $block->setType('line');
        $block->getChart()->setCurveType('function');
        $block->setQuery($query);

        $this->prepareTabs($block, '%s_rate', array('views', 'clicks', 'trackers', 'outputs', 'bounces'));
        $this->prepareTable($block->getTable());

        return $block;
    }

    /**
     *
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function getActivityBlock()
    {
        $query = $this->getCachedQuery('activity', 'date', 'sendings', false);

        /* @var $block Mzax_Emarketing_Block_Chart_Widget_Tab */
        $block = $this->getLayout()->createBlock('mzax_emarketing/chart_widget_tab');
        $block->setType('column');
        $block->setQuery($query);

        $this->prepareTabs($block, '%ss', null);
        $this->prepareTable($block->getTable());

        return $block;
    }

    /**
     *
     * @param string $dimension
     * @param string[] $tabs
     *
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function getDimensionPie($dimension, $tabs = array('views', 'clicks'))
    {
        $query = $this->getCachedQuery('dimension_'.$dimension, $dimension, 'views', false, 1);

        /* @var $block Mzax_Emarketing_Block_Chart_Widget_Tab */
        $block = $this->getLayout()->createBlock('mzax_emarketing/chart_widget_tab');
        $block->setCampaign($this->getCampaign());
        $block->setType('pie');
        $block->setQuery($query);
        $block->getChart()->setChartArea(array(
                'top' => '5%',
                'right' => '5%',
                'bottom' => '5%',
                'left' => '5%',
                'width' => '90%',
                'height' => '90%'
        ));
        $block->getChart()->setPieHole(0.25);
        $block->getChart()->setLegend(array(
                'position'  => 'left',
                'alignment' => 'center'
        ));

        $this->prepareTabs($block, '%ss', $tabs);
        $this->prepareTable($block->getTable());

        return $block;
    }

    /**
     *
     * @param string $dimension
     * @param string $charType
     * @param string[] $tabs
     *
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function getDimensionChart($dimension, $charType = 'column', $tabs = null)
    {
        $query = $this->getCachedQuery('dimension_'.$dimension, $dimension, 'views', false);

        /* @var $block Mzax_Emarketing_Block_Chart_Widget_Tab */
        $block = $this->getLayout()->createBlock('mzax_emarketing/chart_widget_tab');
        $block->setCampaign($this->getCampaign());
        $block->setType($charType);
        $block->setQuery($query);
        $block->getChart()->setChartArea(array(
                'top' => '0%',
                'right' => '0%',
                'bottom' => '0%',
                'left' => '0%',
                'width' => '100%',
                'height' => '100%'
        ));

        if ($charType === 'geo') {
            $block->getChart()->setHeight(400);
        }

        $this->prepareTabs($block, '%ss', $tabs);
        $this->prepareTable($block->getTable());

        return $block;
    }

    /**
     * @param Mzax_Emarketing_Block_Chart_Widget_Tab $block
     * @param string $metric
     * @param string[] $tabs
     *
     * @return void
     */
    protected function prepareTabs(Mzax_Emarketing_Block_Chart_Widget_Tab $block, $metric = "%ss", $tabs = null)
    {
        if (!$tabs || in_array('sendings', $tabs)) {
            $block->addTab('sendings', array(
                'label'  => $this->__('Sendings'),
                'metric' => sprintf($metric, 'sending')
            ));
        }

        if (!$tabs || in_array('views', $tabs)) {
            $block->addTab('views', array(
                'label'  => $this->__('Views'),
                'metric' => sprintf($metric, 'view')
            ));
        }

        if (!$tabs || in_array('clicks', $tabs)) {
            $block->addTab('clicks', array(
                'label'  => $this->__('Clicks'),
                'metric' => sprintf($metric, 'click')
            ));
        }

        if ($this->hasTracker()) {
            if (!$tabs || in_array('trackers', $tabs)) {
                $trackers = array();
                foreach ($this->getCampaign()->getTrackers() as $tracker) {
                    $trackers[sprintf($metric, '#'.$tracker->getId())] = $tracker->getTitle();
                }
                if ($tracker = $this->getCampaign()->getDefaultTracker()) {
                    $block->addTab('trackers', array(
                        'label'   => $tracker->getTitle(),
                        'default' => sprintf($metric, '#'.$tracker->getId()),
                        'metric'  => $trackers
                    ));
                }
            }
        }

        if ($tabs && in_array('optouts/bounces', $tabs)) {
            $block->addTab('optouts', array(
                    'label'   => $this->__('Optouts'),
                    'default' => 'optouts',
                    'metric'  => array(
                        sprintf($metric, 'optout') => $this->__('Optouts'),
                        sprintf($metric, 'bounce') => $this->__('Bounces'),
                    )
            ));
        }

        if (!$tabs || in_array('optouts', $tabs)) {
            $block->addTab('optouts', array(
                    'label'   => $this->__('Optouts'),
                    'metric'  => sprintf($metric, 'optout')
            ));
        }

        if (!$tabs || in_array('bounces', $tabs)) {
            $block->addTab('bounces', array(
                    'label'   => $this->__('Bounces'),
                    'metric'  => sprintf($metric, 'bounce')
            ));
        }
    }

    /**
     *
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function getGeoChart()
    {
        $query = $this->getCachedQuery('dimension_country', 'country', 'views', false);

        /* @var $block Mzax_Emarketing_Block_Chart_Widget_Geo */
        $block = $this->getLayout()->createBlock('mzax_emarketing/chart_widget_geo');
        $block->setQuery($query);

        $this->prepareTabs($block, '%ss', array('views', 'clicks'));
        $this->prepareTable($block->getTable());

        return $block;
    }


    /**
     *
     * @param string $type
     * @param string $chartType
     *
     * @return Mzax_Emarketing_Block_Chart_Widget_Tab
     */
    public function getRevenueChart($type = '', $chartType = 'area')
    {
        $type = $type ? '_revenue_'.$type : '_revenue';

        $tracker = $this->getCampaign()->getDefaultTracker();

        $query = $this->getCachedQuery($type, 'date', '#'.$tracker->getId(). $type, false);

        /* @var $block Mzax_Emarketing_Block_Chart_Widget_Tab */
        $block = $this->getLayout()->createBlock('mzax_emarketing/chart_widget_tab');
        $block->setType($chartType);
        $block->setQuery($query);

        foreach ($this->getCampaign()->getTrackers() as $tracker) {
            $block->addTab('tracker_'.$tracker->getId(), array(
                    'class'  => 'trackers',
                    'label'  => $tracker->getTitle(),
                    'metric' => '#'.$tracker->getId(). $type
            ));
        }

        $this->prepareTable($block->getTable());

        return $block;
    }

    /**
     * @param string $label
     * @param int $value
     * @param string $color
     *
     * @return Mzax_Emarketing_Block_Chart_Abstract
     */
    public function createCircle($label = '', $value = 0, $color = 'EA7601')
    {
        $total = $this->getTotal();

        $percentage = $total ? round(($value/$total)*100, 1) : 0;

        $chart = $this->createChart('pie');
        $chart->setColors(array($color, 'E7EFEF'));
        $chart->setPieHole(0.8);
        $chart->setChartArea(array(
                'top' => '10%',
                'right' => '10%',
                'bottom' => '10%',
                'left' => '10%',
                'width' => '80%',
                'height' => '80%'
        ));

        $chart->setEnableInteractivity(false);
        $chart->setPieSliceText('none');
        $chart->setLegend('none');
        $chart->setWidth(80);
        $chart->setHeight(80);
        $chart->setTitle($label);
        $chart->setAutoRedraw(false);
        $chart->setAutoRedraw(false);
        $chart->addOverlay('percentage', "{$percentage}%");
        $chart->addColumn('Label', 'string');
        $chart->addColumn('Values', 'number');
        $chart->addRow(array($label, $value));
        $chart->addRow(array('', max(0, $total-$value)));
        $chart->setValue($value);
        $chart->setLabel($label);

        return $chart;
    }

    /**
     * Create chart type
     *
     * @param string $type
     * @return Mzax_Emarketing_Block_Chart_Abstract
     */
    public function createChart($type)
    {
        $block = 'mzax_emarketing/chart_' . $type;

        /* @var $chart Mzax_Emarketing_Block_Chart_Abstract */
        $chart = $this->getLayout()->createBlock($block);

        return $chart;
    }

    /**
     * Retrieve GeoIP credits
     *
     * @return array
     */
    public function getGeoIpCredits()
    {
        /** @var Mzax_Emarketing_Model_System_Config_Source_Geoip $geoIpSource */
        $geoIpSource = Mage::getSingleton('mzax_emarketing/system_config_source_geoIp');

        $adapters = $geoIpSource->getSelectedAdapters();
        $credits  = array();

        $geoLiteCredit = 'GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.';
        $geoLite = false;
        /* @var $adapter Mzax_GeoIp_Adapter_Abstract */
        foreach ($adapters as $adapter) {
            $credit = $adapter->getCredits();
            if ($credit) {
                if (strpos($credit, $geoLiteCredit)) {
                    $geoLite = true;
                    $credit = str_replace($geoLiteCredit, 'GeoLite*', $credit);
                }
                $credits[] = $credit;
            }
        }

        if ($geoLite) {
            $credits[] = '*' . $geoLiteCredit;
        }

        return $credits;
    }

    /**
     * Retrieve Varien Data Form
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        $elementRenderer = Mage::getBlockSingleton('mzax_emarketing/editable');
        $elementRenderer->setFormat('form');

        $form = new Varien_Data_Form();
        $form->setElementRenderer($elementRenderer);

        return $form;
    }

    /**
     * Helper for simple select element
     *
     * @param string $key
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getDateElement($key)
    {
        $format = Varien_Date::DATE_INTERNAL_FORMAT;
        $value = $this->getRequest()->getParam($key, '');

        return $this->getForm()->addField(
            $key,
            'date',
            array(
                'name'           => $key,
                'value_name'     => $value,
                'value'          => $value,
                'explicit_apply' => true,
                'image'          => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
                'input_format'   => $format,
                'format'         => $format
            )
        )->setId('report_'.$key);
    }
}
