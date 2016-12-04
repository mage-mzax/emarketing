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
 * Class Mzax_Emarketing_Model_Object_Filter_Address_Street
 */
class Mzax_Emarketing_Model_Object_Filter_Address_Street
    extends Mzax_Emarketing_Model_Object_Filter_Column
{
    /**
     * @var string
     */
    protected $_formText = '%s %s %s.';

    /**
     * @var string
     */
    protected $_requireBinding = 'street';

    /**
     * @var string
     */
    protected $_label = 'Street';

    /**
     * @return string
     */
    public function getTitle()
    {
        return "Address | Street";
    }
}
