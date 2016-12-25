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
#namespace UAParser\Result;

class UAParser_Result_Client extends UAParser_Result_AbstractClient
{
    /** @var UAParser_Result_UserAgent */
    public $ua;

    /** @var UAParser_Result_OperatingSystem */
    public $os;

    /** @var UAParser_Result_Device */
    public $device;

    /** @var string */
    public $originalUserAgent;

    /**
     * @param string $originalUserAgent
     */
    public function __construct($originalUserAgent)
    {
        $this->originalUserAgent = $originalUserAgent;
    }

    public function toString()
    {
        return $this->ua->toString() . '/' . $this->os->toString();
    }
}
