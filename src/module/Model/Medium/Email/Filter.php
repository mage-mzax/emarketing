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
class Mzax_Emarketing_Model_Medium_Email_Filter
    extends Mage_Widget_Model_Template_Filter
{
    
    /**
     * 
     * @var boolean
     */
    protected $_enableVarDirective = true;
    
    
    
    /**
     * Disable var directive
     * 
     * @param string $flag
     * @return Mzax_Emarketing_Model_Medium_Email_Filter
     */
    public function disableVarDirective($flag = true)
    {
        $this->_enableVarDirective = !$flag;
        return $this;
    }
    
    
    
    
    /**
     * Var directive with modifiers support
     *
     * @param array $construction
     * @return string
     */
    public function varDirective($construction)
    {
        if($this->_enableVarDirective) {
            return parent::varDirective($construction);
        }
        return $construction[0];
    }
    
    
    
    
    /**
     * Generate widget HTML if template variables are assigned
     *
     * @param array $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        $construction[2] .= sprintf(' store_id ="%s"', $this->getStoreId());
        return parent::widgetDirective($construction);
    }
    
    
    
    /**
     * Filter the string as template.
     * Rewrited for logging exceptions
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        try {
            $value = Varien_Filter_Template::filter($value);
        } 
        catch (Exception $e) {
            throw $e;
            //$value = '';
            //Mage::logException($e);
        }
        return $value;
    }
}
