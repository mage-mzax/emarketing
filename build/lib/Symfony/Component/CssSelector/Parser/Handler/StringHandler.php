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

#use Symfony\Component\CssSelector\Exception\InternalErrorException;
#use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
#use Symfony\Component\CssSelector\Parser\Reader;
#use Symfony\Component\CssSelector\Parser\Token;
#use Symfony\Component\CssSelector\Parser\TokenStream;
#use Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerEscaping;
#use Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns;

/**
 * CSS selector comment handler.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_Parser_Handler_StringHandler implements Symfony_Component_CssSelector_Parser_Handler_HandlerInterface
{
    /**
     * @var Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerPatterns
     */
    private $patterns;

    /**
     * @var Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerEscaping
     */
    private $escaping;

    /**
     * @param Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerPatterns $patterns
     * @param Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerEscaping $escaping
     */
    public function __construct(Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerPatterns $patterns, Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerEscaping $escaping)
    {
        $this->patterns = $patterns;
        $this->escaping = $escaping;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Symfony_Component_CssSelector_Parser_Reader $reader, Symfony_Component_CssSelector_Parser_TokenStream $stream)
    {
        $quote = $reader->getSubstring(1);

        if (!in_array($quote, array("'", '"'))) {
            return false;
        }

        $reader->moveForward(1);
        $match = $reader->findPattern($this->patterns->getQuotedStringPattern($quote));

        if (!$match) {
            throw new Symfony_Component_CssSelector_Exception_InternalErrorException(sprintf('Should have found at least an empty match at %s.', $reader->getPosition()));
        }

        // check unclosed strings
        if (strlen($match[0]) === $reader->getRemainingLength()) {
            throw Symfony_Component_CssSelector_Exception_SyntaxErrorException::unclosedString($reader->getPosition() - 1);
        }

        // check quotes pairs validity
        if ($quote !== $reader->getSubstring(1, strlen($match[0]))) {
            throw Symfony_Component_CssSelector_Exception_SyntaxErrorException::unclosedString($reader->getPosition() - 1);
        }

        $string = $this->escaping->escapeUnicodeAndNewLine($match[0]);
        $stream->push(new Symfony_Component_CssSelector_Parser_Token(Symfony_Component_CssSelector_Parser_Token::TYPE_STRING, $string, $reader->getPosition()));
        $reader->moveForward(strlen($match[0]) + 1);

        return true;
    }
}
