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



/* @var $installer Mzax_Emarketing_Model_Resource_Setup */
$installer  = $this;
$installer->startSetup();

$connection = $installer->getConnection();


$req = array('nullable' => false);
$opt = array('nullable' => true, 'unsigned' => true);


$int = array('nullable' => false, 'unsigned' => true, 'default' => 0);


$uid = array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true
);

$varchar = 255;
$text = 65536;

$primaryIndex = array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY);





$subscriberTable = $installer->getTable('newsletter/subscriber');


/********************************************************************
 * Setup Newsletter List Table
 ********************************************************************/
$listTable = $connection->newTable($installer->getTable('mzax_emarketing/newsletter_list'))
    ->addColumn('list_id',        Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,    $uid, 'Newsletter list ID')
    ->addColumn('created_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('updated_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Update Time')
    ->addColumn('name',           Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'List Name')
    ->addColumn('description',    Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'List Description')
    ->addColumn('auto_subscribe', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $int, 'Auto subscribe to List')
    ->addColumn('is_private',     Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $int, 'Private List');

$connection->createTable($listTable);



/********************************************************************
 * Setup Newsletter List Subscriber Table
 ********************************************************************/
$listSubscriberTable = $connection->newTable($installer->getTable('mzax_emarketing/newsletter_list_subscriber'))
    ->addColumn('list_id',        Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,    $req, 'Newsletter list ID')
    ->addColumn('subscriber_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Newsletter subscriber ID')
    ->addColumn('changed_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('list_status',    Varien_Db_Ddl_Table::TYPE_TINYINT, null,  $int, 'List subscribe status')
    ->addIndex('PRIMARY', array('list_id', 'subscriber_id'),$primaryIndex );

$installer->addForeignKey($listSubscriberTable, $subscriberTable, 'subscriber_id');
$installer->addForeignKey($listSubscriberTable, $listTable, 'list_id');


$connection->createTable($listSubscriberTable);





$installer->endSetup();
