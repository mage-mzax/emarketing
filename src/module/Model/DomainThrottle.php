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



class Mzax_Emarketing_Model_DomainThrottle
{


    /**
     *
     * @var Mzax_Presist
     */
    protected $_presist;



    /**
     * The number of seconds for the threshold.
     * Works togher with send threshold
     *
     * @var integer
     */
    protected $_defaultTimeThreshold = 300;



    /**
     * The number of emails that can be send within the
     * threshold time before we rest the domain.
     *
     * @var integer
     */
    protected $_defaultSendThreshold = 50;


    /**
     * The number of seconds to wait before sending an email
     * to this domain again after the send threshold exceeded
     *
     * @var integer
     */
    protected $_defaultRestTime = 600;


    /**
     * Domain specific options
     *
     * @var array
     */
    protected $_domainOptions = array();






    public function __construct()
    {
        $this->_presist = new Mzax_Presist;
        $this->_presist->open(Mage::getBaseDir('tmp') . DS . 'domain_throttle.data');

        if (empty($this->_presist->data)) {
            $this->_presist->data = array();
        }
    }



    /**
     * Is given domain resting
     *
     * 0 = no resting, send out emails
     *
     * @param string $domain
     * @return integer
     */
    public function isResting($domain)
    {
        $domain = strtolower($domain);
        $data = &$this->_presist->data;
        if (!$domain || !isset($data[$domain])) {
            return false;
        }
        list($restTill, $lastTime, $count) = $data[$domain];

        return max(0, $restTill - time());
    }




    /**
     * Calculate current counter value
     *
     * The counter value can slowly decrease if enough
     * time passes without any send outs
     *
     * @param string $domain
     * @param array $entry
     * @return number
     */
    protected function _calcCounter($domain, $entry)
    {
        $timeThereshold = $this->getTimeThreshold($domain);

        list($restTill, $lastTime, $count) = $entry;

        // @todo maybe there is better formular
        return $count - max(floor(($now - $lastTime)/$timeThereshold), $count);
    }




    /**
     * Touch domain and increase the internal counter
     * and check if we have reached the threshold and
     * should wait with the next email
     *
     * @param string $domain
     */
    public function touchDomain($domain)
    {
        $domain = strtolower($domain);

        $now = time();

        $data = &$this->_presist->data;


        if (!isset($data[$domain])) {
            $restTill = false;
            $lastTime = $now;
            $count = 1;
        }
        else {
            list($restTill, $lastTime, $count) = $data[$domain];
            $count = $this->_calcCounter($domain, $data[$domain]);
            $count++;
        }

        if ($count >= $this->getSendThreshold($domain)) {
            $restTill = $now + $this->getRestTime($domain);
        }

        $data[$domain] = array($restTill, $lastTime, $count);
    }



    /**
     * Check all records and remove the unrelevant ones
     *
     *  @return Mzax_Emarketing_Model_Domain
     */
    public function purge()
    {
        $data = &$this->_presist->data;
        foreach ($data as $domain => $entry) {
            if (!$this->_calcCounter($domain, $data[$domain])) {
                unset($data[$domain]);
            }
        }
        return $this;
    }



    public function __destruct()
    {
        try {
            $this->_presist->close();
        }
        catch(Exception $e) {
            // ignore
        }
    }






    public function addDomainOption($domain, $timeThreshold, $sendThreshold, $restTime)
    {
        $domain = strtolower($domain);
        $this->_domainOptions[$domain] = array(
            'time_threshold'  => (int) $timeThreshold,
            'send_threshold'  => (int) $sendThreshold,
            'rest_time'       => (int) $restTime
        );
        return $this;
    }



    public function setTimeThreshold($value)
    {
        $this->_defaultTimeThreshold = (int) $value;
        return $this;
    }


    public function setSendThreshold($value)
    {
        $this->_defaultSendThreshold = (int) $value;
        return $this;
    }

    public function setRestTime($value)
    {
        $this->_defaultRestTime = (int) $value;
        return $this;
    }



    public function getTimeThreshold($domain)
    {
        if (isset($this->_domainOptions[$domain])) {
            return $this->_domainOptions[$domain]['time_threshold'];
        }
        return $this->_defaultTimeThreshold;
    }


    public function getSendThreshold($domain)
    {
        if (isset($this->_domainOptions[$domain])) {
            return $this->_domainOptions[$domain]['send_threshold'];
        }
        return $this->_defaultSendThreshold;
    }

    public function getRestTime($domain)
    {
        if (isset($this->_domainOptions[$domain])) {
            return $this->_domainOptions[$domain]['rest_time'];
        }
        return $this->_defaultRestTime;
    }






}
