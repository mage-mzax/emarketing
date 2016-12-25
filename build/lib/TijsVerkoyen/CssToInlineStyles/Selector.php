<?php
/*
 * NOTICE:
 * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.
 */
#namespace TijsVerkoyen\CssToInlineStyles;

#use Symfony\Component\CssSelector\CssSelector;
#use Symfony\Component\CssSelector\CssSelectorConverter;
#use Symfony\Component\CssSelector\Exception\ExceptionInterface;

/**
 * CSS to Inline Styles TijsVerkoyen_CssToInlineStyles_Selector class.
 *
 */
class TijsVerkoyen_CssToInlineStyles_Selector
{
    /**
     * The CSS selector
     *
     * @var string
     */
    protected $selector;
    
    /**
     * @param  string $selector The CSS selector
     */
    public function __construct($selector)
    {
        $this->selector = $selector;
    }
    
    public function toXPath()
    {
        try {
            if (class_exists('Symfony_Component_CssSelector_CssSelectorConverter')) {
                $converter = new Symfony_Component_CssSelector_CssSelectorConverter();
                $query = $converter->toXPath($this->selector);
            } else {
                $query = Symfony_Component_CssSelector_CssSelector::toXPath($this->selector);
            }
        } catch (Symfony_Component_CssSelector_Exception_ExceptionInterface $e) {
            $query = null;
        }
        
        return $query;
    }
}
