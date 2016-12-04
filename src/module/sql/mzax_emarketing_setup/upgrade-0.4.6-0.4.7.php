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



/* @var $installer Mzax_Emarketing_Model_Resource_Setup */
$installer  = $this;
$installer->startSetup();

$connection = $installer->getConnection();


$listTable = $installer->getTable('mzax_emarketing/newsletter_list');


$connection->addColumn($listTable, 'store_ids', array(
    'nullable' => false,
    'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'   => 255,
    'after'    => 'list_id',
    'comment'  => 'Store Ids',
    'default'  => Mage_Core_Model_App::ADMIN_STORE_ID
));




$installer->endSetup();
