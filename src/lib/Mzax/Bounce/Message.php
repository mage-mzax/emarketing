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
class Mzax_Bounce_Message extends Mzax_Bounce_Mime_Part
{
    
    
    /**
     * Result of bounce detector
     * 
     * @var array
     */
    protected $_info;

    protected $_certainty;
    
    
    
    
    public function reset()
    {
        $this->_info = array();
        $this->_certainty = array();
        $this->_headers = null;
        $this->_content = null;
        $this->_mimeParts = array();
        $this->_contentType = null;
    }
    
    
    
    /**
     * Retrieve email subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->getHeader('subject');
    }
    
    
    
    /**
     * Retrieve email from header
     *
     * @return string
     */
    public function getFrom()
    {
        $from = $this->getHeader('from');
        if(preg_match('/(?:[a-z0-9_\-]+(?:\.[_a-z0-9\-]+)*@(?:[_a-z0-9\-]+\.)+(?:[a-z]+))/i', $from, $matches)) {
            return $matches[0];
        }
        return '';
    }
    
    
    
    /**
     * Retrieve reference message ids
     * 
     * @return array
     */
    public function getReferences()
    {
        $result = array();
        $references = $this->getHeader('references');
        if(!empty($references)) {
            $references = preg_split('/\s+/', $references);
            foreach($references as $ref) {
                $result[] = trim($ref, '<>');
            }
        }
        
        // we may also find usefull reference in any rfc822 parts
        if($part = $this->getMimePart('text/rfc822')) {
            if($id = $part->getHeader('message-id')) {
                $result[] = trim($id, '<>');
            }
        }
        
        if($part = $this->getMimePart('text/rfc822-headers')) {
            $hash = $part->getDecodedHash();
            if(isset($hash['message-id'])) {
                $result[] = trim($hash['message-id'], '<>');
            }
        }
        
        return $result;
    }
    
    
    
    /**
     * Retrieve date if available
     * 
     * @return string|NULL
     */
    public function getDate()
    {
        foreach(array('date', 'delivery-date', 'created_at') as $header) {
            if($date = $this->getHeader($header)) {
                if($date = strtotime($date)) {
                    return date('Y-m-d H:i:s', $date);
                }
            }
        }
        return null;
    }
    
    
    
    
    
    /**
     * Retrieve header by name if available
     * if not return false
     *
     * @param string $name
     * @return string|boolean
     */
    public function header($name)
    {
        $name = strtolower($name);
        if(isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }
        return false;
    }
    
    
    
    /**
     * Set/Get info string
     *
     * Can retrieve some more detailed information
     * about the email.
     *
     * @param string $name
     * @param mixed $value
     * @return string|boolean
     */
    public function info($name = null, $value = null, $priority = 0)
    {
        if($name === null) {
            return $this->_info;
        }
        if($value !== null) {
            if(!isset($this->_certainty[$name]) || $this->_certainty[$name] <= $priority) {
                $this->_certainty[$name] = (float) $priority;
                $this->_certainty[$name] = $value;
                return $this->_info[$name] = $value;
            }
        }
        if(isset($this->_info[$name])) {
            return $this->_info[$name];
        }
        return false;
    }
    
    
    
    
    /**
     * Checks for any header from the given
     * $headers array, if one is found
     * return the header found, otherwise false
     *
     * @return boolean|string
     */
    public function searchHeader($headers)
    {
        foreach($headers as $key => $header) {
            if(is_integer($key)) {
                $found = $this->getHeader($header);
                if($found) {
                    return $header;
                }
            }
            else {
                $found = $this->getHeader($key);
                if(in_array($found, (array) $header)) {
                    return $key;
                }
            }
        }
        return false;
    }
    
    
    
    
    

    /**
     * Converts the message to a new zend mail object
     * which can be forwared
     * 
     * @return Mzax_Bounce_Mail
     */
    public function forward()
    {
        $mail = new Mzax_Bounce_Mail;
        $mail->setBodyPart($this);
        $mail->setReplyTo($this->getFrom());
        $mail->setSubject($this->getSubject());
        
        return $mail;
    }
    
    
    
}