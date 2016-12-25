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

interface UAParser_Util_Logfile_ReaderInterface
{
    /**
     * @param string $line
     * @return bool
     */
    public function test($line);

    /**
     * @param string $line
     * @return string
     */
    public function read($line);
}
