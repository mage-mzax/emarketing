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
$connection = $installer->getConnection();


// fix index - it should not be unique
$recipientTable = $installer->getTable('mzax_emarketing/recipient');
$campaignTable  = $installer->getTable('mzax_emarketing/campaign');

$installer->dropForeignKey($recipientTable, $campaignTable, 'campaign_id');

$connection->dropIndex($recipientTable, 'IDX_CAMPAIGN_CREATED');
$connection->addIndex($recipientTable, 'IDX_CAMPAIGN_CREATED', array('campaign_id', 'created_at'));
$installer->addForeignKey($recipientTable, $campaignTable, 'campaign_id');

