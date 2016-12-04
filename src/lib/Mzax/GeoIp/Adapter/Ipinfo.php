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
 * @link http://ipinfo.io/
 * @link http://ipinfo.io/developers
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_GeoIp_Adapter_Ipinfo extends Mzax_GeoIp_Adapter_Abstract
{
    const API_URI = 'http://ipinfo.io/{IP}/json';

    public $requestLimit = 800;

    public $resetPeriode = self::DAILY;


    /**
     * Retrieve the name of this adapter
     *
     * @return string
     */
    public function getName()
    {
        return 'ipinfo.io';
    }


    public function getCredits()
    {
        return '<a href="http://ipinfo.io"><strong>ipinfo.io</strong></a> - this product includes GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.';
    }


    protected function _fetch(Mzax_GeoIp_Request $request)
    {
        $uri = str_replace('{IP}', $request->ip, self::API_URI);
        $client = new Zend_Http_Client($uri);
        $client->setHeaders('tool', 'PHP Mzax_GeoIp Lib');
        $client->setHeaders('tool_version', 'v1.0.1');

        try {
            $response = $client->request();
            $request->httpResponse = $response;

            $data = Zend_Json::decode($response->getBody());
            if(isset($data['city'])) {
                $request->city = $data['city'];
            }
            if(isset($data['region'])) {
                $request->region = $data['region'];
            }
            if(isset($data['country'])) {
                $request->countryId = $data['country'];
            }
            if(isset($data['loc'])) {
                $request->loc = explode(',', $data['loc']);
            }
            if(isset($data['org'])) {
                $request->org = $data['org'];
            }
        }
        catch(Zend_Json_Exception $e) {
            $this->easeTillNextDay();
            throw $e;
        }
    }





}

