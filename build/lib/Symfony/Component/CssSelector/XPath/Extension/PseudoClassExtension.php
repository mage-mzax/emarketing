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
#use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator pseudo-class extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Extension_PseudoClassExtension extends Symfony_Component_CssSelector_XPath_Extension_AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getPseudoClassTranslators()
    {
        return array(
            'root' => array($this, 'translateRoot'),
            'first-child' => array($this, 'translateFirstChild'),
            'last-child' => array($this, 'translateLastChild'),
            'first-of-type' => array($this, 'translateFirstOfType'),
            'last-of-type' => array($this, 'translateLastOfType'),
            'only-child' => array($this, 'translateOnlyChild'),
            'only-of-type' => array($this, 'translateOnlyOfType'),
            'empty' => array($this, 'translateEmpty'),
        );
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateRoot(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition('not(parent::*)');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateFirstChild(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath
            ->addStarPrefix()
            ->addNameTest()
            ->addCondition('position() = 1');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateLastChild(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath
            ->addStarPrefix()
            ->addNameTest()
            ->addCondition('position() = last()');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateFirstOfType(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        if ('*' === $xpath->getElement()) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException('"*:first-of-type" is not implemented.');
        }

        return $xpath
            ->addStarPrefix()
            ->addCondition('position() = 1');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateLastOfType(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        if ('*' === $xpath->getElement()) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException('"*:last-of-type" is not implemented.');
        }

        return $xpath
            ->addStarPrefix()
            ->addCondition('position() = last()');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateOnlyChild(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath
            ->addStarPrefix()
            ->addNameTest()
            ->addCondition('last() = 1');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     *
     * @throws Symfony_Component_CssSelector_Exception_ExpressionErrorException
     */
    public function translateOnlyOfType(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        if ('*' === $xpath->getElement()) {
            throw new Symfony_Component_CssSelector_Exception_ExpressionErrorException('"*:only-of-type" is not implemented.');
        }

        return $xpath->addCondition('last() = 1');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateEmpty(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition('not(*) and not(string-length())');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pseudo-class';
    }
}
