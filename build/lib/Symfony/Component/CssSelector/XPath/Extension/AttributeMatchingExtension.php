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

#use Symfony\Component\CssSelector\XPath\Translator;
#use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator attribute extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Extension_AttributeMatchingExtension extends Symfony_Component_CssSelector_XPath_Extension_AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getAttributeMatchingTranslators()
    {
        return array(
            'exists' => array($this, 'translateExists'),
            '=' => array($this, 'translateEquals'),
            '~=' => array($this, 'translateIncludes'),
            '|=' => array($this, 'translateDashMatch'),
            '^=' => array($this, 'translatePrefixMatch'),
            '$=' => array($this, 'translateSuffixMatch'),
            '*=' => array($this, 'translateSubstringMatch'),
            '!=' => array($this, 'translateDifferent'),
        );
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateExists(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($attribute);
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateEquals(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition(sprintf('%s = %s', $attribute, Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value)));
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateIncludes(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and contains(concat(\' \', normalize-space(%1$s), \' \'), %2$s)',
            $attribute,
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral(' '.$value.' ')
        ) : '0');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateDashMatch(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition(sprintf(
            '%1$s and (%1$s = %2$s or starts-with(%1$s, %3$s))',
            $attribute,
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value),
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value.'-')
        ));
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translatePrefixMatch(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and starts-with(%1$s, %2$s)',
            $attribute,
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value)
        ) : '0');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateSuffixMatch(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and substring(%1$s, string-length(%1$s)-%2$s) = %3$s',
            $attribute,
            strlen($value) - 1,
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value)
        ) : '0');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateSubstringMatch(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition($value ? sprintf(
            '%1$s and contains(%1$s, %2$s)',
            $attribute,
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value)
        ) : '0');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     * @param string    $attribute
     * @param string    $value
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateDifferent(Symfony_Component_CssSelector_XPath_XPathExpr $xpath, $attribute, $value)
    {
        return $xpath->addCondition(sprintf(
            $value ? 'not(%1$s) or %1$s != %2$s' : '%s != %s',
            $attribute,
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral($value)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'attribute-matching';
    }
}
