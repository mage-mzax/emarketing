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

abstract class UAParser_Result_AbstractSoftware extends UAParser_Result_AbstractClient
{
    /** @var string */
    public $family = 'Other';

    public function toString()
    {
        return $this->family;
    }
}
