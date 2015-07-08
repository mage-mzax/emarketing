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


class Mzax_Emarketing_Model_Template_Exception extends Exception
{
    
    protected $_errors;
    
    public function __construct($errors)
    {
        parent::__construct("Failed to parse template HTML");
        $this->_errors = $errors;
    }
    
    
    /**
     * 
     * @see http://php.net/manual/en/function.libxml-get-errors.php
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    
}