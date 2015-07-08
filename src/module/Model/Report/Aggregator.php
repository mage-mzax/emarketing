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
class Mzax_Emarketing_Model_Report_Aggregator extends Mzax_Emarketing_Model_Report_Aggregator_Abstract
{
    
    protected $_aggregators = array(
        'events',
        'recipient',
        'goals',
        'tracker',
        'dimension',
        'rates',
        'campaign',
    );
    
    
    
    /**
     * Prepare options an run all aggregators
     * 
     * @param array $options
     */
    public function run(array $options = array())
    {
        if($lock = Mage::helper('mzax_emarketing')->lock('report_aggregator')) 
        {
            $options = new Varien_Object($options);
            $options->setLock($lock);
            
            if($aggregator = $options->getAggregator()) {
                $options->setAggregator((array) $aggregator);
            }
            if($dimension = $options->getDimension()) {
                $options->setDimension((array) $dimension);
            }
            if($trackerId = $options->getTrackerId()) {
                $options->setTrackerId((array) $trackerId);
            }
            
            if($options->getData('full')) {
                $options->unsAggregator();
                $options->unsDimension();
                $options->unsTrackerId();
                $options->unsAggregator();
                $options->unsIncremental();
            }
            
            $this->aggregate($options);
            $lock->unlock();
        }
    }
    
    
    
    

    protected function _aggregate()
    {
        $filter = $this->getOption('aggregator');
        
        foreach($this->_aggregators as $type) {
            if(empty($filter) || in_array($type, $filter)) {
                $aggregator = $this->getAggregator($type);
                $aggregator->aggregate($this->_options);
                $this->_options->getLock()->touch();
            }
        }
    }
    
    
    
    
    /**
     * 
     * @param string $type
     * @return Mzax_Emarketing_Model_Report_Aggregator_Abstract
     */
    public function getAggregator($type)
    {
        return Mage::getSingleton('mzax_emarketing/report_aggregator_' . $type);
    }
    
    
    
}