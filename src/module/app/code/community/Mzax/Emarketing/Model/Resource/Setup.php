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
 * Class Mzax_Emarketing_Model_Resource_Setup
 *
 * @method Varien_Db_Adapter_Pdo_Mysql getConnection()
 */
class Mzax_Emarketing_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Install campaign presets specified dir
     *
     * @param string $dir
     *
     * @return $this
     */
    public function installCampaignPresets($dir)
    {
        /* @var $resource Mzax_Emarketing_Model_Resource_Campaign_Preset */
        $resource = Mage::getResourceSingleton('mzax_emarketing/campaign_preset');
        $pattern  = rtrim($dir, DS) . DS . '*' . $resource::SUFFIX;

        foreach (glob($pattern) as $file) {
            $resource->installFile($file, true, 'Mzax - eMarketing');
        }

        return $this;
    }

    /**
     *
     * @param mixed $tableName
     *
     * @return string
     */
    public function getTable($tableName)
    {
        if ($tableName instanceof Varien_Db_Ddl_Table) {
            return $tableName->getName();
        }

        switch (strpos($tableName, '/')) {
            case false:
                return $tableName;
            case 0:
                $tableName = 'mzax_emarketing' . $tableName;
                break;
        }

        return parent::getTable($tableName);
    }

    /**
     * Shortcut for adding foreign keys
     *
     * @param Varien_Db_Ddl_Table|string $table
     * @param Varien_Db_Ddl_Table|string $refTable
     * @param string $columnName
     *
     * @return $this
     */
    public function addForeignKey($table, $refTable, $columnName)
    {
        $refTable = $this->getTable($refTable);

        if ($table instanceof Varien_Db_Ddl_Table) {
            $fkName = $this->getFkName($table->getName(), $columnName, $refTable, $columnName);
            $table->addForeignKey(
                $fkName,
                $columnName,
                $refTable,
                $columnName,
                Varien_Db_Ddl_Table::ACTION_CASCADE,
                Varien_Db_Ddl_Table::ACTION_CASCADE
            );
        } else {
            $table = $this->getTable($table);
            $fkName = $this->getFkName($table, $columnName, $refTable, $columnName);
            $this->getConnection()->addForeignKey($fkName, $table, $columnName, $refTable, $columnName);
        }

        return $this;
    }

    /**
     *
     * @param string $table
     * @param string $refTable
     * @param string $columnName
     *
     * @return $this
     */
    public function dropForeignKey($table, $refTable, $columnName)
    {
        $table = $this->getTable($table);
        $refTable = $this->getTable($refTable);

        $fkName = $this->getFkName($table, $columnName, $refTable, $columnName);
        $this->getConnection()->dropForeignKey($table, $fkName);

        return $this;
    }
}
