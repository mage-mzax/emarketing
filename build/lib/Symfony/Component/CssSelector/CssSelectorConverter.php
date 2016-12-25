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

#namespace Symfony\Component\CssSelector;

#use Symfony\Component\CssSelector\Parser\Shortcut\ClassParser;
#use Symfony\Component\CssSelector\Parser\Shortcut\ElementParser;
#use Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser;
#use Symfony\Component\CssSelector\Parser\Shortcut\HashParser;
#use Symfony\Component\CssSelector\XPath\Extension\HtmlExtension;
#use Symfony\Component\CssSelector\XPath\Translator;

/**
 * Symfony_Component_CssSelector_CssSelectorConverter is the main entry point of the component and can convert CSS
 * selectors to XPath expressions.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Symfony_Component_CssSelector_CssSelectorConverter
{
    private $translator;

    /**
     * @param bool $html Whether HTML support should be enabled. Disable it for XML documents
     */
    public function __construct($html = true)
    {
        $this->translator = new Symfony_Component_CssSelector_XPath_Translator();

        if ($html) {
            $this->translator->registerExtension(new Symfony_Component_CssSelector_XPath_Extension_HtmlExtension($this->translator));
        }

        $this->translator
            ->registerParserShortcut(new Symfony_Component_CssSelector_Parser_Shortcut_EmptyStringParser())
            ->registerParserShortcut(new Symfony_Component_CssSelector_Parser_Shortcut_ElementParser())
            ->registerParserShortcut(new Symfony_Component_CssSelector_Parser_Shortcut_ClassParser())
            ->registerParserShortcut(new Symfony_Component_CssSelector_Parser_Shortcut_HashParser())
        ;
    }

    /**
     * Translates a CSS expression to its XPath equivalent.
     *
     * Optionally, a prefix can be added to the resulting XPath
     * expression with the $prefix parameter.
     *
     * @param string $cssExpr The CSS expression
     * @param string $prefix  An optional prefix for the XPath expression
     *
     * @return string
     */
    public function toXPath($cssExpr, $prefix = 'descendant-or-self::')
    {
        return $this->translator->cssToXPath($cssExpr, $prefix);
    }
}
