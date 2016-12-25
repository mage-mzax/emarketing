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

#namespace Symfony\Component\CssSelector\XPath\Extension;

#use Symfony\Component\CssSelector\Exception\ExpressionErrorException;
#use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
#use Symfony\Component\CssSelector\Node\FunctionNode;
#use Symfony\Component\CssSelector\Parser\Parser;
#use Symfony\Component\CssSelector\XPath\Translator;
#use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator function extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Extension_FunctionExtension extends Symfony_Component_CssSelector_XPath_Extension_AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctionTranslators()
    {
        return array(
            'nth-child' => array($this, 'translateNthChild'),
            'nth-last-child' => array($this, 'translateNthLastChild'),
            'nth-of-type' => array($this, 'translateNthOfType'),
            'nth-last-of-type' => array($this, 'translateNthLastOfType'),
            'contains' => array($this, 'translateContains'),
            'lang' => array($this, 'translateLang'),
        );
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     * @param bool         $last
     * @param bool         $addNameTest
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateNthChild(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function, $last = false, $addNameTest = true)
    {
        try {
            list($a, $b) = Symfony_Component_CssSelector_Parser_Parser::parseSeries($function->getArguments());
        } catch (Symfony_Component_CssSelector_Exception_SyntaxErrorException $e) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Invalid series: %s', implode(', ', $function->getArguments())), 0, $e);
        }

        $xpath->addStarPrefix();
        if ($addNameTest) {
            $xpath->addNameTest();
        }

        if (0 === $a) {
            return $xpath->addCondition('position() = '.($last ? 'last() - '.($b - 1) : $b));
        }

        if ($a < 0) {
            if ($b < 1) {
                return $xpath->addCondition('false()');
            }

            $sign = '<=';
        } else {
            $sign = '>=';
        }

        $expr = 'position()';

        if ($last) {
            $expr = 'last() - '.$expr;
            --$b;
        }

        if (0 !== $b) {
            $expr .= ' - '.$b;
        }

        $conditions = array(sprintf('%s %s 0', $expr, $sign));

        if (1 !== $a && -1 !== $a) {
            $conditions[] = sprintf('(%s) mod %d = 0', $expr, $a);
        }

        return $xpath->addCondition(implode(' and ', $conditions));

        // todo: handle an+b, odd, even
        // an+b means every-a, plus b, e.g., 2n+1 means odd
        // 0n+b means b
        // n+0 means a=1, i.e., all elements
        // an means every a elements, i.e., 2n means even
        // -n means -1n
        // -1n+6 means elements 6 and previous
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateNthLastChild(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function)
    {
        return $this->translateNthChild($xpath, $function, true);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateNthOfType(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function)
    {
        return $this->translateNthChild($xpath, $function, false, false);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateNthLastOfType(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function)
    {
        if ('*' === $xpath->getElement()) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException('"*:nth-of-type()" is not implemented.');
        }

        return $this->translateNthChild($xpath, $function, true, false);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateContains(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function)
    {
        $arguments = $function->getArguments();
        foreach ($arguments as $token) {
            if (!($token->isString() || $token->isIdentifier())) {
                throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(
                    'Expected a single string or identifier for :contains(), got '
                    .implode(', ', $arguments)
                );
            }
        }

        return $xpath->addCondition(sprintf(
            'contains(string(.), %s)',
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($arguments[0]->getValue())
        ));
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateLang(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function)
    {
        $arguments = $function->getArguments();
        foreach ($arguments as $token) {
            if (!($token->isString() || $token->isIdentifier())) {
                throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(
                    'Expected a single string or identifier for :lang(), got '
                    .implode(', ', $arguments)
                );
            }
        }

        return $xpath->addCondition(sprintf(
            'lang(%s)',
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($arguments[0]->getValue())
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'function';
    }
}
