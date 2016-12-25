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

#use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator combination extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Extension_CombinationExtension extends Symfony_Component_CssSelector_XPath_Extension_AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getCombinationTranslators()
    {
        return array(
            ' ' => array($this, 'translateDescendant'),
            '>' => array($this, 'translateChild'),
            '+' => array($this, 'translateDirectAdjacent'),
            '~' => array($this, 'translateIndirectAdjacent'),
        );
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateDescendant(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath)
    {
        return $xpath->join('/descendant-or-self::*/', $combinedXpath);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateChild(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath)
    {
        return $xpath->join('/', $combinedXpath);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateDirectAdjacent(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath)
    {
        return $xpath
            ->join('/following-sibling::', $combinedXpath)
            ->addNameTest()
            ->addCondition('position() = 1');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateIndirectAdjacent(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, Symfony_Component_CssSelector_XPath_XPathExpr $combinedXpath)
    {
        return $xpath->join('/following-sibling::', $combinedXpath);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'combination';
    }
}
