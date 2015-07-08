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
class Mzax_Bounce_Mime_Decode extends Zend_Mime_Decode
{
    
    
    
    /**
     * decodes a mime encoded String and returns a
     * struct of parts with header and body
     *
     * @param  string $message  raw message content
     * @param  string $boundary boundary as found in content-type
     * @param  string $EOL EOL string; defaults to {@link Zend_Mime::LINEEND}
     * @return array|null parts as array('header' => array(name => value), 'body' => content), null if no parts found
     * @throws Zend_Exception
     */
    public static function splitMessageStruct($message, $boundary, $EOL = Zend_Mime::LINEEND)
    {
        $parts = self::splitMime($message, $boundary);
        if (count($parts) <= 0) {
            return null;
        }
        $result = array();
        foreach ($parts as $part) {
            Zend_Mime_Decode::splitMessage($part, $headers, $body, $EOL);
            $result[] = array('header' => $headers,
                              'body'   => $body);
        }
        return $result;
    }
    
    
    
    
    
    /**
     * Split message struct
     *
     * sometimes messages miss the final two dashes and cause
     * trouble, this is a little fix
     *
     * @param string $message
     * @param string $boundary
     * @see Zend_Mime_Decode::splitMessageStruct()
     * @return Ambigous <multitype:, NULL, multitype:multitype:unknown  >
     */
    public static function splitMime($body, $boundary)
    {
        // TODO: we're ignoring \r for now - is this function fast enough and is it safe to asume noone needs \r?
        $body = str_replace("\r", '', $body);
    
        $start = 0;
        $res = array();
        // find every mime part limiter and cut out the
        // string before it.
        // the part before the first boundary string is discarded:
        $p = strpos($body, '--' . $boundary . "\n", $start);
        if ($p === false) {
            // no parts found!
            return array();
        }
    
        // position after first boundary line
        $start = $p + 3 + strlen($boundary);
    
        while (($p = strpos($body, '--' . $boundary . "\n", $start)) !== false) {
            $res[] = substr($body, $start, $p-$start);
            $start = $p + 3 + strlen($boundary);
        }
    
        // no more parts, find end boundary
        $p = strpos($body, '--' . $boundary . '--', $start);
        if ($p===false) {
            //expect invalid mime messages, just add everything to the end
            //throw new Zend_Exception('Not a valid Mime Message: End Missing');
            $res[] = substr($body, $start);
        }
        else {
            // the remaining part also needs to be parsed:
            $res[] = substr($body, $start, $p-$start);
        }
        return $res;
    }
    
    
    
    
    /**
     * Decode a report message
     * e.g.
     * foo: bar
     * bar: foo
     *    foo1
     *    foor
     * var2: foobar
     *
     * to:
     * array(...);
     *
     * @param unknown $report
     * @return NULL|multitype:
     */
    public static function decodeHash($string, $tolower = true)
    {
        $string = trim($string);
        if(empty($string)) {
            return null;
        }
        $string = preg_replace("/[\r\n]+/", "\n", $string);
        $hash = iconv_mime_decode_headers($string, ICONV_MIME_DECODE_CONTINUE_ON_ERROR);
        if(!$hash) {
            return null;
        }
        if($tolower) {
            $hash = array_change_key_case($hash);
            //$hash = array_combine(array_map("strtolower", array_keys($hash)), array_values($hash));
        }
        return $hash;
    }
    
    
    
    
    /**
     * Parse an RFC-822 message
     *
     * this format is quite old and not used anymore but some old
     * devices may still send it
     *
     * @param string $message
     * @return array
     */
    public static function decodeRFC822(&$message)
    {
        try {
            Zend_Mime_Decode::splitMessage(ltrim($message), $headers, $content);
        
            $contentType = isset($headers['content-type']) ? $headers['content-type'] : '';
            if($contentType) {
                $contentType = Zend_Mime_Decode::splitContentType($contentType);
            }
        
            if(isset($contentType['boundary'])) {
                $mimeParts = self::splitMessageStruct($content, $contentType['boundary']);
            } else {
                $mimeParts = array();
            }
        
            $message = array(
                'headers'      => $headers,
                'content'      => $content,
                'mime_parts'   => $mimeParts,
                'content_type' => $contentType,
            );
            return true;
        }
        catch(Exception $e) {
            return false;
        }
    }
    
    
    
}