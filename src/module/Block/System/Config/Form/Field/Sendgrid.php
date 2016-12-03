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
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Block_System_Config_Form_Field_Sendgrid
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{



    protected function _getHtml()
    {
        $username = Mage::getStoreConfig('mzax_emarketing/email/sendgrid_username');
        $password = Mage::getStoreConfig('mzax_emarketing/email/sendgrid_password');

        if (empty($username)) {
            $message = Mage::helper('mzax_emarketing')->__('Please provider a valid SandGrid username.');
            $class = 'inbox-failure';
        }
        else if (empty($password)) {
            $message = Mage::helper('mzax_emarketing')->__('Please provider a valid SandGrid password.');
            $class = 'inbox-failure';
        }
        else {
            /* @var $transprot Mzax_Emarketing_Model_Outbox_Transporter_Sendgrid */
            $transprot = Mage::getModel('mzax_emarketing/outbox_transporter_sendgrid');

            $result = $transprot->testAuth($username, $password);

            if ($result === true) {
                $message = Mage::helper('mzax_emarketing')->__('Successfully conntected to SendGrid');
                $class = 'inbox-success';
            }
            else {
                $message = Mage::helper('mzax_emarketing')->__('Failed to connect to SendGird: %s', $result);
                $class = 'inbox-failure';
            }
        }

        return '<div class="inbox-status '.$class.'">' . $message . '</div>';
    }



    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        if (Mage::getStoreConfig('mzax_emarketing/email/transporter') !== 'sendgird') {
            return '';
        }

        $useContainerId = $element->getData('use_container_id');
        $html = '<tr id="row_' . $id . '">'
              .   '<td class="mzax-mail-storage-test" colspan="3">' . $this->_getHtml(). '</td>'
              . '</tr>';
        return $html;
    }
}
