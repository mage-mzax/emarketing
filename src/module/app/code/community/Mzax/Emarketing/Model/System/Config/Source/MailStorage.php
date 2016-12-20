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
 * Class Mzax_Emarketing_Model_System_Config_Source_MailStorage
 */
class Mzax_Emarketing_Model_System_Config_Source_MailStorage
{
    /**
     * Retrieve mail storage options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();

        $options[] = array(
            'value' => 'Zend_Mail_Storage_Pop3',
            'label' => 'Pop3'
        );
        /* @todo test imap support
        $options[] = array(
            'value' => 'Zend_Mail_Storage_Imap',
            'label' => 'Imap'
        );
        */
        return $options;
    }
}
