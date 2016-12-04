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
 * Helper class for campaign related tasks
 */
class Mzax_Emarketing_Helper_Campaign extends Mage_Core_Helper_Abstract
{
    /**
     * Check all campaigns for new recipients
     *
     * This will look for new recipients that match the current filters
     * and should recieve the campaign
     *
     * @param array $options
     *
     * @return int The number of new recipients
     * @throws Exception
     */
    public function fetchNewRecipients(array $options)
    {
        /** @var Mzax_Emarketing_Helper_Data $helper */
        $helper = Mage::helper('mzax_emarketing');

        $lock = $helper->lock('fetch_new_recipients');
        if (!$lock) {
            return false;
        }

        /* @var $campaigns Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $campaigns = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $campaigns->addCheckFilter();
        $campaigns->addRunningFilter();

        // do the once that get checked more often at the end
        $campaigns->setOrder('check_frequency', 'DESC');
        // then go for a random sort to make sure they all get a chance
        $campaigns->setOrder('RAND()', 'ASC');

        $options = new Varien_Object($options);
        $options->getDataSetDefault('timeout', 150);
        $options->getDataSetDefault('break_on_error', Mage::getIsDeveloperMode());

        $verbose = (bool)$options->getData('verbose');

        if ($verbose) {
            echo "\n\n{$campaigns->getSelect()}\n\n";
        }

        $start = time();
        $timeout = (int) $options->getTimeout();

        $count = 0;
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        foreach ($campaigns as $campaign) {
            try {
                if ($verbose) {
                    echo sprintf("Find Recipients for '%s' (#%s)...", $campaign->getName(), $campaign->getId());
                }

                $count += $found = $campaign->findRecipients();

                if ($verbose) {
                    echo sprintf(" \tfound %s\n", $found);
                }

                $lock->touch();
            } catch (Exception $e) {
                if ($options->getBreakOnError()) {
                    $lock->unlock();
                    throw $e;
                }
                Mage::logException($e);

                if ($verbose) {
                    echo "\n{$e->getMessage()}\n{$e->getTraceAsString()}\n\n";
                }
            }
            // make sure we don't exceed the timeout
            if ($timeout && time()-$start > $timeout) {
                break;
            }
        }
        $lock->unlock();

        return $count;
    }

    /**
     * Send recipients
     *
     * @param array $options
     *
     * @return int
     * @throws Exception
     */
    public function sendRecipients(array $options)
    {
        /** @var Mzax_Emarketing_Helper_Data $helper */
        $helper = Mage::helper('mzax_emarketing');

        $lock = $helper->lock('send_recipients');
        if (!$lock) {
            return 0;
        }

        /* @var $campaigns Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $campaigns = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        $campaigns->addRunningFilter();
        //$campaigns->setOrder('last_check', 'DESC');
        $campaigns->setOrder('RAND()', 'ASC');

        $options = new Varien_Object($options);
        $options->getDataSetDefault('timeout', 100);
        $options->getDataSetDefault('maximum', 500);
        $options->getDataSetDefault('break_on_error', Mage::getIsDeveloperMode());

        $verbose = (bool)$options->getData('verbose');

        if ($verbose) {
            echo "\n\n{$campaigns->getSelect()}\n\n";
        }

        $start = time();

        $timeout = (int) $options->getTimeout();
        $maximum = (int) $options->getMaximum();

        $count = 0;
        /* @var $campaign Mzax_Emarketing_Model_Campaign */
        foreach ($campaigns as $campaign) {
            try {
                if ($verbose) {
                    echo sprintf("Send Recipients for '%s' (#%s)...", $campaign->getName(), $campaign->getId());
                }

                $count += $sent = $campaign->sendRecipients(array(
                    'timeout' => $timeout - (time()-$start),
                    'maximum' => $maximum - $count,
                    'break_on_error' => $options->getBreakOnError()
                ));

                if ($verbose) {
                    echo sprintf(" \tsent %s\n", $sent);
                }
            } catch (Exception $e) {
                if ($options->getBreakOnError()) {
                    $lock->unlock();
                    throw $e;
                }
                Mage::logException($e);
                if ($verbose) {
                    echo "\n{$e->getMessage()}\n{$e->getTraceAsString()}\n\n";
                }
            }
            // make sure we don't exceed the timeout
            if ($timeout && time()-$start > $timeout) {
                break;
            }
            // don't send more then max at once
            if ($maximum && $maximum <= $count) {
                break;
            }
            $lock->touch();
        }
        $lock->unlock();

        return $count;
    }
}
