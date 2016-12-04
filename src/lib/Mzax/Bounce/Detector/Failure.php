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




/*
 *
* @see http://de.wikipedia.org/wiki/Bounce_Message
* @see http://support.novara.ie/info/index.php/Email_bounce_Message
* @see https://support.google.com/postini/answer/134416?hl=de
* @see http://www.glocksoft.com/email-automation-software/how-to-process-bounced-emails/
* @see http://www.iana.org/assignments/smtp-enhanced-status-codes/smtp-enhanced-status-codes.xhtml
* @see https://support.sendgrid.com/hc/en-us/articles/200181758-Common-SMTP-server-Bounce-responses
*
* @credits https://github.com/cfortune/PHP-Bounce-Handler/
*/

/**
 *
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Bounce_Detector_Failure extends Mzax_Bounce_Detector_Abstract
{


    /**
     * @see https://tools.ietf.org/html/rfc3463#section-2
     * @var string
     */
    const RFC3463_CODE = '[245]\.[01234567]\.[0-9]{1,2}';

    /**
     * @see https://tools.ietf.org/html/rfc2821
     * @var string
     */
    const RFC2821_CODE = '[45][01257][012345]';




    const DEFAULT_STATUS = '5.5.0';





    public static function getPhrases()
    {
        static $phrases;

        if(!$phrases) {
            $filename = dirname(__FILE__) . '/Data/phrases.cnf';
            if (!file_exists($filename)) {
                throw new Exception("Missing file '$file'.");
            }
            $data = file_get_contents($filename);

            // remove comments and empty lines
            $data = preg_replace('/^#(.*)$/m', '', $data);
            $data = preg_replace('/^\s*\n/m', '', $data);
            $data = trim($data);

            $data = preg_split('/^\[([0-9.]+)\](?:\s*([0-9+-]+))$/m', $data, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            //var_dump($data); exit;
            $phrases = array();
            for($i = 0; $i< count($data); $i+=3) {
                $code = $data[$i];
                $sort = (float) $data[$i+1];
                $rows = explode("\n", trim($data[$i+2]));

                if(!isset($phrases[$sort])) {
                    $phrases[$sort] = array();
                }
                $phrases[$sort][$code] = $rows;
            }
            krsort($phrases, SORT_NUMERIC);

            //var_dump($phrases); exit;

        }
        return $phrases;
    }


    protected static $subjects = array(
        #EN
        'delivery errors',
        'delivery failure',
        'delivery has failed',
        'delivery notification',
        'delivery problem',
        'delivery reports about your email',
        'delivery status notif',
        'failure delivery',
        'failure notice',
        'mail delivery fail',
        'mail delivery system',
        'mailserver notification',
        'mail status report',
        'mail system error',
        'mail transaction failed',
        'mdaemon notification',
        'message delayed',
        'nondeliverable mail',
        'returned email',
        'returned mail',
        'returned to sender',
        'returning message to sender',
        'spam eater',
        'undeliverable',
        'undelivered mail',
        'warning: message',
        'deletver reports about your email',
        'returned email',
        #NL
        'Onbestelbaar',
        #FR
        'Non remis',
        #ES
        'No se puede entregar',
    );



    public static function normalizeSubject(&$subject)
    {
        $subject = preg_replace('/\W+/', ' ', $subject);
        $subject = str_replace('mail', 'email', $subject);
    }


    public function isFailure(Mzax_Bounce_Message $message)
    {
        $from = $this->findEmail($message->getFrom());

        /* FROM CHECK */
        if(preg_match("/^(postmaster|mailer-daemon)\@?/i", $from)) {
            $message->info('bounce_justification', 'From: ' . $from);
            return true;
        }


        $subject = $message->getSubject();
        self::normalizeSubject($subject);

        if($subject) {
            foreach(self::$subjects as $needle) {
                if(stripos($subject, $needle) !== false) {
                    $message->info('bounce_justification', 'Subject: ' . $subject);
                    return true;
                }
            }
        }


        return false;
    }




    public function inspect(Mzax_Bounce_Message $message)
    {
        if(!$this->isFailure($message)) {
            return;
        }

        $status = $this->detectStatus($message);

        $message->info('type', Mzax_Bounce::TYPE_BOUNCE);
        $message->info('status', $status);

        // try to find recipient in body text
        if(!$message->info('recipient')) {
            // assume recipient email in body text
            if($email = $this->findEmail($message->asString())) {
                $message->info('recipient', $email);
            }
        }

        return true;
    }



    public function detectStatus(Mzax_Bounce_Message $message)
    {
        $body = $message->asString();
        $body = strtolower($body);
        $body = preg_replace('/[\s]+/', ' ', $body);


        $RFC3463 = self::RFC3463_CODE;
        $RFC2821 = self::RFC2821_CODE;



        // e.g. 421 #4.7.0  |  421-4.7.1/
        if(preg_match("/{$RFC2821}[- ]#?({$RFC3463})/i" ,$body, $matches)) {
            $message->info('found_phrase', strtolower($matches[0]));
            return $matches[1];
        }


        // e.g. Diagnostic-Code: smtp; 41 4.5.12
        if(preg_match("/diagnostic[- ]code: smtp; ?\d\d\ ({$RFC3463})/i" ,$body, $matches)) {
            $message->info('found_phrase', strtolower($matches[0]));
            return $matches[1];
        }


        // e.g. Status: 4.5.12
        if(preg_match("/status: ({$RFC3463})/i" ,$body, $matches)) {
            $message->info('found_phrase', strtolower($matches[0]));
            return $matches[1];
        }

        // Use common phrases to detect status code
        foreach(self::getPhrases() as $sort => $phrasesByCodes) {
            foreach($phrasesByCodes as $code => $phrases) {
                foreach($phrases as $phrase) {
                    if(stripos($body, $phrase) !== false) {
                        $message->info('found_phrase', strtolower($phrase));
                        $message->info('found_phrase_sort', $sort);
                        return $code;
                    }
                }
            }
        }


        // https://tools.ietf.org/html/rfc3463#section-2
        if(preg_match("/\W($RFC3463)\W/", $body, $matches)) {
            $message->info('found_phrase', strtolower($matches[0]));
            return $matches[1];
        }



        // search for RFC2821 return code
        // thanks to mark.tolman@gmail.com
        // Maybe at some point it should have it's own place within the main parsing scheme (at line 88)
        if(preg_match("/\]?: ($RFC2821) /", $body, $matches) ||
           preg_match("/^($RFC2821) (?:.*?)(?:denied|inactive|deactivated|rejected|disabled|unknown|no such|not (?:our|activated|a valid))+/i", $body, $matches))
        {
            // map RFC2821 -> RFC3463 codes
            $map = array(
                '5.1.1' => '550, 551, 553, 554', // perm error
                '4.2.2' => '452, 552',  // mailbox full
                '4.3.2' => '450, 421',  // temp unavailable
            );
            foreach($map as $convert => $values) {
                if(strpos($values, $matches[1]) !== false) {
                    return $convert;
                }
            }
        }

        return self::DEFAULT_STATUS;
    }



}

