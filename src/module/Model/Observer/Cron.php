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
 * All cron tab commands are here, you can disable them
 * and run your own scripts if ever needed.
 * 
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Observer_Cron
    extends Mzax_Emarketing_Model_Observer_Abstract
{

    /**
     * 
     * @var boolean
     */
    protected $_testMode = false;
    
    
    
    /**
     * Simple test
     * 
     * @access private
     * @return void
     */
    public function test()
    {
        $this->_testMode = true;

        echo "Purge old emails... ";
        $this->purge();
        echo "done\n\n";

        echo "Fetch recipients... ";
        $this->fetchRecipients();
        echo "done\n\n";
        
        echo "Send recipients... ";
        $this->sendRecipients();
        echo "done\n\n";
        
        echo "Outbox send... ";
        $this->outboxSend();
        echo "done\n\n";
        
        echo "Inbox load... ";
        $this->inboxLoad();
        echo "done\n\n";
        
        echo "Run report tasks... ";
        $this->runReportTasks();
        echo "done\n\n";
        
        echo "Aggregate reports... ";
        $this->aggregateWeekly();
        echo "done\n\n";
        
        
        $this->_testMode = false;
    }
    
    
    
    /**
     * Can run cron tabs
     * 
     * @return boolean
     */
    public function enabled() 
    {
        if ($this->_testMode) {
            return true;
        }
        return Mage::getStoreConfigFlag('mzax_emarketing/general/enable');
    }
    
    
    
    /**
     * Check all campaigns for new recipients
     * 
     * @return void
     */
    public function fetchRecipients()
    {
        if (!$this->enabled()) {
            return;
        }
        
        $options = array(
            'timeout'        => 60*5,
            'break_on_error' => false,
            'verbose'        => $this->_testMode
        );
        Mage::helper('mzax_emarketing/campaign')->fetchNewRecipients($options);
    }
    
    
    
    /**
     * Send recipients
     * 
     * @return void
     */
    public function sendRecipients()
    {
        if (!$this->enabled()) {
            return;
        }
        
        $options = array(
            'timeout'        => 60*5,
            'maximum'        => 1000,
            'break_on_error' => false,
            'verbose'        => $this->_testMode
        );
        Mage::helper('mzax_emarketing/campaign')->sendRecipients($options);
    }
    
    
    
    
    
    /**
     * Send emails from outbox and queue in template
     * 
     * @return void
     */
    public function outboxSend()
    {
        if (!$this->enabled()) {
            return;
        }
        
        $options = array(
            'timeout'        => 60*5,
            'maximum'        => 200,
            'break_on_error' => false,
            'verbose'        => $this->_testMode
        );
        
        /* @var $outbox Mzax_Emarketing_Model_Outbox */
        $outbox = Mage::getModel('mzax_emarketing/outbox');
        $outbox->sendEmails($options);
    }
    
    
    
    /**
     * Connect to email inbox and check for new messages
     * 
     * @return void
     */
    public function inboxLoad()
    {
        if (!$this->enabled()) {
            return;
        }
        
        /* @var $inbox Mzax_Emarketing_Model_Inbox */
        $inbox = Mage::getModel('mzax_emarketing/inbox');
        $inbox->downloadEmails();
        $inbox->parseEmails();
    }
    
    
    
    
    
    /**
     * Run tasks related to report
     * 
     * @return void
     */
    public function runReportTasks()
    {
        if (!$this->enabled()) {
            return;
        }
        
        /* @var $report Mzax_Emarketing_Model_Report */
        $report = Mage::getModel('mzax_emarketing/report');
        $report->fetchGeoIp($this->_testMode);
        $report->parseUseragents();
    }
    
    
    
    /**
     * Aggregate reports for the past 2 days
     * 
     * This can run fairly quick only updating records from the past 2 days
     * making it a fast way for aggregating new data.
     * 
     * However there might be some rare duplicates
     *
     * @return void
     */
    public function aggregateHourly()
    {
        /* @var $aggregator Mzax_Emarketing_Model_Report_Aggregator */
        $aggregator = Mage::getModel('mzax_emarketing/report_aggregator');
        $aggregator->run(array(
            'incremental' => 5,
            'verbose'     => $this->_testMode
        ));
    }
    
    
    
    /**
     * Aggregate reports for the past 5 days
     * 
     * This runs quite quick as well but will definitely have less duplicates
     * 
     * @return void
     */
    public function aggregateDaily()
    {
        /* @var $aggregator Mzax_Emarketing_Model_Report_Aggregator */
        $aggregator = Mage::getModel('mzax_emarketing/report_aggregator');
        $aggregator->run(array(
            'incremental' => 20,
            'verbose'     => $this->_testMode
        ));
    }
    
    
    

    /**
     * Aggregate reports for the past 60 days
     *
     * Will be slower but quite accurate results. Running it once a week is perfect. 
     * 
     * @todo allow full manual reindex
     * @return void
     */
    public function aggregateWeekly()
    {
        /* @var $aggregator Mzax_Emarketing_Model_Report_Aggregator */
        $aggregator = Mage::getModel('mzax_emarketing/report_aggregator');
        $aggregator->run(array(
            'incremental' => 60,
            'verbose'     => $this->_testMode
        ));
    }




    /**
     * Purge/cleanse old emails
     *
     * No need to keep full content - it can grow quick.
     *
     * @return void
     */
    public function purge()
    {
        $ttl = (int) Mage::getStoreConfig('mzax_emarketing/ema/email_ttl');
        $ttl = max($ttl, 14);

        /* @var $inbox Mzax_Emarketing_Model_Resource_Inbox_Email */
        $inbox = Mage::getResourceSingleton('mzax_emarketing/inbox_email');
        $inbox->purge($ttl);

        /* @var $outbox Mzax_Emarketing_Model_Resource_Outbox_Email */
        $outbox = Mage::getResourceSingleton('mzax_emarketing/outbox_email');
        $outbox->purge($ttl);
    }
    
}
