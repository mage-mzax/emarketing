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

#namespace Symfony\Component\CssSelector\Parser;

#use Symfony\Component\CssSelector\Exception\InternalErrorException;
#use Symfony\Component\CssSelector\Exception\SyntaxErrorException;

/**
 * CSS selector token stream.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_Parser_TokenStream
{
    /**
     * @var Symfony_Component_CssSelector_Parser_Token[]
     */
    private $tokens = array();

    /**
     * @var bool
     */
    private $frozen = false;

    /**
     * @var Symfony_Component_CssSelector_Parser_Token[]
     */
    private $used = array();

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * @var Symfony_Component_CssSelector_Parser_Token|null
     */
    private $peeked = null;

    /**
     * @var bool
     */
    private $peeking = false;

    /**
     * Pushes a token.
     *
     * @param Symfony_Component_CssSelector_Parser_Token $token
     *
     * @return Symfony_Component_CssSelector_Parser_TokenStream
     */
    public function push(Symfony_Component_CssSelector_Parser_Token $token)
    {
        $this->tokens[] = $token;

        return $this;
    }

    /**
     * Freezes stream.
     *
     * @return Symfony_Component_CssSelector_Parser_TokenStream
     */
    public function freeze()
    {
        $this->frozen = true;

        return $this;
    }

    /**
     * Returns next token.
     *
     * @return Symfony_Component_CssSelector_Parser_Token
     *
     * @throws Symfony_Component_CssSelector_Exception_InternalErrorException If there is no more token
     */
    public function getNext()
    {
        if ($this->peeking) {
            $this->peeking = false;
            $this->used[] = $this->peeked;

            return $this->peeked;
        }

        if (!isset($this->tokens[$this->cursor])) {
            throw new Symfony_Component_CssSelector_Exception_InternalErrorException('Unexpected token stream end.');
        }

        return $this->tokens[$this->cursor++];
    }

    /**
     * Returns peeked token.
     *
     * @return Symfony_Component_CssSelector_Parser_Token
     */
    public function getPeek()
    {
        if (!$this->peeking) {
            $this->peeked = $this->getNext();
            $this->peeking = true;
        }

        return $this->peeked;
    }

    /**
     * Returns used tokens.
     *
     * @return Symfony_Component_CssSelector_Parser_Token[]
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Returns nex identifier token.
     *
     * @return string The identifier token value
     *
     * @throws Symfony_Component_CssSelector_Exception_SyntaxErrorException If next token is not an identifier
     */
    public function getNextIdentifier()
    {
        $next = $this->getNext();

        if (!$next->isIdentifier()) {
            throw Symfony_Component_CssSelector_Exception_SyntaxErrorException::unexpectedToken('identifier', $next);
        }

        return $next->getValue();
    }

    /**
     * Returns nex identifier or star delimiter token.
     *
     * @return null|string The identifier token value or null if star found
     *
     * @throws Symfony_Component_CssSelector_Exception_SyntaxErrorException If next token is not an identifier or a star delimiter
     */
    public function getNextIdentifierOrStar()
    {
        $next = $this->getNext();

        if ($next->isIdentifier()) {
            return $next->getValue();
        }

        if ($next->isDelimiter(array('*'))) {
            return;
        }

        throw Symfony_Component_CssSelector_Exception_SyntaxErrorException::unexpectedToken('identifier or "*"', $next);
    }

    /**
     * Skips next whitespace if any.
     */
    public function skipWhitespace()
    {
        $peek = $this->getPeek();

        if ($peek->isWhitespace()) {
            $this->getNext();
        }
    }
}
