<?php
/*
 * NOTICE:
 * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.
 */
/**
 * ua-parser
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 *
 * Released under the MIT license
 */
#namespace UAParser\Util;

#use Symfony\Component\Filesystem\Filesystem;
#use Symfony\Component\Yaml\Yaml;
#use UAParser\Exception\FileNotFoundException;

class UAParser_Util_Converter
{
    /** @var string */
    private $destination;

    /** @var Symfony_Component_Filesystem_Filesystem */
    private $fs;

    /**
     * @param string $destination
     * @param Symfony_Component_Filesystem_Filesystem $fs
     */
    public function __construct($destination, Symfony_Component_Filesystem_Filesystem $fs = null)
    {
        $this->destination = $destination;
        $this->fs = $fs ? $fs : new Symfony_Component_Filesystem_Filesystem();
    }

    /**
     * @param string $yamlFile
     * @param bool $backupBeforeOverride
     * @throws UAParser_Exception_FileNotFoundException
     */
    public function convertFile($yamlFile, $backupBeforeOverride = true)
    {
        if (!$this->fs->exists($yamlFile)) {
            throw UAParser_Exception_FileNotFoundException::fileNotFound($yamlFile);
        }

        $this->doConvert(Symfony_Component_Yaml_Yaml::parse(file_get_contents($yamlFile)), $backupBeforeOverride);
    }

    /**
     * @param string $yamlString
     * @param bool $backupBeforeOverride
     */
    public function convertString($yamlString, $backupBeforeOverride = true)
    {
        $this->doConvert(Symfony_Component_Yaml_Yaml::parse($yamlString), $backupBeforeOverride);
    }

    /**
     * @param array $_regexes
     * @param bool $backupBeforeOverride
     */
    protected function doConvert(array $_regexes, $backupBeforeOverride = true)
    {
        $_regexes = $this->sanitizeRegexes($_regexes);
        $data = "<?php\nreturn " . preg_replace('/\s+$/m', '', var_export($_regexes, true)) . ';';

        $regexesFile = $this->destination . '/_regexes.php';
        if ($backupBeforeOverride && $this->fs->exists($regexesFile)) {

            $currentHash = hash('sha512', file_get_contents($regexesFile));
            $futureHash = hash('sha512', $data);

            if ($futureHash === $currentHash) {
                return;
            }

            $backupFile = $this->destination . '/_regexes-' . $currentHash . '.php';
            $this->fs->copy($regexesFile, $backupFile);
        }

        $this->fs->dumpFile($regexesFile, $data);
    }

    private function sanitizeRegexes(array $_regexes)
    {
        foreach ($_regexes as $groupName => $group) {
            $_regexes[$groupName] = array_map(array($this, 'sanitizeRegex'), $group);
        }

        return $_regexes;
    }

    private function sanitizeRegex(array $regex)
    {
        $regex['regex'] = str_replace('@', '\@', $regex['regex']);

        return $regex;
    }
}
