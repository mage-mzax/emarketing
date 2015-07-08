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


class Mzax_Emarketing_Block_Outbox_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('outbox_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
    }
    
    
    protected function _beforeToHtml()
    {
        $this->setId('outbox_grid');
        parent::_beforeToHtml();
    }
    
    
    
    public function getCollection()
    {
        if(!$this->_collection) {
            /* Mzax_Emarketing_Model_Resource_Outbox_Email_Collection */
            $this->_collection = Mage::getResourceModel('mzax_emarketing/outbox_email_collection');
            $this->_collection->assignRecipients();
            $this->_collection->assignCampaigns();
        }
        return $this->_collection;
    }
    
    
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header'    => $this->__('Created at'),
            'index'     => 'created_at',
            'gmtoffset' => true,
            'type'      =>'datetime'
        ));
        
        $this->addColumn('recipient', array(
            'header'         => $this->__('Recipient'),
            'index'          => 'to',
            'frame_callback' => array($this, 'addObjectLink'),
        ));
        
        
        $this->addColumn('sent_at', array(
                'header'    => $this->__('Sent at'),
                'index'     => 'sent_at',
                'gmtoffset' => true,
                'type'      =>'datetime'
        ));
        $this->addColumn('expire_at', array(
                'header'    => $this->__('Expire at'),
                'index'     => 'expire_at',
                'gmtoffset' => true,
                'type'      =>'datetime'
        ));
        
        
        /*
        $this->addColumn('subject', array(
            'header'    => $this->__('Subject'),
            'index'     => 'subject',
            'type'      => 'text',
            'getter'    => function($row) {
                return $row->getSubject();
            },
            'truncate'  => 50
        ));
        $this->addColumn('message', array(
            'header'    => $this->__('Message'),
            'index'     => 'message',
            'type'      => 'text',
            'getter'    => function($row) {
                return $row->getMessage();
            },
            'truncate'  => 100
        ));
        */
        
        /* @var $campaigns Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $campaigns = Mage::getResourceModel('mzax_emarketing/campaign_collection');
        
        $this->addColumn('campaign', array(
            'header'    => $this->__('Campaign'),
            'index'     => 'campaign_id',
            'type'      => 'options',
            'options'   => $campaigns->toOptionHash(),
            'frame_callback' => array($this, 'addCampaignLink'),
            'width'     => '100px',
        ));
        
        
        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                Mzax_Emarketing_Model_Outbox_Email::STATUS_NOT_SEND   => $this->__('Not yet sent'),
                Mzax_Emarketing_Model_Outbox_Email::STATUS_EXPIRED    => $this->__('Expired'),
                Mzax_Emarketing_Model_Outbox_Email::STATUS_FAILED     => $this->__('Failed'),
                Mzax_Emarketing_Model_Outbox_Email::STATUS_SENT       => $this->__('Sent'),
                Mzax_Emarketing_Model_Outbox_Email::STATUS_DISCARDED  => $this->__('Discarded'),
            ),
            'frame_callback' => function($value, $row, $column, $export){
                $status = $row->getData($column->getIndex());
                return "<span class=\"outbox-status-{$status}\">!!$value!!</span>";
            },
            'width'     => '100px',
        ));
        
        return parent::_prepareColumns();
    }

    
    
    
    /**
     * Frame Callback
     * 
     * Add link to admin for subject if available
     * 
     * 
     * @param string $value
     * @param Varien_Object $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $export
     * @return string
     */
    public function addObjectLink($value, $row, $column, $export)
    {
        $recipient = $row->getRecipient();
        
        if($recipient instanceof Mzax_Emarketing_Model_Recipient && !$export) 
        {
            $campaign = $recipient->getCampaign();
            if($campaign->getRecipientProvider()) {
                $object  = $campaign->getRecipientProvider()->getObject();
                
                if($object) {
                    $url = $object->getAdminUrl($recipient->getObjectId());
                    return sprintf('<a href="%s">%s</a>', $url, $value);
                }
            }
        }
        return $value;
    }
    
    

    /**
     * Frame Callback
     *
     * Add link to campaign if available
     *
     * @param string $value
     * @param Varien_Object $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $export
     * @return string
     */
    public function addCampaignLink($value, $row, $column, $export)
    {
        $id = $row->getCampaignId();
        if($id && !$export) {
            $url = $this->getUrl('*/admin_campaign/edit', array('id' => $id));
            return sprintf('<a href="%s">%s</a>', $url, $value);
        }
        return $value;
    }
    
    
    
    
    
    
    
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('email_id');
        $this->getMassactionBlock()->setFormFieldName('messages');
        
        
        $this->getMassactionBlock()->addItem('send', array(
            'label'      => $this->__('Send'),
            'url'        => $this->getUrl('*/admin_outbox/massSend'),
            'confirm'    => $this->__('Are you sure you want to send those emails now?')
        ));
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'   => $this->__('Delete'),
             'url'     => $this->getUrl('*/admin_outbox/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('discard', array(
            'label'      => $this->__('Discard'),
            'url'        => $this->getUrl('*/admin_outbox/massDiscard'),
            'confirm'    => $this->__('Are you sure?')
        ));
        
        
        return $this;
    }
    
    
    
    
    
    
    

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/email', array('id'=>$row->getId()));
    }
}
