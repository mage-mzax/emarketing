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

/**
 * Represents a combined node.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_Node_CombinedSelectorNode extends Symfony_Component_CssSelector_Node_AbstractNode
{
    /**
     * @var Symfony_Component_CssSelector_Node_NodeInterface
     */
    private $selector;

    /**
     * @var string
     */
    private $combinator;

    /**
     * @var Symfony_Component_CssSelector_Node_NodeInterface
     */
    private $subSelector;

    /**
     * @param Symfony_Component_CssSelector_Node_NodeInterface $selector
     * @param string        $combinator
     * @param Symfony_Component_CssSelector_Node_NodeInterface $subSelector
     */
    public function __construct(Symfony_Component_CssSelector_Node_NodeInterface $selector, $combinator, Symfony_Component_CssSelector_Node_NodeInterface $subSelector)
    {
        $this->selector = $selector;
        $this->combinator = $combinator;
        $this->subSelector = $subSelector;
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
    public function getCombinator()
    {
        return $this->combinator;
    }

    /**
     * @return Symfony_Component_CssSelector_Node_NodeInterface
     */
    public function getSubSelector()
    {
        return $this->subSelector;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus($this->subSelector->getSpecificity());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $combinator = ' ' === $this->combinator ? '<followed>' : $this->combinator;

        return sprintf('%s[%s %s %s]', $this->getNodeName(), $this->selector, $combinator, $this->subSelector);
    }
}
