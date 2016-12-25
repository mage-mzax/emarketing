<?php
/*
 * NOTICE:
 * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.
 */
/**
 * ua-parser
 *
 * Copyright (c) 2011-2013 Dave Olsen, http://dmolsen.com
 * Copyright (c) 2013-2014 Lars Strojny, http://usrportage.de
 *
 * Released under the MIT license
 */
#namespace UAParser;

#use UAParser\Result\Client;

class UAParser_Parser extends UAParser_AbstractParser
{
    /** @var UAParser_DeviceParser */
    private $deviceParser;

    /** @var UAParser_OperatingSystemParser */
    private $operatingSystemParser;

    /** @var UAParser_UserAgentParser */
    private $userAgentParser;

    /**
     * Start up the parser by importing the data file to $this->_regexes
     *
     * @param array $_regexes
     */
    public function __construct(array $_regexes)
    {
        parent::__construct($_regexes);
        $this->deviceParser = new UAParser_DeviceParser($this->_regexes);
        $this->operatingSystemParser = new UAParser_OperatingSystemParser($this->_regexes);
        $this->userAgentParser = new UAParser_UserAgentParser($this->_regexes);
    }

    /**
     * Sets up some standard variables as well as starts the user agent parsing process
     *
     * @param string $userAgent a user agent string to test, defaults to an empty string
     * @param array $jsParseBits
     * @return UAParser_Result_Client
     */
    public function parse($userAgent, array $jsParseBits = array())
    {
        $client = new UAParser_Result_Client($userAgent);

        $client->ua = $this->userAgentParser->parseUserAgent($userAgent, $jsParseBits);
        $client->os = $this->operatingSystemParser->parseOperatingSystem($userAgent);
        $client->device = $this->deviceParser->parseDevice($userAgent);

        return $client;
    }
}
