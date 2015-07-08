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
 * 
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Chart_Table extends Varien_Object
{
    const TYPE_NUMBER   = 'number';
    const TYPE_DATE     = 'date';
    const TYPE_STRING   = 'string';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIME     = 'timeofday';
    const TYPE_BOOLEAN  = 'boolean';
    
    
    protected $_columns = array();
    
    
    protected $_rows = array();
    
    
    
    protected $_p = array();
    
    
    public function setValue($column, $row, $value)
    {
        $cell = $this->getCell($column, $row);
        if($cell) {
            $cell->v = $value;
        }
        return $this;
    }
    
    
    /**
     * Retrieve column
     * 
     * @param string|integer $index
     * @return stdClass
     */
    public function getColumn($index)
    {
        if(is_int($index)) {
            if(!isset($this->_columns[$index])) {
                return null;
            }
            return $this->_columns[$index];
        }
        
        foreach($this->_columns as $column) {
            if($column->id && $column->id === $index) {
                return $column;
            }
        }
        return null;
    }
    
    
    
    
    /**
     * Set column type
     * 
     * @param int|string $column
     * @param string $type
     * @return Mzax_Chart_Table
     */
    public function setColumnType($column, $type)
    {
        $column = $this->getColumn($column);
        if($column) {
            $column->type = $type;
        }
        return $this;
    }
    
    
    
    /**
     * Set column property
     * 
     * @param int|string $index
     * @param string $name
     * @param mixed $value
     * @return Mzax_Chart_Table
     */
    public function setColumnProperty($index, $name, $value)
    {
        $column = $this->getColumn($index);
        if($column) {
            if(!isset($column->p)) {
                $column->p = new stdClass;
            }
            $column->p->$name = $value;
        }
        return $this;
        
    }
    
    
    public function setRowProperty($index, $name, $value)
    {
        if(isset($this->_rows[$index])) {
            $this->_rows[$index]->p->$name = $value;
        }
        return $this;
    }
    
    
    
    public function setTableProperty($name, $value = null)
    {
        if(is_array($name)) {
            foreach ($name as $n => $v) {
                $this->setTableProperty($n, $v);
            }
            return $this;
        }
        $this->_p[$name] = $value;
        return $this;
    }
    
    public function getTableProperty($name)
    {
        if(isset($this->_p[$name])) {
            return $this->_p[$name];
        }
        return null;
        
    }
    
    

    public function addColumn($label, $type = self::TYPE_NUMBER, $id = null, array $p = array())
    {
        $column = array(
            'label' => $label,
            'type'  => $type,
            'id'    => $id,
            'p'     => (object) $p
        );
        return $this->_columns[] = (object) $column;
    }
    
    public function getColumns()
    {
        return $this->_columns;
    }
    
    
    public function clearRows()
    {
        $this->_rows = array();
        return $this;
    }
    
    
    public function getRows()
    {
        return $this->_rows;
    }
    
    
    public function getCell($column, $rowIndex)
    {
        if($column && isset($this->_rows[$rowIndex])) {
            $cells = &$this->_rows[$rowIndex]->c;
            if(!isset($cells[$column])) {
                $cells[$column] = new stdClass();
                $cells[$column]->v = null;
            }
            return $cells[$column];
            
        }
    }
    
    public function addRow(array $data, array $p = array())
    {
        $row = (object) array(
            'p' => (object) $p,
            'c' => array()
        );
        
        foreach($data as $column => $cell) {
            if(!is_array($cell)) {
                $cell = (object) array('v' => $cell);
            }
            $row->c[] = $cell;
        }
        $this->_rows[] = $row;
        return $row;
    }
    
    
    
    
    
    /**
     * To JavaScript
     * 
     * @return string
     */
    public function asJs()
    {
        return "new google.visualization.DataTable({$this->asJson()})";
    }
    
    
    
    
    /**
     * Convert Table to JSON
     * 
     * @see https://google-developers.appspot.com/chart/interactive/docs/reference#dataparam
     * @return string
     */
    public function asJson()
    {
        $json = array(
            'cols' => array(),
            'rows' => array(),
            'p'    => $this->_p
        );
        
        foreach($this->_columns as $column) {
            $json['cols'][] = $column;
        }
        
        $previousValues = array();
        
        foreach($this->_rows as $rowIndex => $data) {
            $row = array('c' => array(), 'p' => $data->p);
            
            foreach($this->_columns as $i => $column) {
                $cell = clone $data->c[$i];
                
                if($cell->v === null && isset($column->p->default)) {
                    switch($column->p->default) {
                        case '@previous':
                            if(isset($previousValues[$i])) {
                                $cell->v = $previousValues[$i];
                            }
                            /*
                            $previousIndex = $rowIndex-1;
                            while($previousIndex >= 0) {
                                if(isset($this->_rows[$previousIndex]->c[$i]->v)) {
                                    $cell->v = $this->_rows[$previousIndex]->c[$i]->v;
                                    break;
                                }
                            }*/
                            break;
                         
                        default: 
                            $cell->v = $column->p->default;
                            break;
                    }
                }
                
                $value = $previousValues[$i] = $cell->v;
                
                if($column->type === self::TYPE_DATE) {
                    if(is_string($value)) {
                        $value = DateTime::createFromFormat(Varien_Date::DATE_PHP_FORMAT, $value);
                    }
                    else if(is_int($value)) {
                        $value = new DateTime($value);
                    }
                }
                
                $formattedValue = $this->formatValue($value, $column->type);
                
                if(!empty($column->p->pattern)) {
                    if($value instanceof DateTime) {
                        $cell->f = $value->format($column->p->pattern);
                    }
                }
                else if(isset($column->p->_f)) {
                    $cell->f = sprintf($column->p->_f, $cell->v);
                }
                
                $cell->v = $formattedValue;
                
                $row['c'][] = $cell;
            }
            $json['rows'][] = $row;
        }
        
        return Zend_Json::encode($json, false, array('enableJsonExprFinder' => true));
    }
    
    
    
    public function formatValue($value, $type)
    {
        switch($type) {
            case self::TYPE_DATE:
                if($value instanceof DateTime) {
                    $zeroBaseMonth = $value->format('n')-1;
                    return "Date({$value->format("Y,$zeroBaseMonth,j")})";
                }
                return null;
            case self::TYPE_NUMBER:
                return (float) $value;
                
            case self::TYPE_TIME:
                // assume hour value
                if(is_numeric($value)) {
                    return array((int)$value, 0, 0);
                }
        }
        return $value;
    }
    
    
    
    
    
}