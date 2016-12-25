<?php
/*
 * NOTICE:
 * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

#namespace Symfony\Component\CssSelector\Parser\Handler;

#use Symfony\Component\CssSelector\Parser\Reader;
#use Symfony\Component\CssSelector\Parser\TokenStream;

/**
 * CSS selector handler interface.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface Symfony_Component_CssSelector_Parser_Handler_HandlerInterface
{
    /**
     * @param Symfony_Component_CssSelector_Parser_Reader      $reader
     * @param Symfony_Component_CssSelector_Parser_TokenStream $stream
     *
     * @return bool
     */
    public function handle(Symfony_Component_CssSelector_Parser_Reader $reader, Symfony_Component_CssSelector_Parser_TokenStream $stream);
}
