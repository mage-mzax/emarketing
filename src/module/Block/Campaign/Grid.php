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
 * Campaign Grid
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Block_Campaign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{


    protected $_tagColors = array('627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4', 'A559BF', '14D277', '627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4', 'A559BF', '14D277','627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4', 'A559BF', '14D277','627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4', 'A559BF', '14D277','627379', 'FC7A00', 'BF4848', '87969E', 'D7D020', '00A6D4', 'A559BF', '14D277');

    protected $_tagColorMap = array();


    public function __construct()
    {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('campaign_id');
    }



    protected function _prepareCollection()
    {
        /* @var $collection Mzax_Emarketing_Model_Resource_Campaign_Collection */
        $collection = Mage::getResourceModel('mzax_emarketing/campaign_collection');

        if (!$this->getRequest()->getParam('archive')) {
            $collection->addArchiveFilter(false);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }



    protected function _prepareLayout()
    {
        if ( $this->getRequest()->getParam('archive') ) {
            $this->setChild('archive_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label'   => $this->__('Hide Archived'),
                    'onclick' => "{$this->getJsObjectName()}.addVarToUrl('archive', 0); {$this->getJsObjectName()}.reload();",
                    'class'   => 'task'
                ))
            );
        }
        else {
            $this->setChild('archive_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label'   => $this->__('Show Archived'),
                    'onclick' => "{$this->getJsObjectName()}.addVarToUrl('archive', 1); {$this->getJsObjectName()}.reload();",
                    'class'   => 'task'
                ))
            );
        }

        return parent::_prepareLayout();
    }




    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getChildHtml('archive_button') . $html;

        return $html;
    }




    protected function _prepareColumns()
    {
        $this->addColumn('added_at', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Date Added'),
            'index'     => 'created_at',
            'gmtoffset' => true,
            'width'     => 150,
            'type'      => 'datetime'
        ));

        $this->addColumn('modified_at', array(
            'header'    => $this->__('Last Change'),
            'index'     => 'updated_at',
            'gmtoffset' => true,
            'width'     => 150,
            'type'      => 'datetime'
        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Name'),
            'index'     => 'name',
            'frame_callback' => array($this, 'renderName')
        ));


        $this->addColumn('recipients', array(
            'header'    => $this->__('Recipients'),
            'index'     => 'provider',
            'type'      => 'options',
            'width'     => 150,
            'options'   => Mage::getSingleton('mzax_emarketing/recipient_provider')->getOptionHash()
        ));

        $this->addColumn('medium', array(
            'header'    => $this->__('Medium'),
            'index'     => 'medium',
            'type'      => 'options',
            'width'     => 100,
            'options'   => Mage::getSingleton('mzax_emarketing/medium')->getMediums()
        ));

        $this->addColumn('running', array(
            'header'    => $this->__('Is running'),
            'index'     => 'running',
            'type'      => 'options',
            'width'     => 80,
            'options'   => array(
                0 => $this->__('No'),
                1 => $this->__('Yes')
            )
        ));

        $this->addColumn('stats', array(
            'header'   => Mage::helper('mzax_emarketing')->__('Quick Stats'),
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'mzax_emarketing/campaign_grid_renderer_stats'
        ));

        $this->addColumn('count', array(
            'header'    => $this->__('Recip.'),
            'index'     => 'sending_stats',
            'filter'    => false,
            'width'     => 50,
            'type'      => 'number'
        ));

        return parent::_prepareColumns();
    }


    /**
     * Name Frame Callback
     *
     * @param string $value
     * @param Mzax_Emarketing_Model_Campaign $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param boolean $export
     * @return string
     */
    public function renderName($value, $row, $column, $export)
    {
        if (!$export) {

            if ($row->isRunning()) {
                $value .= ' <span class="mzax-grid-running"></span>';
            }

            foreach ($row->getTags() as $tag) {

                $t = strtolower($tag);
                if (!isset($this->_tagColorMap[$t])) {
                    $this->_tagColorMap[$t] = $this->_tagColors[count($this->_tagColorMap)%count($this->_tagColors)];
                }

                $value .= ' <span class="mzax-grid-tag" style="background-color: #' . $this->_tagColorMap[$t] . ';">' . $this->escapeHtml($tag) . '</span>';
            }
        }
        return $value;
    }




    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('campaign_id');
        $this->getMassactionBlock()->setFormFieldName('campaigns');

        $this->getMassactionBlock()->addItem('start', array(
            'label'   => $this->__('Start campaigns'),
            'url'     => $this->getUrl('*/*/massStart'),
            'confirm' => $this->__('Are you sure you want to START all selected campaigns?')
        ));
        $this->getMassactionBlock()->addItem('stop', array(
            'label'   => $this->__('Stop campaigns'),
            'url'     => $this->getUrl('*/*/massStop'),
            'confirm' => $this->__('Are you sure you want to STOP all selected campaigns?')
        ));
        $this->getMassactionBlock()->addItem('archive', array(
            'label'   => $this->__('Archive campaigns'),
            'url'     => $this->getUrl('*/*/massArchive'),
            'confirm' => $this->__('Are you sure you want to archive all selected campaigns?')
        ));

        $this->getMassactionBlock()->addItem('add_tag', array(
            'label'=> $this->__('Add tag(s)...'),
            'url'  => $this->getUrl('*/*/massAddTag', array('_current'=>true)),
            'additional' => array(
                'tags' => array(
                    'name' => 'tags',
                    'type' => 'text',
                    'class' => 'required-entry',
                    'label' => $this->__('Tag(s)')
                )
            )
        ));

        $this->getMassactionBlock()->addItem('remove_tag', array(
            'label'=> $this->__('Remove tag(s)...'),
            'url'  => $this->getUrl('*/*/massRemoveTag', array('_current'=>true)),
            'additional' => array(
                'tags' => array(
                    'name' => 'tags',
                    'type' => 'text',
                    'class' => 'required-entry',
                    'label' => $this->__('Tag(s)')
                )
            )
        ));


        return $this;
    }





    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
