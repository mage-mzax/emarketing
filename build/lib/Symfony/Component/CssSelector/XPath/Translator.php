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

#namespace Symfony\Component\CssSelector\XPath;

#use Symfony\Component\CssSelector\Exception\ExpressionErrorException;
#use Symfony\Component\CssSelector\Node\FunctionNode;
#use Symfony\Component\CssSelector\Node\NodeInterface;
#use Symfony\Component\CssSelector\Node\SelectorNode;
#use Symfony\Component\CssSelector\Parser\Parser;
#use Symfony\Component\CssSelector\Parser\ParserInterface;

/**
 * XPath expression translator interface.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Translator implements Symfony_Component_CssSelector_XPath_TranslatorInterface
{
    /**
     * @var Symfony_Component_CssSelector_Parser_ParserInterface
     */
    private $mainParser;

    /**
     * @var Symfony_Component_CssSelector_Parser_ParserInterface[]
     */
    private $shortcutParsers = array();

    /**
     * @var Symfony_Component_CssSelector_XPath_Extension_ExtensionInterface
     */
    private $extensions = array();

    /**
     * @var array
     */
    private $nodeTranslators = array();

    /**
     * @var array
     */
    private $combinationTranslators = array();

    /**
     * @var array
     */
    private $functionTranslators = array();

    /**
     * @var array
     */
    private $pseudoClassTranslators = array();

    /**
     * @var array
     */
    private $attributeMatchingTranslators = array();

    public function __construct(Symfony_Component_CssSelector_Parser_ParserInterface $parser = null)
    {
        $this->mainParser = $parser ?: new Symfony_Component_CssSelector_Parser_Parser();

        $this
            ->registerExtension(new Symfony_Component_CssSelector_XPath_Extension_NodeExtension())
            ->registerExtension(new Symfony_Component_CssSelector_XPath_Extension_CombinationExtension())
            ->registerExtension(new Symfony_Component_CssSelector_XPath_Extension_FunctionExtension())
            ->registerExtension(new Symfony_Component_CssSelector_XPath_Extension_PseudoClassExtension())
            ->registerExtension(new Symfony_Component_CssSelector_XPath_Extension_AttributeMatchingExtension())
        ;
    }

    /**
     * @param string $element
     *
     * @return string
     */
    public static function getXpathLiteral($element)
    {
        if (false === strpos($element, "'")) {
            return "'".$element."'";
        }

        if (false === strpos($element, '"')) {
            return '"'.$element.'"';
        }

        $string = $element;
        $parts = array();
        while (true) {
            if (false !== $pos = strpos($string, "'")) {
                $parts[] = sprintf("'%s'", substr($string, 0, $pos));
                $parts[] = "\"'\"";
                $string = substr($string, $pos + 1);
            } else {
                $parts[] = "'$string'";
                break;
            }
        }

        return sprintf('concat(%s)', implode($parts, ', '));
    }

    /**
     * {@inheritdoc}
     */
    public function cssToXPath($cssExpr, $prefix = 'descendant-or-self::')
    {
        $selectors = $this->parseSelectors($cssExpr);

        /** @var Symfony_Component_CssSelector_Node_SelectorNode $selector */
        foreach ($selectors as $index => $selector) {
            if (null !== $selector->getPseudoElement()) {
                throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException('Pseudo-elements are not supported.');
            }

            $selectors[$index] = $this->selectorToXPath($selector, $prefix);
        }

        return implode(' | ', $selectors);
    }

    /**
     * {@inheritdoc}
     */
    public function selectorToXPath(Symfony_Component_CssSelector_Node_SelectorNode $selector, $prefix = 'descendant-or-self::')
    {
        return ($prefix ?: '').$this->nodeToXPath($selector);
    }

    /**
     * Registers an extension.
     *
     * @param Symfony_Component_CssSelector_XPath_Extension_ExtensionInterface $extension
     *
     * @return $this
     */
    public function registerExtension(Symfony_Component_CssSelector_XPath_Extension_ExtensionInterface $extension)
    {
        $this->extensions[$extension->getName()] = $extension;

        $this->nodeTranslators = array_merge($this->nodeTranslators, $extension->getNodeTranslators());
        $this->combinationTranslators = array_merge($this->combinationTranslators, $extension->getCombinationTranslators());
        $this->functionTranslators = array_merge($this->functionTranslators, $extension->getFunctionTranslators());
        $this->pseudoClassTranslators = array_merge($this->pseudoClassTranslators, $extension->getPseudoClassTranslators());
        $this->attributeMatchingTranslators = array_merge($this->attributeMatchingTranslators, $extension->getAttributeMatchingTranslators());

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Symfony_Component_CssSelector_XPath_Extension_ExtensionInterface
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function getExtension($name)
    {
        if (!isset($this->extensions[$name])) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Extension "%s" not registered.', $name));
        }

        return $this->extensions[$name];
    }

    /**
     * Registers a shortcut parser.
     *
     * @param Symfony_Component_CssSelector_Parser_ParserInterface $shortcut
     *
     * @return $this
     */
    public function registerParserShortcut(Symfony_Component_CssSelector_Parser_ParserInterface $shortcut)
    {
        $this->shortcutParsers[] = $shortcut;

        return $this;
    }

    /**
     * @param Symfony_Component_CssSelector_Node_NodeInterface $node
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function nodeToXPath(Symfony_Component_CssSelector_Node_NodeInterface $node)
    {
        if (!isset($this->nodeTranslators[$node->getNodeName()])) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Symfony_Component_CssSelector_Node "%s" not supported.', $node->getNodeName()));
        }

        return call_user_func($this->nodeTranslators[$node->getNodeName()], $node, $this);
    }

    /**
     * @param string        $combiner
     * @param Symfony_Component_CssSelector_Node_NodeInterface $xpath
     * @param Symfony_Component_CssSelector_Node_NodeInterface $combinedXpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function addCombination($combiner, Symfony_Component_CssSelector_Node_NodeInterface $xpath, Symfony_Component_CssSelector_Node_NodeInterface $combinedXpath)
    {
        if (!isset($this->combinationTranslators[$combiner])) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Combiner "%s" not supported.', $combiner));
        }

        return call_user_func($this->combinationTranslators[$combiner], $this->nodeToXPath($xpath), $this->nodeToXPath($combinedXpath));
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr    $xpath
     * @param Symfony_Component_CssSelector_Node_FunctionNode $function
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function addFunction(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_Node_FunctionNode $function)
    {
        if (!isset($this->functionTranslators[$function->getName()])) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Function "%s" not supported.', $function->getName()));
        }

        return call_user_func($this->functionTranslators[$function->getName()], $xpath, $function);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $pseudoClass
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function addPseudoClass(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $pseudoClass)
    {
        if (!isset($this->pseudoClassTranslators[$pseudoClass])) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Pseudo-class "%s" not supported.', $pseudoClass));
        }

        return call_user_func($this->pseudoClassTranslators[$pseudoClass], $xpath);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $operator
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function addAttributeMatching(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $operator, $attribute, $value)
    {
        if (!isset($this->attributeMatchingTranslators[$operator])) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException(sprintf('Attribute matcher operator "%s" not supported.', $operator));
        }

        return call_user_func($this->attributeMatchingTranslators[$operator], $xpath, $attribute, $value);
    }

    /**
     * @param string $css
     *
     * @return Symfony_Component_CssSelector_Node_SelectorNode[]
     */
    private function parseSelectors($css)
    {
        foreach ($this->shortcutParsers as $shortcut) {
            $tokens = $shortcut->parse($css);

            if (!empty($tokens)) {
                return $tokens;
            }
        }

        return $this->mainParser->parse($css);
    }
}
