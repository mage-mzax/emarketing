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


class Mzax_Emarketing_Block_Inbox_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('inbox_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
    }


    public function getCollection()
    {
        if (!$this->_collection) {
            /* Mzax_Emarketing_Model_Resource_Inbox_Email_Collection */
            $this->_collection = Mage::getResourceModel('mzax_emarketing/inbox_email_collection');
            $this->_collection->assignRecipients();
            $this->_collection->assignCampaigns();
        }
        return $this->_collection;
    }


    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header'    => $this->__('Created At'),
            'index'     => 'created_at',
            'gmtoffset' => true,
            'width'     => 150,
            'type'      =>'datetime'
        ));

        $this->addColumn('recipient', array(
            'header'         => $this->__('Recipient'),
            'index'          => 'email',
            'frame_callback' => array($this, 'addCampaignLink'),
        ));

        $this->addColumn('subject', array(
            'header'    => $this->__('Subject'),
            'index'     => 'subject',
            'type'      => 'text',
            'truncate'  => 50
        ));
        $this->addColumn('message', array(
            'header'    => $this->__('Message'),
            'index'     => 'message',
            'type'      => 'text',
            'truncate'  => 100
        ));
        $this->addColumn('type', array(
            'header'    => $this->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                Mzax_Emarketing_Model_Inbox_Email::BOUNCE_SOFT => $this->__('Soft'),
                Mzax_Emarketing_Model_Inbox_Email::BOUNCE_HARD => $this->__('Hard'),
                Mzax_Emarketing_Model_Inbox_Email::AUTOREPLY   => $this->__('Autoreply'),
                Mzax_Emarketing_Model_Inbox_Email::UNSUBSCRIBE => $this->__('Unsubscribe'),
                Mzax_Emarketing_Model_Inbox_Email::NO_BOUNCE   => $this->__('No Bounce'),
            ),
            'width'     => '100px',
        ));


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


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'          => $this->__('Store'),
                'index'           => 'store_id',
                'type'            => 'store',
                'store_view'      => false,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('status_code', array(
            'header'    => $this->__('Status'),
            'index'     =>'status_code'
        ));

        $this->addColumn('size', array(
            'header'    => $this->__('Size'),
            'index'     =>'size',
            'filter'    => false,
            'renderer'  => 'mzax_emarketing/grid_column_renderer_size',
            'width'     => '50px',
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
    public function addSubjectLink($value, $row, $column, $export)
    {
        $recipient = $row->getRecipient();

        if ($recipient instanceof Mzax_Emarketing_Model_Recipient && !$export)
        {
            $campaign = $recipient->getCampaign();
            $subject  = $campaign->getRecipientProvider()->getSubject();

            if ($subject) {
                $url = $subject->getAdminUrl($recipient->getObjectId());
                return sprintf('<a href="%s">%s</a>', $url, $value);
            }

        }
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
        if ($id && !$export) {
            $url = $this->getUrl('*/emarketing_campaign/edit', array('id' => $id));
            return sprintf('<a href="%s">%s</a>', $url, $value);
        }
        return $value;
    }








    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('email_id');
        $this->getMassactionBlock()->setFormFieldName('messages');


        $this->getMassactionBlock()->addItem('unsubscribe', array(
                'label'   => $this->__('Unsubscribe Email(s)'),
                'url'     => $this->getUrl('*/*/massUnsubscribe'),
                'confirm' => $this->__('Are you sure you want to unsubscribe all selected emails?')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
             'label'   => $this->__('Delete Email(s)'),
             'url'     => $this->getUrl('*/*/massDelete'),
             'confirm' => $this->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('reparse', array(
            'label'   => $this->__('Re-parse Email(s)'),
            'url'     => $this->getUrl('*/*/massParse')
        ));

        $this->getMassactionBlock()->addItem('flag_as', array(
                'label'=> $this->__('Flag as...'),
                'url'  => $this->getUrl('*/*/massFlag', array('_current'=>true)),
                'additional' => array(
                'visibility' => array(
                    'name' => 'type',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Type'),
                    'options'   => array(
                        Mzax_Emarketing_Model_Inbox_Email::BOUNCE_SOFT => $this->__('Soft'),
                        Mzax_Emarketing_Model_Inbox_Email::BOUNCE_HARD => $this->__('Hard'),
                        Mzax_Emarketing_Model_Inbox_Email::AUTOREPLY   => $this->__('Autoreply'),
                        Mzax_Emarketing_Model_Inbox_Email::NO_BOUNCE   => $this->__('No Bounce'),
                    ),
                )
            )
        ));

        $this->getMassactionBlock()->addItem('forward', array(
            'label'   => $this->__('Forward emails'),
            'url'     => $this->getUrl('*/*/massForward')
        ));


        // enable disable
        $this->getMassactionBlock()->addItem('report', array(
            'label'   => $this->__('Report as bounce'),
            'url'     => $this->getUrl('*/*/massReport'),
            'confirm' => $this->__('Thank you for your help! This will forward the email to me (Jacob Siefer) and I will use it to test it against the bounce filter. I will not share or publish any of the orignal content.')
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
