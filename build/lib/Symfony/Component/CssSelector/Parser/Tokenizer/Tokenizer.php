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

#namespace Symfony\Component\CssSelector\Parser\Tokenizer;

#use Symfony\Component\CssSelector\Parser\Handler;
#use Symfony\Component\CssSelector\Parser\Reader;
#use Symfony\Component\CssSelector\Parser\Token;
#use Symfony\Component\CssSelector\Parser\TokenStream;

/**
 * CSS selector tokenizer.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_Parser_Tokenizer_Tokenizer
{
    /**
     * @var Symfony_Component_CssSelector_Parser_Handler_HandlerInterface[]
     */
    private $handlers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $patterns = new Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerPatterns();
        $escaping = new Symfony_Component_CssSelector_Parser_Tokenizer_TokenizerEscaping($patterns);

        $this->handlers = array(
            new Symfony_Component_CssSelector_Parser_Handler_WhitespaceHandler(),
            new Symfony_Component_CssSelector_Parser_Handler_IdentifierHandler($patterns, $escaping),
            new Symfony_Component_CssSelector_Parser_Handler_HashHandler($patterns, $escaping),
            new Symfony_Component_CssSelector_Parser_Handler_StringHandler($patterns, $escaping),
            new Symfony_Component_CssSelector_Parser_Handler_NumberHandler($patterns),
            new Symfony_Component_CssSelector_Parser_Handler_CommentHandler(),
        );
    }

    /**
     * Tokenize selector source code.
     *
     * @param Symfony_Component_CssSelector_Parser_Reader $reader
     *
     * @return Symfony_Component_CssSelector_Parser_TokenStream
     */
    public function tokenize(Symfony_Component_CssSelector_Parser_Reader $reader)
    {
        $stream = new Symfony_Component_CssSelector_Parser_TokenStream();

        while (!$reader->isEOF()) {
            foreach ($this->handlers as $handler) {
                if ($handler->handle($reader, $stream)) {
                    continue 2;
                }
            }

            $stream->push(new Symfony_Component_CssSelector_Parser_Token(Symfony_Component_CssSelector_Parser_Token::TYPE_DELIMITER, $reader->getSubstring(1), $reader->getPosition()));
            $reader->moveForward(1);
        }

        return $stream
            ->push(new Symfony_Component_CssSelector_Parser_Token(Symfony_Component_CssSelector_Parser_Token::TYPE_FILE_END, null, $reader->getPosition()))
            ->freeze();
    }
}
