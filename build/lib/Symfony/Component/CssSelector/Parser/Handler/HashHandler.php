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
class Symfony_Component_CssSelector_Parser_Handler_HashHandler implements Symfony_Component_CssSelector_Parser_Handler_HandlerInterface
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
        $match = $reader->findPattern($this->patterns->getHashPattern());

        if (!$match) {
            return false;
        }

        $value = $this->escaping->escapeUnicode($match[1]);
        $stream->push(new Symfony_Component_CssSelector_Parser_Token(Symfony_Component_CssSelector_Parser_Token::TYPE_HASH, $value, $reader->getPosition()));
        $reader->moveForward(strlen($match[0]));

        return true;
    }
}
