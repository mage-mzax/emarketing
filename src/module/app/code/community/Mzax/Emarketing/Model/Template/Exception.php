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
 * Class Mzax_Emarketing_Model_Template_Exception
 */
class Mzax_Emarketing_Model_Template_Exception extends Exception
{
    /**
     * @var LibXMLError[]
     */
    protected $_errors;

    /**
     * Mzax_Emarketing_Model_Template_Exception constructor.
     *
     * @param LibXMLError[] $errors
     */
    public function __construct($errors)
    {
        parent::__construct("Failed to parse template HTML");
        $this->_errors = $errors;
    }

    /**
     * Retrieve errors
     *
     * @see http://php.net/manual/en/function.libxml-get-errors.php
     *
     * @return LibXMLError[]
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $message = parent::__toString();

        if (!empty($this->_errors)) {
            foreach($this->_errors as $error) {
                $message .= sprintf(
                    "\n  [Line %s:%s] #%s %s",
                    $error->line,
                    $error->column,
                    $error->code,
                    $error->message
                );
            }
        }

        return $message;
    }
}
