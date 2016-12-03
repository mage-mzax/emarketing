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
$connection = $installer->getConnection();





$req = array('nullable' => false);
$opt = array('nullable' => true, 'unsigned' => true);
$sig = array('nullable' => true, 'unsigned' => false);


/*
 * variation_id column defenition
* (0=orignal, -1=none)
* NULL breaks index and 1-1 key lookup
* We can assume that variation_id never outreach int unsigned limit
*/
$var = array('nullable' => false, 'unsigned' => false);

$int = array('nullable' => false, 'unsigned' => true, 'default' => 0);


$uid = array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true
);


$uniqueIndex  = array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);
$primaryIndex = array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY);

$varchar = 255;
$text = 65536;


$installer->startSetup();





/********************************************************************
 * Setup Template Table
 ********************************************************************/
$templateTable = $connection->newTable($installer->getTable('mzax_emarketing/template'))
    ->addColumn('template_id',    Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,    $uid, 'Template ID')
    ->addColumn('created_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('updated_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Update Time')
    ->addColumn('name',           Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Campaign Name')
    ->addColumn('description',    Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Description')
    ->addColumn('credits',        Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Credits')
    ->addColumn('body',           Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Email Content');

$connection->dropTable($templateTable->getName());
$connection->createTable($templateTable);




/********************************************************************
 * Setup Conversion Tracker Table
 ********************************************************************/
$trackerTable = $connection->newTable($installer->getTable('mzax_emarketing/conversion_tracker'))
    ->addColumn('tracker_id',      Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $uid, 'Tracker ID')
    ->addColumn('created_at',      Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('updated_at',      Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Update Time')
    ->addColumn('title',           Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Title')
    ->addColumn('description',     Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Description')
    ->addColumn('campaign_ids',    Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Campagin Ids to track')
    ->addColumn('is_active',       Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1,     $req, 'Is Active')
    ->addColumn('is_default',      Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1,     $req, 'Is Default tracker')
    ->addColumn('is_aggregated',   Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1,     $req, 'Flag if data has been aggregated')
    ->addColumn('goal_type',       Varien_Db_Ddl_Table::TYPE_TEXT, 32,       $req, 'The goal type id')
    ->addColumn('filter_data',     Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Filter Options as JSON for the aggregator')
    ->addIndex('IDX_ACTIVE', array('is_active'));


$connection->dropTable($trackerTable->getName());
$connection->createTable($trackerTable);





/********************************************************************
 * Setup Campagin Table
 ********************************************************************/
$campaignTable = $connection->newTable($installer->getTable('mzax_emarketing/campaign'))
    ->addColumn('campaign_id',          Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Campaign ID')
    ->addColumn('created_at',           Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('updated_at',           Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Update Time')
    ->addColumn('start_at',             Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Start Time')
    ->addColumn('end_at',               Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'End Time')
    ->addColumn('archived',             Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Is archived')
    ->addColumn('running',              Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Is running')
    ->addColumn('check_frequency',      Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'How often to check for new recipients in minutes')
    ->addColumn('last_check',           Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'The last check done')
    ->addColumn('min_resend_interval',  Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'The minimum time interval before campaign can be resend to same recipient')
    ->addColumn('autologin',            Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Autologin')
    ->addColumn('abtest_enable',        Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Enable Ab-Testing')
    ->addColumn('abtest_traffic',       Varien_Db_Ddl_Table::TYPE_SMALLINT, 3,    $req + array('default' => 100), 'Allowed traffic for Ab-Test')
    ->addColumn('expire_time',          Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req + array('default' => 120), 'The expire time in minutes after send')
    ->addColumn('store_id',             Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,    $req, 'Store ID')
    ->addColumn('default_tracker_id',   Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $opt, 'Default Tracker ID')
    ->addColumn('name',                 Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Campaign Name')
    ->addColumn('identity',             Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Sender Identity')
    ->addColumn('provider',             Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Recipient Provider')
    ->addColumn('medium',               Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Medium')
    ->addColumn('medium_json',          Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Custom Data for the medium as JSON')
    ->addColumn('filter_data',          Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Filter Options as JSON')
    ->addColumn('sending_stats',        Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Stats on how many recipient the campaign got sent to')
    ->addColumn('interaction_stats',    Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Stats on how many user interacted with the campaign')
    ->addColumn('conversion_stats',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Stats on how many conversions happend')
    ->addColumn('fail_stats',           Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Stats on how many bounce/optouts happend')
    ->addColumn('revenue_stats',        Varien_Db_Ddl_Table::TYPE_DECIMAL, '9,2', $req, 'Stats on revenue')
    ->addIndex('IDX_RUNNING', array('running'));


$connection->dropTable($campaignTable->getName());
$connection->createTable($campaignTable);





/********************************************************************
 * Setup Campagin Variation Table
 ********************************************************************/
$variationTable = $connection->newTable($installer->getTable('mzax_emarketing/campaign_variation'))
    ->addColumn('variation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Variation ID')
    ->addColumn('campaign_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Campaign ID')
    ->addColumn('created_at',   Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('updated_at',   Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Update Time')
    ->addColumn('is_active',    Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Is Active')
    ->addColumn('is_removed',   Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Is Removed')
    ->addColumn('name',         Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Variation Name')
    ->addColumn('medium_json',  Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Custom Data for the medium as JSON');

$installer->addForeignKey($variationTable, $campaignTable, 'campaign_id');

$connection->dropTable($variationTable->getName());
$connection->createTable($variationTable);



/********************************************************************
 * Setup Recipient Address Table
********************************************************************/
$recipientAddressTable = $connection->newTable($installer->getTable('mzax_emarketing/recipient_address'))
    ->addColumn('address_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Address Id')
    ->addColumn('address',        Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Address (Can be an email or real address)')
    ->addColumn('exists',         Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Exists or has bounced')
    ->addColumn('view_id',        Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Last detected view event id')
    ->setOption('charset', ' ascii')
    ->setOption('collate', 'ascii_bin')
    ->addIndex('UNQ_ADDRESS', array('address'), $uniqueIndex);

$connection->dropTable($recipientAddressTable->getName());
$connection->createTable($recipientAddressTable);





/********************************************************************
 * Setup Recipient Table
 ********************************************************************/
$recipientTable = $connection->newTable($installer->getTable('mzax_emarketing/recipient'))
    ->addColumn('recipient_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Recipient ID')
    ->addColumn('address_id',   Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Email Address ID')
    ->addColumn('is_mock',      Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Is mock recipient created from admin for testing')
    ->addColumn('created_at',   Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('prepared_at',  Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Prepare by medium')
    ->addColumn('sent_at',      Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Letter sent time')
    ->addColumn('viewed_at',    Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Letter viewed time')
    ->addColumn('object_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Object ID from email provider')
    ->addColumn('campaign_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Campagin')
    ->addColumn('variation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $sig, 'Variation (-1 = no variation, 0 = orignal)')
    ->addColumn('beacon_hash',  Varien_Db_Ddl_Table::TYPE_CHAR, 16,       $opt, 'Unique random hash')
    ->setOption('charset', ' ascii')
    ->setOption('collate', 'ascii_bin')
    ->addIndex('IDX_PREPARED_AT', array('prepared_at'))
    ->addIndex('IDX_CAMPAIGN_CREATED', array('campaign_id', 'created_at'))
    ->addIndex('UNQ_BEACON', array('beacon_hash'), $uniqueIndex);

$installer->addForeignKey($recipientTable, $campaignTable, 'campaign_id');
$installer->addForeignKey($recipientTable, $recipientAddressTable, 'address_id');

$connection->dropTable($recipientTable->getName());
$connection->createTable($recipientTable);





/********************************************************************
 * Setup Useragent Table
 ********************************************************************/
$useragentTable = $connection->newTable($installer->getTable('mzax_emarketing/useragent'))
    ->addColumn('useragent_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'User Agent ID')
    ->addColumn('hash',             Varien_Db_Ddl_Table::TYPE_CHAR, 32,       $req, 'MD5 hash of useragent')
    ->addColumn('useragent',        Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Captured date and time')
    ->addColumn('ua',               Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'UA')
    ->addColumn('ua_version',       Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'UA Version')
    ->addColumn('os',               Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'OS')
    ->addColumn('os_version',       Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'OS Version')
    ->addColumn('device',           Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'Device')
    ->addColumn('device_brand',     Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'Device Brand')
    ->addColumn('device_type',      Varien_Db_Ddl_Table::TYPE_TEXT, 32,       $opt, 'Device Type (mobile, desktop, tablet)')
    ->addColumn('parsed',           Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Has been parsed')
    ->addIndex('UNQ_HASH', array('hash'), $uniqueIndex)
    ->addIndex('IDX_PARESED', array('parsed'));

$connection->dropTable($useragentTable->getName());
$connection->createTable($useragentTable);






/********************************************************************
 * Setup Recipient Event Table
 ********************************************************************/
$recipientEventTable = $connection->newTable($installer->getTable('mzax_emarketing/recipient_event'))
    ->addColumn('event_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Event ID')
    ->addColumn('event_type',   Varien_Db_Ddl_Table::TYPE_TINYINT, 1,     $req, 'Event type (1=view, 2=click)')
    ->addColumn('recipient_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Recipient ID')
    ->addColumn('captured_at',  Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Captured date and time')
    ->addColumn('ip',           Varien_Db_Ddl_Table::TYPE_BINARY, 16,     $req, 'IPv4 | IPv6')
    ->addColumn('useragent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Useragent')
    ->addColumn('country_id',   Varien_Db_Ddl_Table::TYPE_CHAR, 2,        $opt, 'Country ID retrieved from IP')
    ->addColumn('region_id',    Varien_Db_Ddl_Table::TYPE_CHAR, 5,        $opt, 'Region ID retrieved from IP')
    ->addColumn('time_offset',  Varien_Db_Ddl_Table::TYPE_TINYINT, 3,     $sig, 'Time offset in minutes divided by 15')  
    ->addIndex('IDX_GEO', array('country_id', 'region_id', 'event_type'));


$installer->addForeignKey($recipientEventTable, $useragentTable, 'useragent_id');
$installer->addForeignKey($recipientEventTable, $recipientTable, 'recipient_id');

$connection->dropTable($recipientEventTable->getName());
$connection->createTable($recipientEventTable);

    

/********************************************************************
 * Setup Error Table
********************************************************************/
$errorTable = $connection->newTable($installer->getTable('mzax_emarketing/recipient_error'))
    ->addColumn('error_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Error ID')
    ->addColumn('recipient_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Recipient ID')
    ->addColumn('campaign_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Campaign ID')
    ->addColumn('created_at',   Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('message',      Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Error Message')
    ->setComment("Simple error log for recipient exceptions");


$installer->addForeignKey($errorTable, $recipientTable, 'recipient_id');
$installer->addForeignKey($errorTable, $campaignTable, 'campaign_id');

$connection->dropTable($errorTable->getName());
$connection->createTable($errorTable);

    
    


/*********************************************************************************************
 *
 * TRACKING TABLES
 * the following tables are used for tracking emails
 *
 ********************************************************************************************/
    
   



/********************************************************************
 * Setup Link Group Table
 ********************************************************************/
$linkGroupTable = $connection->newTable($installer->getTable('mzax_emarketing/link_group'))
    ->addColumn('link_group_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Link Group ID')
    ->addColumn('created_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('updated_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Update Time')
    ->addColumn('name',           Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Name')
    ->addColumn('url_pattern',    Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Regex pattern to match the url')
    ->addColumn('anchor_pattern', Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Regex pattern to match the anchor text')
    ->addColumn('description',    Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Description')
    ->setComment("Allows to group links for reporting purpose");

// @TODO add unsubscribe group
// @TODO n=n link
$connection->dropTable($linkGroupTable->getName());
$connection->createTable($linkGroupTable);





/********************************************************************
 * Setup Link Table
 ********************************************************************/
$linkTable = $connection->newTable($installer->getTable('mzax_emarketing/link'))
    ->addColumn('link_id',        Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Link ID')
    ->addColumn('link_group_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Link Group ID')
    ->addColumn('link_hash',      Varien_Db_Ddl_Table::TYPE_CHAR, 32,       $req, 'Unique hash from `url` and `anchor`')
    ->addColumn('url',            Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Url')
    ->addColumn('anchor',         Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Anchor Text')
    ->addColumn('optout',         Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'Is opt out link')
    ->addIndex('UNQ_LINK_HASH', array('link_hash'), $uniqueIndex);

$installer->addForeignKey($linkTable, $linkGroupTable, 'link_group_id');

$connection->dropTable($linkTable->getName());
$connection->createTable($linkTable);
//$connection->changeColumn($linkTable, 'link_hash', 'link_hash', "CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'MD5 hash of useragent'");








/********************************************************************
 * Setup Link Reference Table
 ********************************************************************/
$linkReferenceTable = $connection->newTable($installer->getTable('mzax_emarketing/link_reference'))
    ->addColumn('reference_id',   Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Link Reference ID')
    ->addColumn('link_id',        Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Link ID')
    ->addColumn('recipient_id',   Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Recipient ID')
    ->addColumn('public_id',      Varien_Db_Ddl_Table::TYPE_CHAR, 16,       $req, 'Public unique hash id for creating the url')
    ->addIndex('UNQ_PUBLIC_ID', array('public_id'), $uniqueIndex)
    ->setOption('charset', 'ascii')
    ->setOption('collate', 'ascii_bin');

$installer->addForeignKey($linkReferenceTable, $linkTable, 'link_id');
$installer->addForeignKey($linkReferenceTable, $recipientTable, 'recipient_id');

$connection->dropTable($linkReferenceTable->getName());
$connection->createTable($linkReferenceTable);







/********************************************************************
 * Setup Link Reference Click Table
 ********************************************************************/
$clickTable = $connection->newTable($installer->getTable('mzax_emarketing/link_reference_click'))
    ->addColumn('click_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Link Reference Click ID')
    ->addColumn('reference_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Link Reference ID')
    ->addColumn('event_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Recipient Event ID')
    ->addColumn('clicked_at',   Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Recipient ID')
    ->addIndex('IDX_CLICKED_AT', array('clicked_at'));

$installer->addForeignKey($clickTable, $linkReferenceTable, 'reference_id');
$installer->addForeignKey($clickTable, $recipientEventTable, 'event_id');


$connection->dropTable($clickTable->getName());
$connection->createTable($clickTable);







/********************************************************************
 * Setup Inbox Table
 ********************************************************************/
$inboxTable = $connection->newTable($installer->getTable('mzax_emarketing/inbox_email'))
    ->addColumn('email_id',       Varien_Db_Ddl_Table::TYPE_INTEGER, 11,    $uid, 'Email ID')
    ->addColumn('store_id',       Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,    $opt, 'Store ID')
    ->addColumn('created_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('headers',        Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Email Subject')
    ->addColumn('size',           Varien_Db_Ddl_Table::TYPE_INTEGER, 11,    $req, 'Email Size')
    ->addColumn('is_parsed',      Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,  $req, 'flag if message has been parsed')
    ->addColumn('recipient_id',   Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Linked original recipient id, if parsed')
    ->addColumn('campaign_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Linked original campaign id, if parsed')
    ->addColumn('sent_at',        Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Sent time, if parsed')
    ->addColumn('email',          Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'From header, if parsed')
    ->addColumn('subject',        Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'Email subject')
    ->addColumn('message_id',     Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'Email Message ID')
    ->addColumn('message',        Varien_Db_Ddl_Table::TYPE_TEXT, 512,      $opt, 'Email message (shorten)')
    ->addColumn('type',           Varien_Db_Ddl_Table::TYPE_CHAR, 2,        $opt, 'Bounce type, hard, soft,...')
    ->addColumn('status_code',    Varien_Db_Ddl_Table::TYPE_CHAR, 6,        $opt, 'Bounce type, hard, soft,...')
    ->addColumn('is_arf',         Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1,     $opt, 'Is ARF (aka Feedback Loop)')
    ->addColumn('arf_type',       Varien_Db_Ddl_Table::TYPE_TEXT, 32,       $opt, 'ARF (aka Feedback Loop) type')
    ->addColumn('is_autoreply',   Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1,     $opt, 'Is Autoreply')
    ->addIndex('IDX_PARSED', array('is_parsed', 'type'))
    ->addIndex('IDX_ARF', array('is_arf'))
    ->addIndex('IDX_AUTOREPLY', array('is_autoreply'));
    

$installer->addForeignKey($inboxTable, $recipientTable, 'recipient_id');
$installer->addForeignKey($inboxTable, $campaignTable, 'campaign_id');
    
$connection->dropTable($inboxTable->getName());
$connection->createTable($inboxTable);



/********************************************************************
 * Setup Outbox Table
 ********************************************************************/
$outboxTable = $connection->newTable($installer->getTable('mzax_emarketing/outbox_email'))
    ->addColumn('email_id',       Varien_Db_Ddl_Table::TYPE_INTEGER, 11,    $uid, 'Email ID')
    ->addColumn('recipient_id',   Varien_Db_Ddl_Table::TYPE_INTEGER, 11,    $req, 'Recipient ID')
    ->addColumn('campaign_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, 11,    $req, 'Campaign ID')
    ->addColumn('created_at',     Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'Creation Time')
    ->addColumn('status',         Varien_Db_Ddl_Table::TYPE_TINYINT, null,  $req, 'Status (1=SENT, 2=EXPIRED, 3=FAILED)')
    ->addColumn('expire_at',      Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Expire Time, dont\' send out email past this date')
    ->addColumn('sent_at',        Varien_Db_Ddl_Table::TYPE_DATETIME, null, $opt, 'Send Time')
    ->addColumn('to',             Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'The recipient email address')
    ->addColumn('subject',        Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $opt, 'Email Subject')
    ->addColumn('body_text',      Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $opt, 'Email Plain Text Body')
    ->addColumn('body_html',      Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $opt, 'Email HTML Body')
    ->addColumn('domain',         Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'The domain of the email address')
    ->addColumn('message_id',     Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Unique email message id')
    ->addColumn('time_filter',    Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Time of day filter')
    ->addColumn('day_filter',     Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Allowed days (So,Mo,..) [0,1...]')
    ->addColumn('mail',           Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Serialized Zend_Mail object')
    ->addColumn('log',            Varien_Db_Ddl_Table::TYPE_TEXT, $text,    $req, 'Log for this email')
    ->addIndex('IDX_EXPIRE', array('expire_at'));


$installer->addForeignKey($outboxTable, $recipientTable, 'recipient_id');
$installer->addForeignKey($outboxTable, $campaignTable, 'campaign_id');

$connection->dropTable($outboxTable->getName());
$connection->createTable($outboxTable);





/********************************************************************
 * Setup Goal Table
********************************************************************/
$goalTable = $connection->newTable($installer->getTable('mzax_emarketing/goal'))
    ->addColumn('goal_id',      Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Event ID')
    ->addColumn('object_type',  Varien_Db_Ddl_Table::TYPE_TINYINT, null,  $req, 'Event type (1=order, 2=signup)')
    ->addColumn('object_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Related Object ID (order_id, customer_id,..)')
    ->addColumn('recipient_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Recipient ID')
    ->addColumn('click_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'The orginal click id that created that goal')
    ->addIndex('IDX_OBJECT', array('object_type', 'object_id'));


$installer->addForeignKey($goalTable, $clickTable, 'click_id');
$installer->addForeignKey($goalTable, $recipientTable, 'recipient_id');

$connection->dropTable($goalTable->getName());
$connection->createTable($goalTable);







/*********************************************************************************************
 * 
 * REPORT TABLES
 * the following tables are used for reporting and data aggregation.
 * 
 ********************************************************************************************/
    



$metrics = array('view', 'click', 'bounce', 'optout');





/********************************************************************
 * Setup Report Enum Table
 ********************************************************************/
$reportEnumTable = $connection->newTable($installer->getTable('mzax_emarketing/report_enum'))
    ->addColumn('value_id',      Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $uid, 'Value ID')
    ->addColumn('value',         Varien_Db_Ddl_Table::TYPE_TEXT, $varchar, $req, 'Value')
    ->setOption('charset', 'ascii')
    ->setOption('collate', 'ascii_bin')
    ->setOption('type', 'MYISAM');

$connection->dropTable($reportEnumTable->getName());
$connection->createTable($reportEnumTable);




/********************************************************************
 * Setup Conversion Tracker Goal Table
 ********************************************************************/
$goalTable = $connection->newTable($installer->getTable('mzax_emarketing/conversion_tracker_goal'))
    ->addColumn('tracker_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Tracker ID')
    ->addColumn('campaign_id',    Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Campaign ID')
    ->addColumn('goal_id',        Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Goal ID (can be a order_id or anything unqiue for that tracker)')
    ->addColumn('goal_time',      Varien_Db_Ddl_Table::TYPE_DATETIME, null, $req, 'The time of the goal event')
    ->addColumn('goal_value',     Varien_Db_Ddl_Table::TYPE_DECIMAL, '9,2', $req, 'The value of the goal event')
    ->addColumn('recipient_id',   Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $opt, 'Optional Recipient ID')
    ->addIndex('PRIMARY', array('tracker_id', 'campaign_id', 'goal_id'), $primaryIndex)
    ->setOption('type', 'MYISAM');

$connection->dropTable($goalTable->getName());
$connection->createTable($goalTable);





/********************************************************************
 * Setup Campaign Report Table
 ********************************************************************/
$reportTable = $connection->newTable($installer->getTable('mzax_emarketing/report'))
    ->addColumn('campaign_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Campaign ID')
    ->addColumn('variation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,    $var, 'Variation ID')
    ->addColumn('date',         Varien_Db_Ddl_Table::TYPE_DATE, null,     $req, 'Date')
    ->addColumn('sendings',     Varien_Db_Ddl_Table::TYPE_INTEGER, 10,    $int, 'How many emails got sent')
    ->addIndex('PRIMARY', array('campaign_id', 'date', 'variation_id'), $primaryIndex)
    ->setOption('type', 'MYISAM');

// Default fields to track
foreach ($metrics as $metric) {
    $reportTable->addColumn("{$metric}s",     Varien_Db_Ddl_Table::TYPE_INTEGER, 10,    $int, "number of {$metric}s");
    $reportTable->addColumn("{$metric}_rate", Varien_Db_Ddl_Table::TYPE_DECIMAL, '6,3', $int, "{$metric} rate");
}

$connection->dropTable($reportTable->getName());
$connection->createTable($reportTable);

    
    
    




/********************************************************************
 * Setup Campaign Report Conversion Table
 ********************************************************************/
$reportConversionTable = $connection->newTable($installer->getTable('mzax_emarketing/report_conversion'))
    ->addColumn('campaign_id',      Varien_Db_Ddl_Table::TYPE_INTEGER, null,   $req, 'Campaign ID')
    ->addColumn('variation_id',     Varien_Db_Ddl_Table::TYPE_INTEGER, 10,     $var, 'Variation ID')
    ->addColumn('date',             Varien_Db_Ddl_Table::TYPE_DATE, null,      $req, 'Date')
    ->addColumn('tracker_id',       Varien_Db_Ddl_Table::TYPE_SMALLINT, null,  $req, 'Tracker ID')
    ->addColumn('hits',             Varien_Db_Ddl_Table::TYPE_INTEGER, 10,     $int, 'Hits')
    ->addColumn('hit_rate',         Varien_Db_Ddl_Table::TYPE_DECIMAL, '7,4',  $int, 'Hit Rate')
    ->addColumn('hit_revenue',      Varien_Db_Ddl_Table::TYPE_DECIMAL, '9,2',  $int, 'Revenue')
    ->addColumn('hit_revenue_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,5', $int, 'Revenue Rate')
    ->addColumn('hit_revenue_sum',  Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,2', $int, 'Revenue Partial Sum')
    ->addIndex('PRIMARY', array('campaign_id', 'tracker_id', 'date', 'variation_id'), $primaryIndex)
    ->setOption('type', 'MYISAM');

$connection->dropTable($reportConversionTable->getName());
$connection->createTable($reportConversionTable);









/********************************************************************
 * Setup Report Dimension Table
 ********************************************************************/
$reportDimensionTable = $connection->newTable($installer->getTable('mzax_emarketing/report_dimension'))
    ->addColumn('campaign_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Campaign ID')
    ->addColumn('variation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,    $var, 'Variation ID')
    ->addColumn('date',         Varien_Db_Ddl_Table::TYPE_DATE, null,     $req, 'Date')
    ->addColumn('dimension_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'Dimension; agent, hour, country (use enum table)')
    ->addColumn('value_id',     Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'Dimension Value; US,DE,ES, FireFox, Safri (use enum table)')
    ->addColumn('sendings',     Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $int, 'How many emails got sent')
    ->addIndex('PRIMARY', array('campaign_id', 'dimension_id', 'variation_id', 'value_id', 'date'), $primaryIndex)
    ->setOption('type', 'MYISAM');

// Default metrics to track
foreach ($metrics as $metric) {
    $reportDimensionTable->addColumn("{$metric}s", Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $int, "number of {$metric}s");
}

$connection->dropTable($reportDimensionTable->getName());
$connection->createTable($reportDimensionTable);






/********************************************************************
 * Setup Report Dimension Conversion Table
 ********************************************************************/
$reportDimensionConvertionTable = $connection->newTable($installer->getTable('mzax_emarketing/report_dimension_conversion'))
    ->addColumn('campaign_id',  Varien_Db_Ddl_Table::TYPE_INTEGER, null,  $req, 'Campaign ID')
    ->addColumn('variation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,    $var, 'Variation ID')
    ->addColumn('date',         Varien_Db_Ddl_Table::TYPE_DATE, null,     $req, 'Date')
    ->addColumn('dimension_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'Dimension; agent, hour, country (use enum table)')
    ->addColumn('value_id',     Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'Dimension Value; US,DE,ES, FireFox, Safri (use enum table)')
    ->addColumn('tracker_id',   Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $req, 'Tracker ID')
    ->addColumn('hits',         Varien_Db_Ddl_Table::TYPE_SMALLINT, null, $int, 'Hits')
    ->addColumn('hit_value',    Varien_Db_Ddl_Table::TYPE_DECIMAL, '9,2', $int, 'Revenue')
    ->addIndex('PRIMARY', array('campaign_id', 'dimension_id', 'variation_id', 'tracker_id', 'value_id', 'date'), $primaryIndex)
    ->setOption('type', 'MYISAM');

$connection->dropTable($reportDimensionConvertionTable->getName());
$connection->createTable($reportDimensionConvertionTable);











/*********************************************************************************************
 *
 * INIT SETUP
 * Add some basic sample data
 *
 ********************************************************************************************/




/* @var $helper Mzax_Emarketing_Helper_Data */
$helper = Mage::helper('mzax_emarketing');


/**
 * Add a sample template to get started with
 * 
 */
$sampleTemplate = dirname(__FILE__) . DS . 'sample.mzax.template';
if (file_exists($sampleTemplate)) {
    /* @var $template Mzax_Emarketing_Model_Template */
    $template = Mage::getModel('mzax_emarketing/template');
    $template->loadFromFile($sampleTemplate);
    $template->save();
}



/**
 * Add default conversion trackers
 * 
 */

/* @var $tracker Mzax_Emarketing_Model_Conversion_Tracker */
$trackerFile = dirname(__FILE__) . DS . 'indirect-orders.mzax.tracker';
if (file_exists($trackerFile)) {
    $tracker = Mage::getModel('mzax_emarketing/conversion_tracker');
    $tracker->loadFromFile($trackerFile);
    $tracker->setIsActive(true);
    $tracker->setCampaignIds('*');
    $tracker->save();
    $tracker->setAsDefault();
}

$trackerFile = dirname(__FILE__) . DS . 'direct-orders.mzax.tracker';
if (file_exists($trackerFile)) {
    $tracker = Mage::getModel('mzax_emarketing/conversion_tracker');
    $tracker->loadFromFile($trackerFile);
    $tracker->setIsActive(true);
    $tracker->setCampaignIds('*');
    $tracker->save();
}






$store = Mage::app()->getDefaultStoreView();
if (!$store) {
    $store = Mage::app()->getStore(Mage_Core_Model_App::DISTRO_STORE_ID);
}
if (!$store) {
    $store = Mage::app()->getStore(Mage_Core_Model_App::DISTRO_STORE_CODE);
}
if (!$store) {
    $store = Mage::app()->getStore();
}


/* @var $campaign Mzax_Emarketing_Model_Campaign */
$campaign = Mage::getModel('mzax_emarketing/campaign');
$campaign->setName($helper->__('My first email campaign'));
$campaign->setProvider('customer');
$campaign->setStoreId($store->getId());


$installer->endSetup();



