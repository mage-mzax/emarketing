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
 * Class Mzax_GeoIp_Region
 *
 * @todo Refactor
 */
class Mzax_GeoIp_Region
{
    /**
     * Retrieve region code from name
     *
     * @param string $countryId
     * @param string $regionName
     * @param boolean $regionOnly
     *
     * @return string|null
     */
    public static function getRegionCode($countryId, $regionName, $regionOnly = true)
    {
        $regions = self::getRegions($countryId);
        if (empty($regions)) {
            return null;
        }

        // cleanup entry skin
        $regionName = self::removeAccents($regionName);

        // search for strict matches
        foreach ($regions as $code => $name) {
            if (strcasecmp($regionName, $name) === 0) {
                // DE-BW or BW
                return $regionOnly ? substr($code, 3) : $code;
            }
        }

        // find similar matches
        foreach ($regions as $code => $name) {
            if (soundex($regionName) === soundex($name)) {
                // DE-BW or BW
                return $regionOnly ? substr($code, 3) : $code;
            }
        }

        return null;
    }

    /**
     * Retrieve region name from code
     *
     * @param string $countryId
     * @param string $regionId
     * @return string|NULL
     */
    public static function getRegion($countryId, $regionId)
    {
        $regions = self::getRegions($countryId);
        if (isset($regions[$regionId])) {
            return $regions[$regionId];
        }
        return null;
    }

    /**
     * Göthe => Gothe
     *
     * @param string $string
     *
     * @return string
     */
    public static function removeAccents($string)
    {
        static $replacements;

        if (empty($replacements)) {
            $subst = array(
                // single ISO-8859-1 letters
                192=>'A', 193=>'A', 194=>'A', 195=>'A', 196=>'A', 197=>'A', 199=>'C',
                208=>'D', 200=>'E', 201=>'E', 202=>'E', 203=>'E', 204=>'I', 205=>'I',
                206=>'I', 207=>'I', 209=>'N', 210=>'O', 211=>'O', 212=>'O', 213=>'O',
                214=>'O', 216=>'O', 138=>'S', 217=>'U', 218=>'U', 219=>'U', 220=>'U',
                221=>'Y', 142=>'Z', 224=>'a', 225=>'a', 226=>'a', 227=>'a', 228=>'a',
                229=>'a', 231=>'c', 232=>'e', 233=>'e', 234=>'e', 235=>'e', 236=>'i',
                237=>'i', 238=>'i', 239=>'i', 241=>'n', 240=>'o', 242=>'o', 243=>'o',
                244=>'o', 245=>'o', 246=>'o', 248=>'o', 154=>'s', 249=>'u', 250=>'u',
                251=>'u', 252=>'u', 253=>'y', 255=>'y', 158=>'z',
                // HTML entities
                258=>'A', 260=>'A', 262=>'C', 268=>'C', 270=>'D', 272=>'D', 280=>'E',
                282=>'E', 286=>'G', 304=>'I', 313=>'L', 317=>'L', 321=>'L', 323=>'N',
                327=>'N', 336=>'O', 340=>'R', 344=>'R', 346=>'S', 350=>'S', 354=>'T',
                356=>'T', 366=>'U', 368=>'U', 377=>'Z', 379=>'Z', 259=>'a', 261=>'a',
                263=>'c', 269=>'c', 271=>'d', 273=>'d', 281=>'e', 283=>'e', 287=>'g',
                305=>'i', 322=>'l', 314=>'l', 318=>'l', 324=>'n', 328=>'n', 337=>'o',
                341=>'r', 345=>'r', 347=>'s', 351=>'s', 357=>'t', 355=>'t', 367=>'u',
                369=>'u', 378=>'z', 380=>'z',
                // ligatures
                198=>'Ae', 230=>'ae', 140=>'Oe', 156=>'oe', 223=>'ss',
            );

            foreach ($subst as $k => $v) {
                $replacements[$k<256 ? chr($k) : '&#'.$k.';'] = $v;
            }
        }

        return strtr($string, $replacements);
    }

