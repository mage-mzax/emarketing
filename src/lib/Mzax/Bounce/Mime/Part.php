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
class Mzax_Bounce_Mime_Part extends Zend_Mime_Part
{

    const REGEX_LINEND = "/\\r\\n|\\r|\\n/";
    
    
    
    
    protected $_headers = array();
    
    
    protected $_contentType;
    
    
    protected $_mimeParts;
    
    
    public function __construct($data = array())
    {
        if(is_string($data)) {
            Mzax_Bounce_Mime_Decode::splitMessage(ltrim($data), $this->_headers, $this->_content);
        }
        else if(is_array($data)) {
            $this->_headers = $data['header'];
            $this->_content = $data['body'];
        }
        
        $this->type     = $this->getContentType('type');
        $this->charset  = $this->getContentType('charset');
        $this->encoding = $this->getHeader('content-transfer-encoding', $this->encoding);
        $this->boundary = $this->getContentType('boundary');
    }
    
    
    
    /**
     * Retreive header value
     * 
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getHeader($name, $default = null)
    {
        $name = strtolower($name);
        if(isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }
        return $default;
    }
    
    
    
    /**
     * Set header
     * 
     * @param string $name
     * @param string $value
     * @return Mzax_Bounce_Mime_Part
     */
    public function setHeader($name, $value)
    {
        if($value !== null) {
            $name = strtolower($name);
            $this->_headers[$name] = (string) $value;
        }
        return $this;
    }
    
    
    
    
    /**
     * Is multipart
     * 
     * @return boolean
     */
    public function isMultipart()
    {
        return strpos($this->type, 'multipart/') === 0;
    }
    
    
    
    
    /**
     * Retrieve all mime parts
     * 
     * @return array|NULL
     */
    public function getMimeParts()
    {
        if(!$this->_mimeParts && $this->isMultipart()) {
            $this->_mimeParts = array();
            if($boundary = $this->getContentType('boundary')) {
                $parts = Mzax_Bounce_Mime_Decode::splitMessageStruct($this->_content, $boundary);
                if(is_array($parts)) {
                    foreach($parts as $data) {
                        $this->_mimeParts[] = new Mzax_Bounce_Mime_Part($data);
                    }
                }
            }
        }
        return $this->_mimeParts;
    }
    
    

    /**
     * Retrieve mime part by content-type or index
     *
     * @param string $contentType
     * @return Mzax_Bounce_Mime_Part|NULL
     */
    public function getMimePart($index)
    {
        if($mimeParts = $this->getMimeParts()) {
            if(is_int($index)) {
                if(isset($mimeParts[$index])) {
                    return $mimeParts[$index];
                }
            }
            else {
                /* @var $part Mzax_Bounce_Mime_Part */
                foreach($mimeParts as $part) {
                    if($part->type === $index) {
                        return $part;
                    }
                }
            }
        }
        return null;
    }
    
    
    
    /**
     * Try to find a mime part by content type within
     * all multiparts.
     * 
     * @param string $type
     * @return Mzax_Bounce_Mime_Part|NULL
     */
    public function findMinePart($type)
    {
        if($mimeParts = $this->getMimeParts()) {
            /* @var $part Mzax_Bounce_Mime_Part */
            foreach($mimeParts as $part) {
                if(strpos($part->type, $type) === 0) {
                    return $part;
                }
            }
            foreach($mimeParts as $part) {
                if($part->isMultipart()) {
                    if($found = $part->findMinePart($type)) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }
    
    
    
    /**
     * Retrieve all mime parts matching the given content-type
     * 
     * @param string $type
     * @return array
     */
    public function findMineParts($type)
    {
        $result = array();
        if($mimeParts = $this->getMimeParts()) {
            /* @var $part Mzax_Bounce_Mime_Part */
            foreach($mimeParts as $part) {
                if(strpos($part->type, $type) === 0) {
                    $result[] = $part;
                }
                if($part->isMultipart()) {
                    $result = array_merge($result, $part->findMineParts($type));
                }
            }
        }
        return $result;
    }
    
    
    
    /**
     * Retrieve all text like mime parts as a string
     * 
     * @return string
     */
    public function asString()
    {
        $parts = array();
        /* @var $part Mzax_Bounce_Mime_Part */
        foreach($this->findMineParts('text/') as $part) {
            $parts[] = $part->getDecodedContent();
        }
        if(empty($parts)) {
            return $this->getDecodedContent();
        }
        return implode("\n\n----------\n\n", $parts);
    }
    
    
    
    
    /**
     * Retrieve content type data
     * 
     * @return array
     */
    public function getContentType($what = null, $default = null)
    {
        if(!$this->_contentType) {
            if($type = $this->getHeader('content-type')) {
                $this->_contentType = Mzax_Bounce_Mime_Decode::splitHeaderField($type, null, '_type');
                $this->_contentType['type'] = $this->_contentType['_type'];
            }
            else {
                $this->_contentType = array();
            }
        }
        if($what) {
            if(isset($this->_contentType[$what])) {
                return $this->_contentType[$what];
            }
            return $default;
        }
        
        return $this->_contentType;
    }
    
    
    
    /**
     * Retreive decoded content
     * 
     * @return string
     */
    public function getDecodedContent()
    {
        switch(strtolower($this->encoding)) {
            case Zend_Mime::ENCODING_BASE64:
                $content = base64_decode($this->_content);
                break;
                
            case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                if(function_exists('imap_qprint')) {
                    $content = imap_qprint($this->_content);
                }
                else if(function_exists('quoted_printable_decode')) {
                    $content = quoted_printable_decode($this->_content);
                }
                else {
                    //$content = Zend_Mime_Decode::decodeQuotedPrintable($this->_content);
                    $content = iconv_mime_decode($this->_content, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $this->charset);
                }
                break;

            default:
                $content = $this->_content;
                break;
        }
        return str_replace("\r\n", "\n", $content);
    }
    
    
    /**
     * Assume hash like content 
     * like the message/feedback-report has
     * 
     * @return array
     */
    public function getDecodedHash()
    {
        return Mzax_Bounce_Mime_Decode::decodeHash($this->_content);
    }
    
    
    

    /**
     * Get the Content of the current Mime Part in the given encoding.
     *
     * @return string
     */
    public function getContent($EOL = Zend_Mime::LINEEND)
    {
        return preg_replace(self::REGEX_LINEND, $EOL, $this->_content);
    }
    
    
    
    /**
     * Create and return the array of headers for this MIME part
     *
     * @access public
     * @return array
     */
    public function getHeadersArray($EOL = Zend_Mime::LINEEND)
    {
        $result = array();
        
        $headers = array(
            'message-id', 
            'mime-version', 
            'content-type', 
            'importance', 
            'content-id',
            'content-disposition',
            'content-location',
            'content-language',
            'content-transfer-encoding');
        
        foreach($this->_headers as $key => $value) {
            if(in_array($key, $headers)) {
                if(is_array($value)) {
                    $parts = array(array_shift($value));
                    foreach($value as $v) {
                        $parts[] = ' ' . $v;
                    }
                    $value = implode($EOL, $parts);
                }
                else {
                    $value = preg_replace(self::REGEX_LINEND, $EOL, $value);
                }
                $result[] = array($key, $value);
            }
        }
        return $result;
    }
    
    
    
}