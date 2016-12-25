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
#namespace UAParser\Exception;

class UAParser_Exception_FetcherException extends UAParser_Exception_DomainException
{
    public static function httpError($resource, $error)
    {
        return new static(
            sprintf('Could not fetch HTTP resource "%s": %s', $resource, $error)
        );
    }
}
