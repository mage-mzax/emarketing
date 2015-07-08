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

class Mzax_Emarketing_Block_Chart_Abstract extends Mage_Core_Block_Template
{

    
    protected $_chartClass = '';
    
    protected function _construct()
    {
        parent::_construct();
        $this->setElementId('gchart_'.rand(0, 100000));
    }
    
    public function getChartClass()
    {
        return "google.visualization.{$this->_chartClass}";
    }
    
    
    
    
    
    protected $_columns = array();
    
    
    protected $_rows = array();
    
    
    protected $_overlays = array();
    
    
    public function addOverlay($name, $overlay)
    {
        $this->_overlays[$name] = $overlay;
        return $this;
    }
    
    
    public function addColumn($label, $type = 'number', $p = null)
    {
        $this->_columns[] = array($label, $type, $p);
        return $this;
    }
    
    
    public function clearRows()
    {
        $this->_rows = array();
        return $this;
    }
    
    public function addRow(array $row, $offset = null)
    {
        if($offset === null) {
            $this->_rows[] = $row;
        }
        else {
            array_splice($this->_rows, $offset, 0, array($row));
        }
        return $this;
    }
    
    
    public function getOptionJs()
    {
        $ignore  = array('type', 'module_name', 'auto_redraw');
        $options = array();
        foreach($this->_data as $key => $value) {
            if(in_array($key, $ignore)) {
                continue;
            }
            $name = lcfirst($this->_camelize($key));
            $options[$name] = $value;
        }
        
        return Zend_Json::encode($options);
    }
    
    
    
    
    
    protected function _getDataJson()
    {
        $json = array();
        foreach($this->_rows as $row) {
            $data = array();
            foreach($this->_columns as $i => $column) {
                list($label, $type, $p) = $column;
                $value = $row[$i];
                if($value === null) {
                    $value = 'null';
                }
                else {
                    switch($type) {
                        case 'tooltip':
                        case 'string':
                            $value = "'".$this->jsQuoteEscape($value)."'";
                            break;
                            
                        case 'date':
                            if($value instanceof DateTime) {
                                $value = "new Date({$value->format('Y, n-1, j')})";
                            }
                            break;
    
                        default:
                            $value = (float) $value;
                            break;
                    }
                }
                $data[] = $value;
            }
    
            $json[] = "[" . implode(', ', $data) . "]";
        }
        return "[" . implode(",\n", $json) . "]";
    }
    
    
    
    protected function _getDataTableJs($name = 'data')
    {
        $html = "var {$name} = new google.visualization.DataTable();\n";
    
        foreach($this->_columns as $column) {
            list($label, $type, $p) = $column;
            
            $json = array('type' => $type, 'label' => $label);
            if($p) {
                $json['p'] = $p;
            }
            $json = Zend_Json::encode($json);
            
            $html .= "{$name}.addColumn($json);\n";
        }
    
        $html .= "{$name}.addRows({$this->_getDataJson()});";
    
        return $html;
    }
    
    
    
    
    
    public function _toHtml()
    {
        $autoredraw = (int) $this->getDataSetDefault('auto_redraw', true);
        
        $overlayHtml = '';
        foreach($this->_overlays as $name => $overlay) {
            if($overlay instanceof Mage_Core_Block_Abstract) {
                $overlay = $overlay->toHtml();
            }
            $overlayHtml .= "<div class=\"overlay $name\">$overlay</div>";
        }
        
        
        return <<<HTML
<div id="{$this->getElementId()}-wrapper" class="chart-wrapper">
    <div id="{$this->getElementId()}"></div>
   {$overlayHtml}
</div>
<script type="text/javascript">
(function() {
    {$this->_getDataTableJs('data')}
    var chart = new {$this->getChartClass()}(document.getElementById('{$this->getElementId()}'));
    chart.draw(data, {$this->getOptionJs()});
    if($autoredraw) {
        Event.observe(window, "resize", function() {
            chart.draw(data, options);
        });
    }
}());
</script>
HTML;
    }
    
    
    
    
    
    
    
    
    
    
    
}
