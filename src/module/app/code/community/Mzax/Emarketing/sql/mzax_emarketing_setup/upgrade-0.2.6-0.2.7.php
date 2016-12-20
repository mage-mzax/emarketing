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

$campaignTable = $installer->getTable('mzax_emarketing/campaign');


$connection->addColumn($campaignTable, 'description', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'   => 1024,
    'after'    => 'name',
    'comment'  => 'Description'
));

$connection->addColumn($campaignTable, 'tags', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'   => 255,
    'after'    => 'name',
    'comment'  => 'Tags'
));



$installer->installCampaignPresets(dirname(__FILE__));
