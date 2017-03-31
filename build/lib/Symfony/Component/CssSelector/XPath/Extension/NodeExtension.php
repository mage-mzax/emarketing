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

#use Symfony\Component\CssSelector\Node;
#use Symfony\Component\CssSelector\XPath\Translator;
#use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator node extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Extension_NodeExtension extends Symfony_Component_CssSelector_XPath_Extension_AbstractExtension
{
    const ELEMENT_NAME_IN_LOWER_CASE = 1;
    const ATTRIBUTE_NAME_IN_LOWER_CASE = 2;
    const ATTRIBUTE_VALUE_IN_LOWER_CASE = 4;

    /**
     * @var int
     */
    private $flags;

    /**
     * Constructor.
     *
     * @param int $flags
     */
    public function __construct($flags = 0)
    {
        $this->flags = $flags;
    }

    /**
     * @param int  $flag
     * @param bool $on
     *
     * @return $this
     */
    public function setFlag($flag, $on)
    {
        if ($on && !$this->hasFlag($flag)) {
            $this->flags += $flag;
        }

        if (!$on && $this->hasFlag($flag)) {
            $this->flags -= $flag;
        }

        return $this;
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function hasFlag($flag)
    {
        return $this->flags & $flag;
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeTranslators()
    {
        return array(
            'Selector' => array($this, 'translateSelector'),
            'CombinedSelector' => array($this, 'translateCombinedSelector'),
            'Negation' => array($this, 'translateNegation'),
            'Function' => array($this, 'translateFunction'),
            'Pseudo' => array($this, 'translatePseudo'),
            'Attribute' => array($this, 'translateAttribute'),
            'Class' => array($this, 'translateClass'),
            'Hash' => array($this, 'translateHash'),
            'Element' => array($this, 'translateElement'),
        );
    }

    /**
     * @param Symfony_Component_CssSelector_Node_SelectorNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator        $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateSelector(Symfony_Component_CssSelector_Node_SelectorNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        return $translator->nodeToXPath($node->getTree());
    }

    /**
     * @param Symfony_Component_CssSelector_Node_CombinedSelectorNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator                $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateCombinedSelector(Symfony_Component_CssSelector_Node_CombinedSelectorNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        return $translator->addCombination($node->getCombinator(), $node->getSelector(), $node->getSubSelector());
    }

    /**
     * @param Symfony_Component_CssSelector_Node_NegationNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator        $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateNegation(Symfony_Component_CssSelector_Node_NegationNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $xpath = $translator->nodeToXPath($node->getSelector());
        $subXpath = $translator->nodeToXPath($node->getSubSelector());
        $subXpath->addNameTest();

        if ($subXpath->getCondition()) {
            return $xpath->addCondition(sprintf('not(%s)', $subXpath->getCondition()));
        }

        return $xpath->addCondition('0');
    }

    /**
     * @param Symfony_Component_CssSelector_Node_FunctionNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator        $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateFunction(Symfony_Component_CssSelector_Node_FunctionNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $xpath = $translator->nodeToXPath($node->getSelector());

        return $translator->addFunction($xpath, $node);
    }

    /**
     * @param Symfony_Component_CssSelector_Node_PseudoNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator      $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translatePseudo(Symfony_Component_CssSelector_Node_PseudoNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $xpath = $translator->nodeToXPath($node->getSelector());

        return $translator->addPseudoClass($xpath, $node->getIdentifier());
    }

    /**
     * @param Symfony_Component_CssSelector_Node_AttributeNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator         $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateAttribute(Symfony_Component_CssSelector_Node_AttributeNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $name = $node->getAttribute();
        $safe = $this->isSafeName($name);

        if ($this->hasFlag(self::ATTRIBUTE_NAME_IN_LOWER_CASE)) {
            $name = strtolower($name);
        }

        if ($node->getNamespace()) {
            $name = sprintf('%s:%s', $node->getNamespace(), $name);
            $safe = $safe && $this->isSafeName($node->getNamespace());
        }

        $attribute = $safe ? '@'.$name : sprintf('attribute::*[name() = %s]', Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($name));
        $value = $node->getValue();
        $xpath = $translator->nodeToXPath($node->getSelector());

        if ($this->hasFlag(self::ATTRIBUTE_VALUE_IN_LOWER_CASE)) {
            $value = strtolower($value);
        }

        return $translator->addAttributeMatching($xpath, $node->getOperator(), $attribute, $value);
    }

    /**
     * @param Symfony_Component_CssSelector_Node_ClassNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator     $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateClass(Symfony_Component_CssSelector_Node_ClassNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $xpath = $translator->nodeToXPath($node->getSelector());

        return $translator->addAttributeMatching($xpath, '~=', '@class', $node->getName());
    }

    /**
     * @param Symfony_Component_CssSelector_Node_HashNode $node
     * @param Symfony_Component_CssSelector_XPath_Translator    $translator
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateHash(Symfony_Component_CssSelector_Node_HashNode $node, Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $xpath = $translator->nodeToXPath($node->getSelector());

        return $translator->addAttributeMatching($xpath, '=', '@id', $node->getId());
    }

    /**
     * @param Symfony_Component_CssSelector_Node_ElementNode $node
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateElement(Symfony_Component_CssSelector_Node_ElementNode $node)
    {
        $element = $node->getElement();

        if ($this->hasFlag(self::ELEMENT_NAME_IN_LOWER_CASE)) {
            $element = strtolower($element);
        }

        if ($element) {
            $safe = $this->isSafeName($element);
        } else {
            $element = '*';
            $safe = true;
        }

        if ($node->getNamespace()) {
            $element = sprintf('%s:%s', $node->getNamespace(), $element);
            $safe = $safe && $this->isSafeName($node->getNamespace());
        }

        $xpath = new Symfony_Component_CssSelector_XPath_XPathExpr('', $element);

        if (!$safe) {
            $xpath->addNameTest();
        }

        return $xpath;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'node';
    }

    /**
     * Tests if given name is safe.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isSafeName($name)
    {
        return 0 < preg_match('~^[a-zA-Z_][a-zA-Z0-9_.-]*$~', $name);
    }
}
