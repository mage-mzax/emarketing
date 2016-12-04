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
 */
class Mzax_Mail_Transport_File extends Zend_Mail_Transport_Abstract
{


    protected $_file;

    protected $_saveHtml = true;


    /**
     * Constructor.
     *
     * @param  string $parameters OPTIONAL (Default: null)
     * @return void
     */
    public function __construct($file = './mails.txt')
    {
        $this->setFile($file);
    }


    /**
     * set folder where emails are saved
     */
    public function setFile($file)
    {
        $this->_file = $file;
        $path = dirname($file);
        if(!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new Exception("Could not create directory '$path'");
        }
        return $this;
    }


    public function setSaveHtml($html)
    {
        $this->_saveHtml = $html;
        return $this;
    }

    public function getSaveHtml()
    {
        return $this->_saveHtml;
    }


    /**
     * get folder where emails are saved
     *
     * @return unknown_type
     */
    public function getFile()
    {
        return $this->_file;
    }



    /**
     * Send mail using PHP native mail()
     *
     * @access public
     * @return void
     * @throws Zend_Mail_Transport_Exception on mail() failure
     */
    public function _sendMail()
    {
        $file = $this->getFile();

        if(!file_exists($file)) {
            touch($file);
            chmod($file, 0777);
        }

        $content = $this->_saveToFile();

        $this->_mail->getBodyHtml();

        $fp = fopen($file, 'a');
        fwrite($fp, $content);
        fclose($fp);

        if($this->getSaveHtml()) {
            $path = dirname($file);

            $subject = $this->_mail->getSubject();

            if(preg_match('/\=\?utf\-8\?B\?([a-z0-9=]+)\?\=/i', $subject, $matches)) {
                $subject = base64_decode($matches[1]);
            }


            $recipients = $this->_mail->getRecipients();
            $recipients = implode('_', $recipients);
            $recipients = str_replace('@','_at_', $recipients);

            $filename = $recipients.'__'.$subject;

            $filename = preg_replace('/([^a-z0-9_]+)/i', '_', $filename);
            $filename = trim($filename, '_');
            $filename = strtolower($filename);


            $body = $this->_mail->getBodyHtml();
            if(!$body) {
                $body = $this->_mail->getBodyText();
                $filename .= '.txt';
            } else {
                $filename .= '.html';
            }

            if($body) {
                $body->encoding = false;
                $content = $body->getContent();

                file_put_contents($path . DS . $filename, $content);
                chmod($path . DS . $filename, 0777);
            }
        }


    }



    protected function _saveToFile()
    {
        $content  = $this->header;
        $content .= "\r\n";

        $content .= $this->body;
        $content .= "\r\n\r\n-----REAL BODY-----\r\n\r\n\r\n";
        $body = $this->_mail->getBodyHtml();
        if($body) {
            $body->encoding = false;
            $content .= $body->getContent();
        }
        $content .= "\r\n\r\n\r\n#############################################################\r\n\r\n\r\n";

        return $content;
    }
}

