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

class Mzax_Emarketing_TestController extends Mage_Core_Controller_Front_Action
{

    
    public function preDispatch()
    {
        parent::preDispatch();
        
        if(!Mage::getIsDeveloperMode()) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_redirect('/');
            return;
        }
        
        header("Content-Type: text/plain; charset=utf8");
        ini_set('html_errors', false);
        ob_implicit_flush(true);
    }
    
    
    public function indexAction()
    {
        header("Content-Type: text/html; charset=utf8");
        
        echo '<ul>';
        echo sprintf('<li><a href="%s">%s</a></li>', Mage::getUrl('*/*/cron'), "Cron Test");
        echo sprintf('<li><a href="%s">%s</a></li>', Mage::getUrl('*/*/geoip'), "GeoIp Test");
        echo sprintf('<li><a href="%s">%s</a></li>', Mage::getUrl('*/*/aggregate'), "Aggregate Test");
        echo '</ul>';
        
        exit;
    }
    
    
    
    /**
     * Test cron jobs
     * 
     * @return void
     */
    public function cronAction()
    {
        Mage::getSingleton('mzax_emarketing/observer_cron')->test();
    }
    
    
    
    /**
     * Test Useragent parsing
     * 
     * @return void
     */
    public function uaAction()
    {
        Mage::getResourceSingleton('mzax_emarketing/useragent')->parse(true);
    }
    
    
    
    
    
    /**
     * Test GeoIp
     * 
     * @return void
     */
    public function geoipAction()
    {
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
        
        
        $startTime = time();
        $maxRunTime = 30;
        
        while(true)
        {
            if(!$geoIp->getRemainingRequests()) {
                $this->log("MzaxEmarketing: No GeoIP requests left, you might want to consider different solution.");
                break;
            }
            
            if($geoIp->getRestTime()) {
                $this->log("MzaxEmarketing: GeoIP is resting, try again later.");
                break;
            }
            
            if((time()-$startTime) >= $maxRunTime) {
                $this->log("MzaxEmarketing: Maximum run time exceeded");
                break;
            }
        
            try {
                $ip = $this->generateIP();
                
                $this->log("Fetch IP: {$ip}...");
                
                $result = $geoIp->fetch($ip);
                if(!$result) {
                    continue;
                }
                $this->log(sprintf("\tCountry %s, Region %s, City %s", $result->countryId, $result->regionId, $result->city));
                
                if(!$result->regionId) {
                    $this->log($result);
                    $this->log("\n\n\n");
                }
                
                //$this->log($result);
            }
            catch(Exception $e) {
                throw $e;
            }
        }
        $this->log("DONE");
        exit;
    }
    
    
    
    
    /**
     * Test data aggregation
     * 
     * @return void
     */
    public function aggregateAction()
    {
        /* @var $aggregator Mzax_Emarketing_Model_Report_Aggregator */
        $aggregator = Mage::getModel('mzax_emarketing/report_aggregator');
        $aggregator->run(array(
           // 'aggregator'  => array('rates'),
           // 'dimension'   => array('useragent'),
           // 'incremental' => 5,
            //'tracker_id'  => 1,
            //'skip_goal_aggregation' => true
            'full' => true,
            'verbose' => true
        ));
    }
    
    
    
    
    
    public function sendAction()
    {
        header("Content-Type: text/plain; charset=utf8");
        ini_set('html_errors', false);
        
        /* @var $outbox Mzax_Emarketing_Model_Outbox */
        $outbox = Mage::getModel('mzax_emarketing/outbox');
        $outbox->sendEmails();
        
    }
    
    
    
    

    public function queryAction()
    {
        header("Content-Type: text/plain");
    
        /* @var $tracker Mzax_Emarketing_Model_Conversion_Tracker */
        $tracker = Mage::getModel('mzax_emarketing/conversion_tracker');
        $tracker->setId('4');
        
        
        /* @var $query Mzax_Emarketing_Model_Report_Query */
        //*
        $query = Mage::getModel('mzax_emarketing/report_query');
        $query->setParam('campaign', 1);
        $query->setParam('dimension', 'date');
        $query->setParam('time_unit', $query::UNIT_MONTHS);
        $query->setParam('metrics', array('#1_revenue_sum'));
        $query->setParam('variations', true);
        $query->setParam('order', null);
        $query->load();
        
        
        
        
        
        //*/
        
        
        /* @var $query Mzax_Emarketing_Model_Report_Query */
        /*
        $query = Mage::getModel('mzax_emarketing/report_query');
        $query->setParam('campaign', 2);
        $query->setParam('metrics', array('sendings', 'views', 'clicks', '#4', '#4_rate', '#8', '#9'));
        $query->setParam('variation', true);
        $query->load();
        //*/
        


        /* @var $query Mzax_Emarketing_Model_Report_Query */
        /*
        $query = Mage::getModel('mzax_emarketing/report_query');
        $query->setParam('campaign', 3);
        $query->setParam('dimension', 'Mail Client');
        $query->setParam('dimension', 'Device');
        //$query->setParam('dimension', 'Mail Client und so');
        $query->setParam('metrics', array('sendings', 'views', 'clicks', '#4', '#8', '#9', '#5'));
        $query->setParam('variation', true);
        $query->load();
        //*/
        
        //die('dd');
        
        echo "\n\n\n\n";
        
        echo $query->getSelect();
        
        echo "\n\n\n\n";
        
        print_r($query->getData());
    
        echo "\n\n\n\n";
        
        echo $query->getDataTable()->asJson();
        //var_dump($result);
    
        die("ok");
    }
    
    
    
    
    
    
    
    public function seedAction()
    {
        header("Content-Type: text/plain");
    
    
        /* @var $seeder Mzax_Emarketing_Model_Report_Seeder */
        $seeder = Mage::getModel('mzax_emarketing/report_seeder');
        $seeder->run();
    
    
        die("ok");
    }

    
    
    
    
    
    
    
    public function pullAction()
    {
        header("Content-Type: text/plain");
        
        
        /* @var $inbox Mzax_Emarketing_Model_Inbox */
        $inbox = Mage::getSingleton('mzax_emarketing/inbox');
        $inbox->downloadEmails();
        
        
        die("ok");
    }
    

    public function parseAction()
    {
        header("Content-Type: text/plain");
        
        /* @var $inbox Mzax_Emarketing_Model_Inbox */
        $inbox = Mage::getSingleton('mzax_emarketing/inbox');
        $inbox->parseEmails();
        
        
        die("ok");
    }
    
    
    
    public function fetchAction()
    {
        die(''.count(Mage::helper('mzax_emarketing')->fetchNewRecipients()));
    }
    
    
    
    protected function log($message)
    {
        if(!is_string($message)) {
            $message = var_export($message, true);
        }
        echo "\n" . $message;
    }
    
    
    
    /**
     * Generate a random public ip for testing
     * 
     * @return string 
     */
    protected function generateIP() 
    {
        $ip = array();
        do {
            $ip[0] = rand(1,254);
        }
        while(in_array($ip[0], explode(',', '10,100,127,169,172,192,198,203,224,240')));
        
        $ip[] = rand(1,254);
        $ip[] = rand(1,254);
        $ip[] = rand(1,254);
        
        return implode('.', $ip);
    }
}