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
class Mzax_Emarketing_Model_Medium_Email extends Mzax_Emarketing_Model_Medium_Abstract
{
    
    
    /**
     * Retrieve medium id
     * 
     * @return string
     */
    public function getMediumId()
    {
        return 'email';
    }
    
    



    /**
     *
     * @param Mzax_Emarketing_Model_Medium_Email_Snippets $snippets
     * @return void
     */
    public function prepareSnippets(Mzax_Emarketing_Model_Medium_Email_Snippets $snippets)
    {
        $hlp = Mage::helper('mzax_emarketing');
        
        $snippets->addVar('urls.unsubscribe', $hlp->__('Unsubscribe link'));
        $snippets->addVar('urls.broswer_view', $hlp->__('View in browser link'));
        $snippets->addVar('subject', $hlp->__('Email Subject'));
        $snippets->addVar('address', $hlp->__('Recipient Address'));
        $snippets->addVar('email', $hlp->__('Recipient Email'));
        
        // requires version 1.6 of sales rule (magento 1.7)
        if(version_compare(Mage::getConfig()->getModuleConfig('Mage_SalesRule')->version, '1.6.0') >= 0) {
            $snippets->addSnippets(
                'mage.coupon', 
                '{{coupon rule="${1:1}" length="${2:8}" expire="${3:120}" prefix="${4:ABC-}" }}', 
                $hlp->__('Coupon Code'), 
                $hlp->__('Generates a coupon code for the specifed shopping cart price rule.'));
        }
        
        
    }
    
    
    
    
    
    /**
     * Prepare recipient
     * 
     * @param Mzax_Emarketing_Model_Recipient $recipient
     */
    public function prepareRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $recipient->setAddress( $recipient->getEmail() );
        
