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
abstract class Mzax_GeoIp_Adapter_Abstract
{
    
    
    const SECONDS_PER_DAY = 86400;
    
    const HOURLY  = 'Y-m-d-h';
    const DAILY   = 'Y-m-d';
    const WEEKLY  = 'Y-W';
    const MONTHLY = 'Y-m';
    const YEARLY  = 'Y';
    
    
    
    public $timeThreshold = 20;
    
    public $requestThreshold = 10;
    
    public $restTime = 60;
    
    public $requestLimit = 4000;
    
    public $resetPeriode = self::DAILY;
    
    
    /**
     * Retrieve the name of this adapter
     * 
     * @return string
     */
    abstract public function getName();
    
    
    
    /**
     * Time of last request
     *
     * @var integer
     */
    protected $_lastRequestTime;
    
    /**
     * Last request periode
     *
     * @var string
     */
    protected $_lastRequestPeriode;
    
    
    /**
     * Number of requests within the current periode
     * 
     * @var integer
     */
    protected $_requestCount;
    
    
    /**
     * Current threshold count
     * 
     * @var integer
     */
    protected $_requestThresholdCount;
    
    
    /**
     * Current rest time
     * 
     * @var integer
     */
    protected $_restTill;
    
    
    /**
     * Number of errors
     * 
     * @var integer
     */
    protected $_errorCount= 0;
    
    
    
    
    
    /**
     * 
     * @param string $ip
     * @throws Mzax_GeoIp_Excpetion
     * @return Mzax_GeoIp_Request
     */
    final public function fetch(Mzax_GeoIp_Request $request)
    {
        if($this->isResting() || $this->reachedLimit()) {
            throw new Mzax_GeoIp_Excpetion("Max request number reached", Mzax_GeoIp_Adapter_Excpetion::MAX_REQUEST);
        }
        
        $request->adapter = get_class($this);
        $this->_guardThreshold();
    
        
        $this->_lastRequestTime = time();
        $this->_requestCount++;
        
        try {
            $this->_fetch($request);
            $this->_errorCount = 0;
            $request->refine();
            return true;
        }
        catch(Exception $e) {
            $this->_handleError($e);
            //throw new Mzax_GeoIp_Exception("Adapter Error:" . $e->getMessage(), $e->getCode(), $e);
        }
        
        return false;
    }
    
    
    
    /**
     * 
     * @param Mzax_GeoIp_Adapter_Request $request
     */
    abstract protected function _fetch(Mzax_GeoIp_Request $request);
    
    
    
    /**
     * Check if adapter is ready
     * 
     * @return boolean
     */
    public function isReady()
    {
        return !$this->isResting() && !$this->reachedLimit();
    }
    
    
    
    /**
     * Number of seconds that this adapter has to rest
     * before it can be used again
     * 
     * @return number
     */
    public function getRestTime()
    {
        return max($this->_restTill - time(), 0);
    }
    
    
    
    /**
     * Check threshold of connections within a short
     * period of time.
     *
     * Don't send too many requests in a short period of time at once.
     *
     * @return boolean
    */
    protected function _guardThreshold()
    {
        if($this->_lastRequestTime) {
            // check if we are pushing over the time threshold
            if($this->_lastRequestTime >= time() - $this->timeThreshold) {
                $this->_requestThresholdCount++;
            }
            // if not, slowly rest out
            else if($this->_requestThresholdCount > 0) {
                $this->_requestThresholdCount -= max(floor((time() - $this->_lastRequestTime)/$this->timeThreshold), $this->_requestThresholdCount);
            }
            // if we pushed our limits to far, wait for predefined after this request
            // before we try again
            if($this->_requestThresholdCount > $this->requestThreshold) {
                $this->rest($this->restTime);
            }
        }
    }
    
    
    
    /**
     * Check if periodical limit is reached
     *
     * if thats the case wait till next day and try again
     *
     * @return boolean
     */
    public function reachedLimit()
    {
        // reset request count if we start a new periode
        if($this->_lastRequestPeriode != date($this->resetPeriode)) {
            $this->_requestCount = 0;
            $this->_lastRequestPeriode = date($this->resetPeriode);
        }
    
        // if we reached the limit, we need to wait for next periode
        if($this->_requestCount >= $this->requestLimit) {
            return true;
        }
    
        return false;
    }
    
    
    
    /**
     * Is adapter easing
     *
     * @return boolean
     */
    public function isResting()
    {
        return $this->_restTill > time();
    }
    
    
    
    /**
     * Rest for given number of seconds
     *
     * @param integer $seconds
     * @return Mzax_GeoIp_Adapter_Abstract
     */
    protected function rest($seconds)
    {
        $this->_restTill = max($this->_restTill, time() + $seconds);
        return $this;
    }
    
    
    
    /**
     * Rest till next day and don't try bother
     * again today
     * 
     * @param integer $extra
     * @return Mzax_GeoIp_Adapter_Abstract
     */
    protected function restTillNextDay($extra = 1800)
    {
        return $this->rest(self::SECONDS_PER_DAY - (time() % self::SECONDS_PER_DAY) + $extra);
    }
    
    
    
    /**
     * Handle errors
     * 
     * If an error happen don't try again straight away,
     * check again later or wait till next day
     * 
     * @param Exception $e
     */
    protected function _handleError(Exception $e)
    {
        $this->_errorCount++;
        if($this->_errorCount > 10) {
            // wait till tomorrow
            $this->restTillNextDay();
        }
        else {
            // wait number of errors * 5 minutes
            $this->rest(60*5*$this->_errorCount);
        }
    }

    
    
    
    /**
     * Retrieve the number of requests that we have left
     * for this adapter for the current time periode
     *
     * @return interger
     */
    public function getRemainingRequests()
    {
        $this->reachedLimit(); // check limits
        return max($this->requestLimit - $this->_requestCount, 0);
    }
    
    
    
    /**
     * Optional credits
     * 
     * @return NULL|string
     */
    public function getCredits()
    {
        return null;
    }
    
    
    
    
    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        return array('_lastRequestTime', '_lastRequestPeriode',
                '_requestCount', '_requestThresholdCount',
                '_restTill', '_errorCount', );
    }
    
    

}

