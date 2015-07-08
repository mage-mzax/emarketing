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



class Mzax_Emarketing_Block_Chart_Widget_Geo extends Mzax_Emarketing_Block_Chart_Widget_Tab
{
    
    
    
    
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mzax/emarketing/widget/chart-geo.phtml');
    }
    
    
    
    public function getType()
    {
        return 'geo';
    }
    
    
    
    
    /**
     * 
     * @return Mzax_Emarketing_Block_Chart_Abstract
     */
    public function getChart()
    {
        if(!$this->_chart) {
            
            
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
            $chart->setHeight(400);
            
        }
        return $this->_chart;
    }
    
    
}