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
 * @link https://freegeoip.net/
 * @link http://www.geoplugin.com/webservices/json
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_GeoIp_Adapter_FreeGeoIp extends Mzax_GeoIp_Adapter_Abstract
{
    const API_URI = 'https://freegeoip.net/json/{IP}';

    public $requestLimit = 5;
    public $timeThreshold = 30;
    public $requestThreshold = 30;
    public $resetTime = 500;

    public $resetPeriode = 'Y-m-d-h-i'; //self::HOURLY;



    /**
     * Retrieve the name of this adapter
     *
     * @return string
     */
    public function getName()
    {
        return 'freegeoip.net';
    }


    public function getCredits()
    {
        return '<a href="http://www.freegeoip.net"><strong>freegeoip.net</strong></a> - this product includes GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.';
    }


    protected function _fetch(Mzax_GeoIp_Request $request)
    {
        $uri = str_replace('{IP}', $request->ip, self::API_URI);
        $client = new Zend_Http_Client($uri);

        try {
            $response = $client->request();
            $request->httpResponse = $response;

            if($response->getStatus() == 403) {
                $this->ease(60*60);
            }

            $data = Zend_Json::decode($response->getBody());

            if(isset($data['city'])) {
                $request->city = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data['city']);
            }
            if(isset($data['region_name'])) {
                $request->region = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data['region_name']);
            }
            if(isset($data['country_code'])) {
                $request->countryId = $data['country_code'];
            }
            if(isset($data['region_code'])) {
                $request->regionId = $data['region_code'];
            }
            if(isset($data['zip_code'])) {
                $request->zipCode = $data['zip_code'];
            }
            if(isset($data['metro_code'])) {
                $request->metroCode = $data['metro_code'];
            }
            if(isset($data['time_zone'])) {
                $request->timeZone = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data['time_zone']);;
            }
            if(isset($data['country_name'])) {
                $request->country = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $data['country_name']);;
            }
            if(isset($data['longitude']) && isset($data['latitude'])) {
                $request->loc = array($data['longitude'], $data['latitude']);
            }

        }
        catch(Zend_Json_Exception $e) {
            $this->easeTillNextDay();
            throw $e;
        }
    }





}

