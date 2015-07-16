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



class Mzax_Emarketing_Model_Resource_Campaign_Preset
{
    const SUFFIX = '.mzax.campaign';
    
    
    

    
    
    
    /**
     * Load preset from file
     * 
     * @param Mzax_Emarketing_Model_Campaign_Preset $preset
     * @param string $file
     * @return Mzax_Emarketing_Model_Resource_Campaign_Preset
     */
    public function loadByFile(Mzax_Emarketing_Model_Campaign_Preset $preset, $file)
    {
        $data = $this->_decodeData(file_get_contents($file));
    
        $preset->setData($data);
        $preset->setFile($file);
        $preset->setFilename(basename($file, self::SUFFIX));
        
        return $this;
    }
    
    
    
    /**
     * Load preset by name (filename)
     * 
     * @param Mzax_Emarketing_Model_Campaign_Preset $preset
     * @param string $name
     * @throws Exception
     * @return Mzax_Emarketing_Model_Resource_Campaign_Preset
     */
    public function load(Mzax_Emarketing_Model_Campaign_Preset $preset, $name)
    {
        if(!preg_match('/^[a-z0-9_-]+$/i', $name)) {
            throw new Exception("Invalid preset filename. ($name)");
        }
        $file = $this->getPath() . DS . $name . self::SUFFIX;
        
        if(!file_exists($file)) {
            throw new Mage_Core_Exception(Mage::helper('mzax_emarketing')->__("Could not find any preset by name '%s'", $name));
        }
        
        return $this->loadByFile($preset, $file);
    }
    
    
    /**
     * Install preset from file
     * 
     * @param string $file
     * @param string $overwrite
     * @param string $author
     * @return Mzax_Emarketing_Model_Resource_Campaign_Preset
     */
    public function installFile($file, $overwrite = false, $author = null)
    {
        $name = basename($file, self::SUFFIX);
        $data = file_get_contents($file);
        if(!$data) {
            throw new Exception('File is empty');
        }
        return $this->install($name, $data, $overwrite, $author);
    }
    
    
    
    
    /**
     * Install preset
     * 
     * @param string $name
     * @param string $data
     * @param boolean $overwrite
     * @throws Exception
     * @throws Mage_Core_Exception
     * @return Mzax_Emarketing_Model_Resource_Campaign_Preset
     */
    public function install($name, $data, $overwrite = false, $author = null)
    {
        if(!preg_match('/^[a-z0-9_-]+$/i', $name)) {
            throw new Exception("Invalid preset filename. ($name)");
        }
        
        $file = $this->getPath() . DS . $name . self::SUFFIX;
        
        if(!$overwrite && !file_exists($file)) {
            throw new Mage_Core_Exception(Mage::helper('mzax_emarketing')->__("Preset by nae '%s' already installed", $name));
        }
        
        $data = $this->_decodeData($data);
        if(!$data) {
            throw new Exception("Failed to decode preset");
        }
        if($author) {
            $data['author'] = (string) $author;
        }
        $data['installed_at'] = time();
        
        $data = $this->_encodeData($data);
        
        if(!file_put_contents($file, $data)) {
            throw new Exception("Failed to install preset");
        }
        
        @chmod($file, 0666);
        
        return $this;
    }
    
    
    
    
    /**
     * Retrieve all preset files
     * 
     * @return array
     */
    public function getAllPresetFiles()
    {
        $pattern = $this->getPath() . DS . '*' . self::SUFFIX;
        return glob($pattern);
    }
    
    
    
    /**
     * Retrieve preset storage path
     * 
     * @throws Exception
     * @return string
     */
    public function getPath()
    {
        $path = array(
            Mage::getBaseDir('var'),
            'mzax_emarketing',
            'presets'
        );
        $path = implode(DS, $path);
        
        if(!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new Exception("Failed to make directory ($path)");
        }
        
        return $path;
    }
    
    
    protected function _decodeData($data)
    {
        $data = base64_decode($data);
        return Zend_Json::decode($data);
    }
    
    
    protected function _encodeData($data)
    {
        $data = Zend_Json::encode($data);
        return base64_encode($data);
    }
    
    
}