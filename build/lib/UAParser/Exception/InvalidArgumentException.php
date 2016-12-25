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

#use UAParser_Exception_InvalidArgumentException as BaseInvalidArgumentException;

class UAParser_Exception_InvalidArgumentException extends BaseInvalidArgumentException
{
    public static function oneOfCommandArguments()
    {
        return new static(
            sprintf('One of the command arguments "%s" is required', join('", "', func_get_args()))
        );
    }

    public static function unexpectedArgument($expectedType, $actualType, $position, $symbol)
    {
        return new static(
            sprintf(
                'Argument %d of %s() is expected to be of type "%s", got "%s"',
                $position,
                $symbol,
                $expectedType,
                $actualType
            )
        );
    }
}
