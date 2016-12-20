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
 * Class Mzax_Emarketing_Helper_Data
 */
class Mzax_Emarketing_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DAY_START = 1;
    const DAY_END = 2;

    const TIME_UNIT_HOURS = 'hours';
    const TIME_UNIT_DAYS = 'days';
    const TIME_UNIT_WEEKS = 'weeks';
    const TIME_UNIT_MONTHS = 'months';
    const TIME_UNIT_YEARS = 'years';

    const TYPE_STRING = 'string';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_DATE = 'date';
    const TYPE_SELECT = 'select';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_GRID = 'grid';

    /**
     * @var string[][]
     */
    protected $_operatorsByType = array(
        self::TYPE_STRING      => array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'),
        self::TYPE_NUMERIC     => array('==', '!=', '>=', '>', '<=', '<', '()', '!()'),
        self::TYPE_DATE        => array('==', '>=', '<='),
        self::TYPE_SELECT      => array('==', '!=', '()', '!()'),
        self::TYPE_BOOLEAN     => array('==', '!='),
        self::TYPE_MULTISELECT => array('()', '!()'),
        self::TYPE_GRID        => array('()', '!()'),
    );

    /**
     * Retrieve version
     *
     * @return string
     */
    public function getVersion()
    {
        return (string) Mage::getConfig()->getModuleConfig('Mzax_Emarketing')->version;
    }

    /**
     * Can show credits?
     * Credits can be disabled by setting
     * global/mzax_emarketing/hide_credits = true
     * in your local.xml
     *
     * @return boolean
     */
    public function showCredits()
    {
        if (Mage::getConfig()->getNode('global/mzax_emarketing')->is('hide_credits', false)) {
            return false;
        }
        if (Mage::getResourceSingleton('mzax_emarketing/recipient')->countRecipients() <= 1000) {
            return false;
        }
        return true;
    }

    /**
     * Log message
     *
     * @param $message
     *
     * @return $this
     */
    public function log($message)
    {
        $message = call_user_func_array(array($this, '__'), func_get_args());

        Mage::log($message, null, 'mzax_emarketing.log', true);

        return $this;
    }

    /**
     * Create a lock with the given name
     *
     * @param string $name
     * @param int $timeout
     * @param int $maxRunTime
     *
     * @return Mzax_Once|bool
     */
    public function lock($name, $timeout = 5, $maxRunTime = 3600)
    {
        $filename = Mage::getBaseDir('tmp') . DS . 'mzax_emarketing_' . $name . '.lock';
        $lock = Mzax_Once::createLock($filename, $timeout, $maxRunTime);

        return $lock;
    }

    /**
     * Create a compressed random hash using a extra seed
     *
     * @param string $seed
     *
     * @return string
     */
    public function randomHash($seed)
    {
        $hash = md5(
            $seed .
            mt_rand(0, 99999999) .
            microtime()
        );

        return $this->compressHash($hash);
    }

    /**
     * Compress a 32 char hex hash to a 16 char asci hash
     * without loosing to much of "uniqueness"
     *
     * @param string $hash
     *
     * @return string
     */
    public function compressHash($hash)
    {
        $parts = str_split($hash, 2);

        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXQZ';
        $baseLength = strlen($base);
        $compress = '';
        foreach ($parts as $part) {
            $compress .= $base{hexdec($part)%$baseLength};
        }

        return $compress;
    }

    /**
     * Encode magento expressions
     *
     * Magento expressions {{var xzt}} don't work well
     * with HTML as they are not encoded in attributes for instance.
     *
     * We can encode them and decode them later
     *
     * @param string $html
     *
     * @return void
     */
    public function encodeMageExpr(&$html)
    {
        $html = preg_replace_callback('/\{\{(.*?)\}\}/', function ($match) {
            return '#MAGE_EXPR(' . base64_encode($match[0]) . ')';
        }, $html);
    }


    /**
     * Decode magento expressions that have been encoded
     *
     * @param string $html
     *
     * @return void
     */
    public function decodeMageExpr(&$html)
    {
        $html = preg_replace_callback('/#MAGE_EXPR\((.*?)\)/', function ($match) {
            return base64_decode($match[1]);
        }, $html);
    }

    /**
     * Retrieve Store Options
     *
     * @return array
     */
    public function getStoreOptions()
    {
        /** @var Mage_Adminhtml_Model_System_Config_Source_Store $source */
        $source = Mage::getSingleton('adminhtml/system_config_source_store');

        return $source->toOptionArray();
    }

    /**
     * Retrieve Website Options
     *
     * @return array
     */
    public function getWebsitesOptions()
    {
        /** @var Mage_Adminhtml_Model_System_Config_Source_Website $source */
        $source = Mage::getSingleton('adminhtml/system_config_source_website');

        return $source->toOptionArray();
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getDefaultValueByType($type)
    {
        switch ($type) {
            case 'numeric':
                return '1';
        }
        return '';
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getDefaultOperatorByType($type)
    {
        switch ($type) {
            case self::TYPE_BOOLEAN:
            case self::TYPE_NUMERIC:
            case self::TYPE_SELECT:
            case self::TYPE_DATE:
                return '==';
            case self::TYPE_MULTISELECT:
                return '()';
        }
        return '{}';
    }

    /**
     * Retrieve operators by type
     *
     * @param string $type
     * @return array
     */
    public function getOperatorOptionsByType($type)
    {
        $operators = $this->getOperatorOptions();

        if (isset($this->_operatorsByType[$type])) {
            return $this->getOperatorOptions($this->_operatorsByType[$type]);
        }
        return $operators;
    }

    /**
     * Retrieve operator options
     *
     * @param string[] $filter
     *
     * @return string[]
     */
    public function getOperatorOptions($filter = null)
    {
        $options = array(
            '=='  => $this->__('is'),
            '!='  => $this->__('is not'),
            '>='  => $this->__('equals or greater than'),
            '<='  => $this->__('equals or less than'),
            '>'   => $this->__('greater than'),
            '<'   => $this->__('less than'),
            '{}'  => $this->__('contains'),
            '!{}' => $this->__('does not contain'),
            '()'  => $this->__('is one of'),
            '!()' => $this->__('is not one of')
        );

        if (is_array($filter)) {
            $result = array();
            foreach ($options as $key => $label) {
                if (in_array($key, $filter)) {
                    $result[$key] = $label;
                }
            }
            return $result;
        }

        return $options;
    }

    /**
     * Retrieve time unit options
     *
     * @return string[]
     */
    public function getTimeUnitOptions()
    {
        return array(
            self::TIME_UNIT_HOURS => $this->__('hour(s)'),
            self::TIME_UNIT_DAYS => $this->__('day(s)'),
            self::TIME_UNIT_WEEKS => $this->__('week(s)'),
            self::TIME_UNIT_MONTHS => $this->__('month(s)'),
            self::TIME_UNIT_YEARS => $this->__('year(s)')
        );
    }

    /**
     * Calculate date
     *
     * @param number $value
     * @param string $unit
     * @param int $roundDate
     * @param string $sign
     *
     * @return Zend_Date
     */
    public function calcDate($value, $unit, $roundDate = 0, $sign = '-')
    {
        // validate unit
        if (!key_exists($unit, $this->getTimeUnitOptions())) {
            return null;
        }

        $timestamp = strtotime("{$sign}{$value} {$unit}");
        if ($timestamp) {
            // don't calculate with too high numbers (performance issue)
            $timestamp = min(max($timestamp, 0), 10000000000);
            $date = new Zend_Date;
            $date->setTimestamp($timestamp);

            if ($roundDate === self::DAY_START) {
                $date->setHour(0);
                $date->setMinute(0);
                $date->setSecond(0);
            } elseif ($roundDate === self::DAY_END) {
                $date->setHour(23);
                $date->setMinute(59);
                $date->setSecond(59);
            }

            return $date;
        }

        return null;
    }
}
