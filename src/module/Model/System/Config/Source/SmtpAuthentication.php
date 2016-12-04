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
 * Class Mzax_Emarketing_Model_System_Config_Source_SmtpAuthentication
 */
class Mzax_Emarketing_Model_System_Config_Source_SmtpAuthentication
{
    /**
     * Retrieve smtp authentication options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();

        $options[] = array(
            'value' => 'login',
            'label' => 'Login'
        );
        $options[] = array(
            'value' => 'Crammd5',
            'label' => 'CRAM-MD5'
        );

        return $options;
    }
}