        $recipient->addUrl('unsubscribe',  'mzax_emarketing/unsubscribe');
        $recipient->addUrl('browser_view', 'mzax_emarketing/email');
    }
    
    

    /**
     * 
     * 
     * @see Mzax_Emarketing_Model_Medium_Abstract::prepareCampaignTabs()
     * @param Mzax_Emarketing_Block_Campaign_Edit_Tabs $tabs
     */
    public function prepareCampaignTabs(Mzax_Emarketing_Block_Campaign_Edit_Tabs $tabs)
    {
        /* @var $campaign  Mzax_Emarketing_Model_Campaign */
        $campaign = Mage::registry('current_campaign');
        
        
        if($count = $campaign->countInbox()) {
            $tabs->addTab('inbox', array(
                'label'   => $tabs->__('Inbox (%s)', $count),
                'class'   => 'ajax',
                'url'     => $tabs->getUrl('*/admin_inbox/campaignGrid', array('_current' => true)),
            ));
        }
        
        if($count = $campaign->countOutbox()) {
            $tabs->addTab('outbox', array(
                'label'   => $tabs->__('Outbox (%s)', $count),
                'class'   => 'ajax',
                'url'     => $tabs->getUrl('*/admin_outbox/campaignGrid', array('_current' => true)),
            ));
        }
    }
    
    
    
    /**
     * Init settings form
     * 
     * @param Varien_Data_Form $form
     * @param Mzax_Emarketing_Model_Campaign $campaign
     */
    public function initSettingsForm(Varien_Data_Form $form, Mzax_Emarketing_Model_Campaign $campaign)
    {
        $helper = Mage::helper('mzax_emarketing');
        
        
        $fieldset = $form->addFieldset('email_options', array(
            'legend'   => $helper->__('Email Specific Options')
        ), 'base_fieldset');
        
        
        $fieldset->addField('prerender', 'select', array(
            'label'     => $helper->__('Pre-Render'),
            'note'      => $helper->__("If enabled, email will get pre rendered, cached and then only the basic {{var}} expressions will get parsed. If your content is static or only uses var expressions. Enabling this can increase the render performance."),
            'name'      => 'medium_data[prerender]',
            'options'   => array(
                '0' => $helper->__('Disabled'),
                '1' => $helper->__('Enabled'),
            ),
            'value' => 0
        ));
        
        
        $fieldset->addField('forward_emails', 'text', array(
            'name'      => 'medium_data[forward_emails]',
            'label'     => $helper->__('Forward Email'),
            'note'      => $helper->__("All non-auto email replies will get forward to this email address.")
        ));
        
    }
    
    
    
    
    /**
     * Prepare Recipient Grid
     * 
     * @param Mzax_Emarketing_Block_Campaign_Edit_Tab_Recipients_Grid $grid
     * @return void
     */
    public function prepareRecipientGrid(Mzax_Emarketing_Block_Campaign_Edit_Tab_Recipients_Grid $grid)
    {
        $campaign = $grid->getCampaign();
        
        $previewAction = array(
            'target' => "campaign_{$campaign->getId()}_{id}",
            'url' => array(
                'base' => '*/admin_campaign/preview',
                'params' => array(
                    'id' => $grid->getCampaign()->getId()
                ),
            ),
            'field'  => 'entity',
            'popup'   => true,
            'caption' => $grid->__('Preview')
        );
        $sendAction = array(
            'target' => "campaign_{$campaign->getId()}_{id}",
            'url' => array(
                'base' => '*/admin_campaign/sendTestMail',
                'params' => array(
                    'id' => $grid->getCampaign()->getId()
                ),
            ),
            'field'  => 'recipient',
            'popup'   => true,
            'caption' => $grid->__('Send Test Email')
        );
        
        if($campaign->hasVariations()) 
        {
            $sendAction['caption'] = $previewAction['caption'] = $grid->__('[Orignal]');
            
            $previewAction = array(
                'caption' => $grid->__('Preview'),
                'actions' => array($previewAction)
            );
            $sendAction = array(
                'caption' => $grid->__('Send Test Email'),
                'actions' => array($sendAction)
            );
            
            /* @var $variation Mzax_Emarketing_Model_Campaign_Variation */
            foreach($campaign->getVariations() as $variation) 
            {
                $params = array(
                    'id'        => $campaign->getId(),
                    'variation' => $variation->getId()
                );
                
                $previewAction['actions'][] = array(
                    'target' => "campaign_{$campaign->getId()}_{$variation->getId()}_{id}",
                    'url' => array(
                        'base'   => '*/admin_campaign/preview',
                        'params' => $params,
                    ),
                    'field'  => 'entity',
                    'popup'   => true,
                    'caption' => $variation->getName()
                );
                $sendAction['actions'][] = array(
                    'url' => array(
                        'base'   => '*/admin_campaign/sendTestMail',
                        'params' => $params,
                    ),
                    'field'  => 'recipient',
                    'popup'   => true,
                    'caption' => $variation->getName()
                );
            }
        }
        
        $grid->addColumn('action', array(
            'header'    => $grid->__('Action'),
            'index'     =>'id',
            'getter'	=> 'getId',
            'renderer'  => 'mzax_emarketing/grid_column_renderer_action',
            'type'		=> 'action',
            'sortable'  => false,
            'filter'    => false,
            'no_link'   => true,
            'is_system' => true,
            'width'	    => '80px',
            'actions'   => array($previewAction, $sendAction)
        ));
    }
    
    
    
    
    
    
    /**
     * Send email to recipient
     * 
     * Note: The email medium is not responsible for sending out the email directly
     * it will prepare the recipient and the email and push the email to the Outbox
     * model, which then will send out the emails
     * 
     * @param Mzax_Emarketing_Model_Recipient $recipient
     * @throws Exception
     */
    public function sendRecipient(Mzax_Emarketing_Model_Recipient $recipient)
    {
        $recipient->prepare();
        
        if(!$recipient->getAddress()) {
            throw new Exception("No address set");
        }
        
        /* @var $email Mzax_Emarketing_Model_Outbox_Email */
        $email = Mage::getModel('mzax_emarketing/outbox_email');
        $email->setTo($recipient->getAddress());
        $email->setRecipient($recipient);
        $email->render();
        $email->setExpireAt($recipient->getExpireAt());
        
        if(!$recipient->isMock()) {
            $data = $recipient->getContent()->getMediumData();
            $dayFilter  = $data->getDayFilter();
            $timeFilter = $data->getTimeFilter();
            
            // apply day filter
            if(is_array($dayFilter) && count($dayFilter) && count($dayFilter) < 7) {
                $email->setDayFilter(implode(',', $dayFilter));
            }
            
            // apply time filter
            if(is_array($timeFilter) && count($timeFilter) && count($timeFilter) < 24) {
                $email->setTimeFilter(implode(',', $timeFilter));
            }
        }
        
        $email->save();
        
        // if mock, send out email straight away
        if($recipient->isMock() && !$recipient->getSkipSend()) {
            $email->send();
        }
    }
    
    
    
    
    
    /**
     * Unsubscibe email from all stores for any given reason
     * 
     * 
     * @todo Can be done better...
     * @param string $email
     * @param string $reason Reason for log purpose only
     */
    public function unsubscribe($email, $reason)
    {
        /* @var $helper Mzax_Emarketing_Helper_Newsletter */
        $helper = Mage::helper('mzax_emarketing/newsletter');
        
        /* @var $store Mage_Core_Model_Store */
        foreach(Mage::app()->getStores() as $store) {
            $subscriber = $helper->unsubscribe($email, $store->getId(), false);
            if($subscriber->getIsStatusChanged()) {
                Mage::log(sprintf("unsubscribe '%s' from store '%s': %s", $email, $store->getName(), $reason), null, 'mzax_email_unsubscribe.log');
            }
        }
        
        
    }
    


}

