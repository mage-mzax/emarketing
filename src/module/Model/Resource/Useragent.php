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



class Mzax_Emarketing_Model_Resource_Useragent extends Mage_Core_Model_Resource_Db_Abstract
{
    
    
    protected $_deviceTypes = array(

        'useragent' => array(
            'Bot'     => '/bot\b|googlebot|crawler|spider|robot|crawling|seeker|wget|slurp|mediapartners/i',
        ),
        'device' => array(
            'Mobile'  => '/Samsung GT|Phone|D5833|HTC|LG-D855|Motorola V9mm|PlayStation Portable|Nexus|MT11i/i',
            'Tablet'  => '/iPad|Motorola Xoom/i',
            'Desktop' => '/Mac OS X|Windows (2000|7|8|NT|Vista|XP)/i',
        ),
        'os' => array(
            'Mobile'  => '/iOs|Windows Mobile|Android|Firefox OS|Symbian OS/i',
            'Desktop' => '/Mac OS X|Windows.(2000|7|8|NT|Vista|XP)|Ubuntu|Linux|Solaris|Debian/i',
        ),
        'ua' => array(
            'Mobile'  => '/Mobile|Android/i',
            'Tablet'  => '/Tablet/i',
            'Desktop' => '/Firefox|Chrome|IE|Opera|Safari/i',
        ),
    );
    
    
    
    /**
     * Initiate resources
     *
     */
    public function _construct()
    {
        $this->_init('mzax_emarketing/useragent', 'useragent_id');
    }
    
    
    
    public function parse($all = false)
    {
        $lock = Mage::helper('mzax_emarketing')->lock('parse_user_agent');
        if (!$lock) {
            return;
        }
        
        
        $adapter = $this->_getWriteAdapter();
        
        $select = $adapter->select();
        $select->from($this->getMainTable(), array('useragent_id', 'useragent'));
        if (!$all) {
            $select->where('parsed = 0');
        }
        
        
        $useragents = $adapter->fetchPairs($select);
        
        $regexFile = Mage::getModuleDir('data', 'Mzax_Emarketing') . DS . 'useragent_regexes.php';
        if (!file_exists($regexFile)) {
            throw new Exception("Regex file for UAParser not found ($regexFile)");
        }
        
        
        $parser = UAParser_Parser::create($regexFile);
        
        foreach ($useragents as $id => $ua) {
            try {
                $result = $parser->parse($ua);
                $bind = array(
                    'ua'           => $result->ua->family,
                    'ua_version'   => $result->ua->major,
                    'os'           => $result->os->family,
                    'os_version'   => $result->os->major,
                    'device'       => $result->device->family,
                    'device_brand' => $result->device->brand,
                    'device_type'  => 'Unknown',
                    'parsed'       => 1
                );
                
                foreach ($this->_deviceTypes as $key => $types) {
                    foreach ($types as $type => $regex) {
                        if (isset($bind[$key])) {
                            if (preg_match($regex, $bind[$key])) {
                                $bind['device_type'] = $type;
                                break 2;
                            }
                        }
                    }
                }
                
                $adapter->update($this->getMainTable(), $bind, $adapter->quoteInto('useragent_id = ?', $id));
                $lock->touch();
            }
            catch(Exception $e) {
                Mage::logException($e);
                if (Mage::getIsDeveloperMode()) {
                    $lock->unlock();
                    throw $e;
                }
            }
        }
        
        $lock->unlock();
        return $this;
    }
    
    
    
    

    
    
    /**
     * Retreive useragent id from useragent
     * 
     * This will insert a new record if none was found
     * 
     * @param string $useragent
     * @return number
     */
    public function getUserAgentId($useragent)
    {
        $adapter = $this->_getWriteAdapter();
        $table  = $this->getMainTable();
        
        $select = $adapter->select()
            ->from($table, $this->getIdFieldName())
            ->where('hash = ?', md5($useragent));
        
        $id = (int) $adapter->fetchOne($select);
        
        if (!$id) {
            $stmt = $adapter->insert($table, array(
                'hash'      => md5($useragent),
                'useragent' => $useragent,
                'parsed'    => 0
            ));
            
            $id = (int) $adapter->lastInsertId($table);
        }
        return $id;
    }
    
    
    
    
    

    
}
