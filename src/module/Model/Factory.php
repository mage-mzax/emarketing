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
 * Class Mzax_Emarketing_Model_Factory
 *
 * Object and model factory
 */
class Mzax_Emarketing_Model_Factory
{
    /**
     * Create new recipient model
     *
     * @return Mzax_Emarketing_Model_Recipient
     */
    public function createRecipient()
    {
        return Mage::getModel('mzax_emarketing/recipient');
    }

    /**
     * @return Mzax_Emarketing_Model_Resource_Recipient_Collection
     */
    public function createRecipientCollection()
    {
        return Mage::getResourceModel('mzax_emarketing/recipient_collection');
    }

    /**
     * @return Mzax_Emarketing_Model_Link_Reference
     */
    public function createLinkReference()
    {
        return Mage::getModel('mzax_emarketing/link_reference');
    }

    /**
     * Create new campaign model
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function createCampaign()
    {
        return Mage::getModel('mzax_emarketing/campaign');
    }

    /**
     * @return Mzax_Emarketing_Model_Medium_Email_Snippets
     */
    public function createSnippets()
    {
        return Mage::getModel('mzax_emarketing/medium_email_snippets');
    }

    /**
     * @return Mzax_Emarketing_Model_Campaign_Variation
     */
    public function createVariation()
    {
        return Mage::getModel('mzax_emarketing/campaign_variation');
    }

    /**
     * @return Mzax_Emarketing_Model_Resource_Campaign_Variation_Collection
     */
    public function createVariationCollection()
    {
        return Mage::getResourceModel('mzax_emarketing/campaign_variation_collection');
    }

    /**
     * @return Mzax_Emarketing_Model_Resource_Conversion_Tracker_Collection
     */
    public function createTrackerCollection()
    {
        return Mage::getResourceModel('mzax_emarketing/conversion_tracker_collection');
    }

    /**
     * @return Mzax_Emarketing_Model_Newsletter_List
     */
    public function createNewsletterList()
    {
        return Mage::getModel('mzax_emarketing/newsletter_list');
    }

    /**
     * @return Mzax_Emarketing_Model_Resource_Newsletter_List_Collection
     */
    public function createNewsletterListCollection()
    {
        return Mage::getResourceModel('mzax_emarketing/newsletter_list_collection');
    }

    /**
     * Create url model
     *
     * @return Mage_Core_Model_Url
     */
    public function createUrl()
    {
        return Mage::getModel('core/url');
    }

    /**
     * @return Mage_Newsletter_Model_Subscriber
     */
    public function createNewsletterSubscriber()
    {
        return Mage::getModel('newsletter/subscriber');
    }
}
