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
class Mzax_GeoIp_Request
{

    /**
     * IPv4 or IPv6
     *
     * @var string
     */
    public $ip;


    /**
     * Name of the city if available
     *
     * @var string|null
     */
    public $city;


    /**
     * Name of the region if available
     *
     * @var string|null
     */
    public $region;


    /**
     * Region ID if available
     *
     * @link http://en.wikipedia.org/wiki/ISO_3166-2
     * @var string|null
     */
    public $regionId;


    /**
     * Name of the country if available
     *
     * @var string|null
     */
    public $country;


    /**
     * Country Code if available
     *
     * @link http://en.wikipedia.org/wiki/ISO_3166-2
     * @var string
     */
    public $countryId;


    /**
     * Metro code
     *
     * @var string
     */
    public $metroCode;


    /**
     * TimeZone if available
     *
     * @var string
     */
    public $timeZone;


    /**
     * Zip code if available
     *
     * @var string
     */
    public $zipCode;



    /**
     * Array of long and altitud
     *
     * @var array
     */
    public $loc;


    /**
     * Original, usally the provider
     *
     * @var string
     */
    public $org;


    /**
     * Offset in minutes reltative to GMT
     *
     * @var integer
     */
    public $timeOffset;


    /**
     *
     * @var Zend_Http_Response
     */
    public $httpResponse;



    /**
     * IP is required for a request
     *
     * @param string $ip
     */
    public function __construct($ip)
    {
        if(@inet_pton($ip) === false) {
            throw new Mzax_GeoIp_Exception("Invalid IP Address");
        }
        $this->ip = $ip;
    }



    /**
     * Refine data
     *
     * Not all data can be retrieved but some data can be guessed or assumed.
     * Try to fill in the blanks with the data that we already have.
     *
     * @return Mzax_GeoIp_Request
     */
    public function refine()
    {
        // try to get region id from string
        if(!$this->regionId && $this->region && $this->countryId) {
            $this->regionId = Mzax_GeoIp_Region::getRegionCode($this->countryId, $this->region);
        }

        if(!$this->regionId && $this->countryId && preg_match('/^[A-Z0-9]{2,3}$/', $this->region)) {
            $this->regionId = $this->region;
            $this->region = Mzax_GeoIp_Region::getRegionCode($this->countryId, $this->regionId);
        }


        if(!$this->timeZone) {
            // try to get a time zone from the location, not perfect but will give us a rough idea
            $this->timeZone = Mzax_GeoIp_Region::getTimeZone($this->countryId, $this->regionId, $this->city);
        }


        if($this->timeZone && $this->timeOffset === null) {
            try {
                $time = new DateTime("now", new DateTimeZone($this->timeZone));
                $this->timeOffset = $time->getOffset()/-60;
            }
            catch(Exception $e) {}
        }

        return $this;
    }




}

