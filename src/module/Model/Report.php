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
 * Simple facades for report related tasks
 * 
 * Mage::getSingleton('mzax_emarketing/report');
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Report
{
    
    
    

    
    /**
     * Run all report aggregators
     * 
     * @param array $options
     * @return void
     */
    public function aggregate(array $options = array())
    {
        /* @var $aggregator Mzax_Emarketing_Model_Report_Aggregator */
        $aggregator = Mage::getModel('mzax_emarketing/report_aggregator');
        $aggregator->run($options);
    }
    
    
    
    
    /**
     * Parse useragents
     * 
     * @return void
     */
    public function parseUseragents($all = false)
    {
        /* Mzax_Emarketing_Model_Resource_Useragent */
        Mage::getResourceSingleton('mzax_emarketing/useragent')->parse($all);
    }
    
    
    
    
    
    

    /**
     * Look for recipient events that have an IP but now location info
     * 
     * @throws Exception
     * @return void
     */
    public function fetchGeoIp()
    {
        if(!Mage::getStoreConfigFlag('mzax_emarketing/tracking/use_geo_ip')) {
            return;
        }
        
        $lock = Mage::helper('mzax_emarketing')->lock('fetch_geop_ip');
        if(!$lock) {
            return;
        }
        
        // configure geo ip
        $geoIp = new Mzax_GeoIp(Mage::getBaseDir('tmp') . DS . 'geoip.sess');
        $geoIp->clearAdapters();
        
        $adapters = Mage::getSingleton('mzax_emarketing/system_config_source_geoIp')->getSelectedAdapters();
        
        /* @var $adapter Mzax_GeoIp_Adapter_Abstract */
        foreach($adapters as $adapter) {
            $geoIp->addAdapter($adapter);
        }
        
        if(!$geoIp->hasAdapters()) {
            throw new Exception("No GeoIP adapters defined");
        }
        
        
        

        /* @var $resource Mzax_Emarketing_Model_Resource_Recipient_Event */
        $resource = Mage::getResourceSingleton('mzax_emarketing/recipient_event');
        
        $rows = $resource->fetchPendingGeoIpRows(3 /* expire in hours*/);
        
        // noting todo, stop
        if(empty($rows)) {
            return;
        }
        
        
        
        $startTime = time();
        $maxRunTime = 60*2;
        
        foreach($rows as $row) 
        {
            /* By default we use free public APIs to retrieve the location
             * of an IP, those however limit each server to only a certain number
             * of requests per day/hour.
             * 
             * If this limit is reached it might be required to go for a payed version
             * or implmented a custom version
             */
            if(!$geoIp->getRemainingRequests()) {
                Mage::log("MzaxEmarketing: No GeoIP requests left, you might want to consider different solution.");
                break;
            }
            
            
            /* Prevent flooding the APIs by doing to many calls in a short time or there might be
             * en error happening every now and then.
             * If this showes up in the log too ofter then there is a chance that something
             * does not work OK. 
             */
            if($geoIp->getRestTime()) {
                Mage::log("MzaxEmarketing: GeoIP is resting, try again later.");
                break;
            }
        
            /* Don't run forever...
             * The next cron tab can always finish this later
             */
            if((time()-$startTime) >= $maxRunTime) {
                Mage::log("MzaxEmarketing: Maximum run time exceeded");
                break;
            }
            
            try {
                //$randIp = implode('.', array(rand(1,254),rand(1,254),rand(1,254),rand(1,254)));
                $result = $geoIp->fetch(inet_ntop($row['ip']));
                if(!$result) {
                    continue;
                }
                
                $update = array();
                if(!empty($row['country_id'])) {
                    $update['country_id'] = (string) $result->countryId;
                }
                if(!empty($row['region_id'])) {
                    $update['region_id'] = (string) $result->regionId;
                }
                if($row['time_offset'] === null) {
                    // assume magento store local time if we could not find out.
                    // INFO: we also use javascript to find the offset
                    // @see injectTimeOffsetJs()
                    if($result->timeOffset === null) {
                        //$update['time_offset'] = Mage::app()->getLocale()
                        //    ->storeDate($row['store_id'])->getGmtOffset()/60;
                    }
                    else {
                        $update['time_offset'] = $result->timeOffset;
                    }
                }
                $resource->updateEvent($row['event_id'], $update);
                $lock->touch();
            }
            catch(Exception $e) {
                Mage::logException($e);
                if(Mage::getIsDeveloperMode()) {
                    $lock->unlock();
                    throw $e;
                }
            }
            
        } // foreach($rows as $row) 
        $lock->unlock();
    }
    
    
    
    
    
    
    
}
