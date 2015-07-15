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
 * @method Varien_Db_Adapter_Pdo_Mysql getConnection()
 * 
 * @author Jacob Siefer
 *
 */
class Mzax_Emarketing_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    
    
    /**
     * Install campaign presets specified dir
     * 
     * @param string $sourceDir
     * @return Mzax_Emarketing_Model_Resource_Setup
     */
    public function installCampaignPresets($dir)
    {
        /* @var $resource Mzax_Emarketing_Model_Resource_Campaign_Preset */
        $resource = Mage::getResourceSingleton('mzax_emarketing/campaign_preset');
        $pattern  = rtrim($dir, DS) . DS . '*' . $resource::SUFFIX;
        
        foreach(glob($pattern) as $file) {
            $resource->installFile($file, true, 'Mzax - eMarketing');
        }
        
        return $this;
    }
    
    
    
    /**
     * Shortcut for adding foreign keys
     * 
     * @param Varien_Db_Ddl_Table $table
     * @param Varien_Db_Ddl_Table|string $refTable
     * @param string $columnName
     * @return Mzax_Emarketing_Model_Resource_Setup
     */
    public function addForeignKey(Varien_Db_Ddl_Table $table, $refTable, $columnName)
    {
        if( $refTable instanceof Varien_Db_Ddl_Table ) {
            $refTable = $refTable->getName();
        }
        else {
            $refTable = $this->getTable($refTable);
        }
        
        $fkName = $this->getFkName($table->getName(), $columnName, $refTable, $columnName);
                
        $table->addForeignKey($fkName, $columnName, $refTable, $columnName, 
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);
        
        return $this;
    }
    
    
    
}
