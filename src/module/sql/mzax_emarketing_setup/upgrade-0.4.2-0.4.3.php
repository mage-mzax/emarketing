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


$campaignTable = $installer->getTable('mzax_emarketing/campaign');
$inboxTable    = $installer->getTable('mzax_emarketing/inbox_email');
$outboxTable   = $installer->getTable('mzax_emarketing/outbox_email');


$connection->addColumn($campaignTable, 'max_per_recipient', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable' => false,
    'after'    => 'running',
    'comment'  => 'Maximum number of send outs per recipients',
    'default'  => 0
));


$connection->addColumn($inboxTable, 'purged', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable' => false,
    'comment'  => 'Message content is purged to save space',
    'default'  => 0
));


$connection->addColumn($outboxTable, 'purged', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable' => false,
    'comment'  => 'Message content is purged to save space',
    'default'  => 0
));