    /**
     * Retrieve region codes for country
     *
     * @param string $countryId
     * @return array
     */
    public static function getRegions($countryId)
    {
        static $data;

        if (!$data) {
            $data = self::_loadData();
        }
        $countryId = strtoupper($countryId);

        if (isset($data[$countryId])) {
            return $data[$countryId];
        }

        return array();
    }

    /**
     * Retrieve best matching time zone from location
     *
     * @param string $countryId
     * @param string $regionId
     * @param string $city
     *
     * @return string|null
     */
    public static function getTimeZone($countryId, $regionId = null, $city = null)
    {
        static $data;

        if (!$data) {
            $data = self::_loadRegionTimeZones();
        }

        $tz = null;
        if (isset($data[$countryId])) {
            $country = $data[$countryId];
            $tz = $country['tz'];

            if ($regionId && isset($country['regions'][$regionId])) {
                $region = $country['regions'][$regionId];
                $tz = $region['tz'];
                if ($city && isset($region['cities'][$city])) {
                    $tz = $region['cities'][$city];
                }
            }
        }

        return $tz;
    }

    /**
     * Load region code table
     *
     * @link http://www.ip2location.com/free/iso3166-2
     * @throws Mzax_GeoIp_Exception
     *
     * @return array
     */
    protected static function _loadData()
    {
        $handle = self::_openDataFile('subdivison_codes.csv');

        $data = array();
        while (($row = fgetcsv($handle, 100, ",")) !== false) {
            if (count($row) != 3) {
                continue;
            }
            list($country, $regionName, $regionCode) = $row;
            if (!isset($data[$country])) {
                $data[$country] = array();
            }
            $data[$country][$regionCode] = $regionName;
        }
        fclose($handle);

        return $data;
    }

    /**
     * Load region code table
     *
     *
     * @link http://www.ip2location.com/free/iso3166-2
     * @throws Mzax_GeoIp_Exception
     * @return array
     */
    public static function _loadRegionTimeZones()
    {
        $handle = self::_openDataFile('region_timezones.csv');

        $data = array();
        while (($csv = fgetcsv($handle, 200, ",")) !== false) {
            if (count($csv) != 4) {
                continue;
            }
            list($countryCode, $regionCode, $city, $timeZone) = $csv;

            if (!isset($data[$countryCode])) {
                $data[$countryCode] = array('regions' => array(), 'cities' => array());
            }
            $row = &$data[$countryCode];

            if (!$regionCode) {
                $row['tz'] = $timeZone;
            } else {
                if (!isset($row['regions'][$regionCode])) {
                    $row['regions'][$regionCode] = array('cities' => array());
                }
                $row = &$row['regions'][$regionCode];

                if (!$city) {
                    $row['tz'] = $timeZone;
                } else {
                    $row['cities'][$city] = $timeZone;
                }
            }
        }

        return $data;
    }

    /**
     * Open data file
     *
     * @param string $file
     * @throws Mzax_GeoIp_Exception
     * @return resource
     */
    protected static function _openDataFile($file)
    {
        $filename = dirname(__FILE__) . '/Data/' . $file;

        if (!file_exists($filename)) {
            throw new Mzax_GeoIp_Exception("Missing file '$file'.");
        }

        if (($handle = fopen($filename, "r")) === false) {
            throw new Mzax_GeoIp_Exception("Failed to read file '$file'.");
        }

        return $handle;
    }


    /**
     * @var
     */
    static public $tzCount;

