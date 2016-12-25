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
#use Symfony\Component\CssSelector\Parser\Token;
#use Symfony\Component\CssSelector\Parser\TokenStream;

/**
 * CSS selector whitespace handler.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_Parser_Handler_WhitespaceHandler implements Symfony_Component_CssSelector_Parser_Handler_HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Symfony_Component_CssSelector_Parser_Reader $reader, Symfony_Component_CssSelector_Parser_TokenStream $stream)
    {
        $match = $reader->findPattern('~^[ \t\r\n\f]+~');

        if (false === $match) {
            return false;
        }

        $stream->push(new Symfony_Component_CssSelector_Parser_Token(Symfony_Component_CssSelector_Parser_Token::TYPE_WHITESPACE, $match[0], $reader->getPosition()));
        $reader->moveForward(strlen($match[0]));

        return true;
    }
}
