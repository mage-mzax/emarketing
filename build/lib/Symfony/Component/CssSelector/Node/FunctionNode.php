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

#namespace Symfony\Component\CssSelector\Node;

#use Symfony\Component\CssSelector\Parser\Token;

/**
 * Represents a "<selector>:<name>(<arguments>)" node.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_Node_FunctionNode extends Symfony_Component_CssSelector_Node_AbstractNode
{
    /**
     * @var Symfony_Component_CssSelector_Node_NodeInterface
     */
    private $selector;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Symfony_Component_CssSelector_Parser_Token[]
     */
    private $arguments;

    /**
     * @param Symfony_Component_CssSelector_Node_NodeInterface $selector
     * @param string        $name
     * @param Symfony_Component_CssSelector_Parser_Token[]       $arguments
     */
    public function __construct(Symfony_Component_CssSelector_Node_NodeInterface $selector, $name, array $arguments = array())
    {
        $this->selector = $selector;
        $this->name = strtolower($name);
        $this->arguments = $arguments;
    }

    /**
     * @return Symfony_Component_CssSelector_Node_NodeInterface
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Symfony_Component_CssSelector_Parser_Token[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus(new Symfony_Component_CssSelector_Node_Specificity(0, 1, 0));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $arguments = implode(', ', array_map(function (Symfony_Component_CssSelector_Parser_Token $token) {
            return "'".$token->getValue()."'";
        }, $this->arguments));

        return sprintf('%s[%s:%s(%s)]', $this->getNodeName(), $this->selector, $this->name, $arguments ? '['.$arguments.']' : '');
    }
}
