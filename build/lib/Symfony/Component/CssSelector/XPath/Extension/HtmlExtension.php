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
#use Symfony\Component\CssSelector\Node\FunctionNode;
#use Symfony\Component\CssSelector\XPath\Translator;
#use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator HTML extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Symfony_Component_CssSelector_XPath_Extension_HtmlExtension extends Symfony_Component_CssSelector_XPath_Extension_AbstractExtension
{
    /**
     * Constructor.
     *
     * @param Symfony_Component_CssSelector_XPath_Translator $translator
     */
    public function __construct(Symfony_Component_CssSelector_XPath_Translator $translator)
    {
        $translator
            ->getExtension('node')
            ->setFlag(Symfony_Component_CssSelector_XPath_Extension_NodeExtension::ELEMENT_NAME_IN_LOWER_CASE, true)
            ->setFlag(Symfony_Component_CssSelector_XPath_Extension_NodeExtension::ATTRIBUTE_NAME_IN_LOWER_CASE, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getPseudoClassTranslators()
    {
        return array(
            'checked' => array($this, 'translateChecked'),
            'link' => array($this, 'translateLink'),
            'disabled' => array($this, 'translateDisabled'),
            'enabled' => array($this, 'translateEnabled'),
            'selected' => array($this, 'translateSelected'),
            'invalid' => array($this, 'translateInvalid'),
            'hover' => array($this, 'translateHover'),
            'visited' => array($this, 'translateVisited'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionTranslators()
    {
        return array(
            'lang' => array($this, 'translateLang'),
        );
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateChecked(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition(
            '(@checked '
            ."and (name(.) = 'input' or name(.) = 'command')"
            ."and (@type = 'checkbox' or @type = 'radio'))"
        );
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateLink(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition("@href and (name(.) = 'a' or name(.) = 'link' or name(.) = 'area')");
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateDisabled(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition(
            '('
                .'@disabled and'
                .'('
                    ."(name(.) = 'input' and @type != 'hidden')"
                    ." or name(.) = 'button'"
                    ." or name(.) = 'select'"
                    ." or name(.) = 'textarea'"
                    ." or name(.) = 'command'"
                    ." or name(.) = 'fieldset'"
                    ." or name(.) = 'optgroup'"
                    ." or name(.) = 'option'"
                .')'
            .') or ('
                ."(name(.) = 'input' and @type != 'hidden')"
                ." or name(.) = 'button'"
                ." or name(.) = 'select'"
                ." or name(.) = 'textarea'"
            .')'
            .' and ancestor::fieldset[@disabled]'
        );
        // todo: in the second half, add "and is not a descendant of that fieldset element's first legend element child, if any."
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateEnabled(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition(
            '('
                .'@href and ('
                    ."name(.) = 'a'"
                    ." or name(.) = 'link'"
                    ." or name(.) = 'area'"
                .')'
            .') or ('
                .'('
                    ."name(.) = 'command'"
                    ." or name(.) = 'fieldset'"
                    ." or name(.) = 'optgroup'"
                .')'
                .' and not(@disabled)'
            .') or ('
                .'('
                    ."(name(.) = 'input' and @type != 'hidden')"
                    ." or name(.) = 'button'"
                    ." or name(.) = 'select'"
                    ." or name(.) = 'textarea'"
                    ." or name(.) = 'keygen'"
                .')'
                .' and not (@disabled or ancestor::fieldset[@disabled])'
            .') or ('
                ."name(.) = 'option' and not("
                    .'@disabled or ancestor::optgroup[@disabled]'
                .')'
            .')'
        );
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
            'ancestor-or-self::*[@lang][1][starts-with(concat('
            ."translate(@%s, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '-')"
            .', %s)]',
            'lang',
            Symfony_Component_CssSelector_XPath_Translator::getXpathLiteral(strtolower($arguments[0]->getValue()).'-')
        ));
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateSelected(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition("(@selected and name(.) = 'option')");
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateInvalid(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition('0');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateHover(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition('0');
    }

    /**
     * @param Symfony_Component_CssSelector_XPath_XPathExpr $xpath
     *
     * @return Symfony_Component_CssSelector_XPath_XPathExpr
     */
    public function translateVisited(Symfony_Component_CssSelector_XPath_XPathExpr $xpath)
    {
        return $xpath->addCondition('0');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'html';
    }
}
