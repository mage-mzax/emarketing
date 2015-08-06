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
class Mzax_Db_Select_Exception extends Zend_Db_Exception
{
    
    
    /**
     * 
     * @var Mzax_Db_Select
     */
    public $select;
    
    
    
    /**
     * 
     * @var string
     */
    public $sql;
    
    
    
    /**
     * 
     * @var array
     */
    public $bindings = array();
    
    
    
    public function __construct($msg = '', $code = 0, Mzax_Db_Select $select = null, Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
        $this->select = $select;
        if($select) {
            $this->bindings = $select->getBindings();
        }
    }
    
    
    
    /**
     * String representation of the exception
     *
     * @return string
     */
    public function __toString()
    {
        $str = parent::__toString();
        
        if($this->sql) {
            $str .= "\n\n" . $this->sql;
        }
        if(!empty($this->bindings)) {
            $str .= "\nBindings:";
            foreach($this->bindings as $name => $expr) {
                $str .= "\n\t" . $name . " \t->  " . $expr;
            }
        }
        
        return $str;
    }
    
}