    /**
     * Load region code table
     *
     *
     * @link http://www.ip2location.com/free/iso3166-2
     * @throws Mzax_GeoIp_Exception
     * @return array
     */
    public static function tz()
    {
        $handle = self::_openDataFile('timezones.csv');
        $data = array();

        $backup = array();
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            if (count($row) != 4) {
                continue;
            }
            list($countryCode, $regionCode, $city, $timeZone) = $row;


            if (!$regionCode || !$countryCode) {
                continue;
            }


            if (!isset($backup[$countryCode.'/'.$regionCode])) {
                $backup[$countryCode.'/'.$regionCode] = array();
            }

            $backup[$countryCode.'/'.$regionCode][] = $row;

            if (!isset($data[$countryCode])) {
                $data[$countryCode] = array('time_zone' => $timeZone, 'regions' => array());
            } elseif ($data[$countryCode]['time_zone'] == $timeZone) {
                continue;
            }

            if (!isset($data[$countryCode]['regions'][$regionCode])) {
                $data[$countryCode]['regions'][$regionCode] = array('time_zone' => $timeZone, 'cities' => array());
            } elseif ($data[$countryCode]['regions'][$regionCode]['time_zone'] == $timeZone) {
                continue;
            }

            $data[$countryCode]['regions'][$regionCode]['cities'][] = $row;
        }

        fclose($handle);
        ksort($data);

        header("Content-Type: text/plain; charset=utf-8");

        foreach ($data as $countryCode => $country) {
            if (!$countryCode) {
                continue;
            }

            $buffer = array();

            $regions = $country['regions'];
            ksort($regions);

            $single = count($regions) === 1;

            $ast = count($regions);

            foreach ($regions as $regionCode => $region) {
                $cities = $region['cities'];

                if (count($cities) > 1) {
                    if (isset($backup[$countryCode.'/'.$regionCode])) {
                        $cities = $backup[$countryCode.'/'.$regionCode];
                    }
                }

                $tz = reset($cities);
                $tz = $tz[3];
                $same = true;
                foreach ($cities as $row) {
                    if ($row[3] != $tz) {
                        $same = false;
                        break;
                    }
                }
                if ($same) {
                    $cities = array(reset($cities));
                }


                $defaultTz = null;
               // $single = $single && count($region['cities']) === 1;

                if (true) {
                    self::$tzCount = array();
                    foreach ($cities as $row) {
                        if (!isset(self::$tzCount[$row[3]])) {
                            self::$tzCount[$row[3]] = 0;
                        }
                        self::$tzCount[$row[3]] = self::$tzCount[$row[3]]+1;
                    }
                   // print_r(self::$tzCount);

                    arsort(self::$tzCount);

                    $defaultTz = array_keys(self::$tzCount);
                    $defaultTz = $defaultTz[0];
                    //print_r(self::$tzCount);

                    if (self::$tzCount[$defaultTz] <= 1) {
                        $defaultTz = null;
                    } else {
                        $buffer[] = "$countryCode,$regionCode,,\"$defaultTz\"\n";
                    }

                    usort($cities, array('Mzax_GeoIp_Region','sort'));
                }

                $finalCities = array();
                foreach ($cities as $row) {
                    list($countryCode, $regionCode, $city, $timeZone) = $row;

                    if (!$timeZone) {
                        continue;
                    }

                    if ($timeZone === $defaultTz) {
                        continue;
                    }

                    $finalCities[] = $row;
                }

                $cities = $finalCities;


                $singleA = $single && count($cities) === 1;

                foreach ($cities as $row) {
                    list($countryCode, $regionCode, $city, $timeZone) = $row;

                    if ($city) {
                        $city = trim($city);
                        $city = "\"$city\"";
                    }

                    if ($singleA) {
                        $city = null;
                        $regionCode = null;
                    } elseif (!$defaultTz && count($cities) == 1) {
                        $city = null;
                    }

                    $timeZone = "\"$timeZone\"";
                    $buffer[] = "$countryCode,$regionCode,$city,$timeZone\n";
                }
            }

            if (count($buffer) >= 1) {
                $first = preg_replace('/([A-Z]{2}),.*?,.*?,(.*?)/', '$1,,,$2', $buffer[0]);
                if ($buffer[0] != $first) {
                    echo $first;
                }
            }

            echo implode("", $buffer);
        }
        //var_dump($data);
        //exit;


        return $data;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    public static function sort($a, $b)
    {
        if (self::$tzCount[$a[3]] > self::$tzCount[$b[3]]) {
            return -1;
        } elseif (self::$tzCount[$a[3]] < self::$tzCount[$b[3]]) {
            return 1;
        }

        return strcasecmp($a[2], $a[2]);
    }
}
