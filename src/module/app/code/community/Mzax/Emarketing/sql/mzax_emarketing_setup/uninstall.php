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
 * Simple helper script to run to remove all
 * resources used by this extension
 */


ini_set('html_errors', 0);
ini_set('display_errors', 1);

require 'app/Mage.php';

Mage::app('admin');

/* @var $resource Mage_Core_Model_Resource */
$resource = Mage::getModel('core/resource');

/* @var $connection Varien_Db_Adapter_Interface */
$connection = $resource->getConnection('core_write');

header('Content-Type:text/plain');




/* @todo */
// remove tables containing with mzax_emarketing
// rmeove config path containtng mzax_emarketing
// remove resource_entry
// remove filter indexes
// remove config sttings = path LIKE mzax%
/*
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS
`mzax_emarketing_campaign`, 
`mzax_emarketing_campaign_variation`, 
`mzax_emarketing_conversion_tracker`, 
`mzax_emarketing_conversion_tracker_goal`, 
`mzax_emarketing_email_address`, 
`mzax_emarketing_email_inbox`, 
`mzax_emarketing_email_outbox`, 
`mzax_emarketing_goal`, 
`mzax_emarketing_link`, 
`mzax_emarketing_link_click`, 
`mzax_emarketing_link_group`, 
`mzax_emarketing_link_reference`, 
`mzax_emarketing_recipient`, 
`mzax_emarketing_recipient_event`,
`mzax_emarketing_recipient_error`,
`mzax_emarketing_report`, 
`mzax_emarketing_report_conversion`, 
`mzax_emarketing_report_dimension`, 
`mzax_emarketing_report_dimension_conversion`, 
`mzax_emarketing_report_enum`, 
`mzax_emarketing_template`,
`mzax_emarketing_useragent`;
DELETE FROM `magento_mzax_emarketing`.`core_resource` WHERE `core_resource`.`code` = 'mzax_emarketing_setup';
SET foreign_key_checks = 1;
*/



