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
#namespace UAParser\Util\Logfile;

#use UAParser\Exception\ReaderException;

abstract class UAParser_Util_Logfile_AbstractReader implements UAParser_Util_Logfile_ReaderInterface
{
    /** @var UAParser_Util_Logfile_ReaderInterface[] */
    private static $readers = array();

    /**
     * @param string $line
     * @return UAParser_Util_Logfile_ReaderInterface
     */
    public static function factory($line)
    {
        foreach (static::getReaders() as $reader) {
            if ($reader->test($line)) {
                return $reader;
            }
        }
    }

    private static function getReaders()
    {
        if (static::$readers) {
            return static::$readers;
        }

        static::$readers[] = new UAParser_Util_Logfile_ApacheCommonLogFormatReader();

        return static::$readers;
    }

    public function test($line)
    {
        $matches = $this->match($line);

        return isset($matches['userAgentString']);
    }

    public function read($line)
    {
        $matches = $this->match($line);

        if (!isset($matches['userAgentString'])) {
            throw UAParser_Exception_ReaderException::userAgentParserError($line);
        }

        return $matches['userAgentString'];
    }

    protected function match($line)
    {
        if (preg_match($this->getRegex(), $line, $matches)) {
            return $matches;
        }

        return array();
    }

    abstract protected function getRegex();
}
