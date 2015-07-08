<?php
/**
 * Mzax Emarketing (www.mzax.de)
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this Extension in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @version     {{version}}
 * @category    Mzax
 * @package     Mzax_Emarketing
 * @author      Jacob Siefer (jacob@mzax.de)
 * @copyright   Copyright (c) 2015 Jacob Siefer
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * 
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Mail extends Zend_Mail
{
    
    
    public function getBodyText($textOnly = false)
    {
        if($textOnly) {
            return parent::getBodyText(true);
        }
        return null;
    }
    
    
    
    public function getBodyHtml($htmlOnly = false)
    {
        if($htmlOnly) {
            return parent::getBodyHtml(true);
        }
        
        $mime = new Zend_Mime($this->getMimeBoundary());
        $boundaryLine = $mime->boundaryLine($this->EOL);
        $boundaryEnd  = $mime->mimeEnd($this->EOL);
        
        $html = parent::getBodyHtml();
        $text = parent::getBodyText();
        
        $text->disposition = false;
        $html->disposition = false;
        
        $body = $boundaryLine
        . $text->getHeaders($this->EOL)
        . $this->EOL
        . $text->getContent($this->EOL)
        . $this->EOL
        . $boundaryLine
        . $html->getHeaders($this->EOL)
        . $this->EOL
        . $html->getContent($this->EOL)
        . $this->EOL
        . $boundaryEnd;
        
        $mp           = new Zend_Mime_Part($body);
        $mp->type     = Zend_Mime::MULTIPART_ALTERNATIVE;
        $mp->boundary = $mime->boundary();
        
        return $mp;
    }
    
    
    
    
    
    
}