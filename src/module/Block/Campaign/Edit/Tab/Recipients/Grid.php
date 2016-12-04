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


class Mzax_Emarketing_Block_Campaign_Edit_Tab_Recipients_Grid extends Mzax_Emarketing_Block_Filter_Object_Grid
{

    /**
     * @var Mzax_Emarketing_Model_Campaign
     */
    protected $_campaign;



    public function __construct()
    {
        parent::__construct();
        $this->setId('recipients_grid');
        $this->setUseAjax(true);

        $this->addExportType('*/*/exportRecipients', 'CSV');
    }



    /**
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        if (!$this->_campaign) {
            $this->_campaign = Mage::registry('current_campaign');
        }
        return $this->_campaign;
    }




    public function getFilter()
    {
        $filter = $this->getCampaign()->getRecipientProvider();
        $this->prepareEmulation($filter);

        return $filter;
    }



    public function prepareEmulation(Mzax_Emarketing_Model_Object_Filter_Abstract $filter)
    {
        $parent = $this->getParentBlock();
        if ($parent && method_exists($parent, 'prepareEmulation')) {
            $parent->prepareEmulation($filter);
        }
    }



    /**
     * Prepare grid columns
     *
     * This is done by the email provider. The grid
     * does not know what type of objects it is loading
     *
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->removeColumn('matches');
        $this->getCampaign()->getMedium()->prepareRecipientGrid($this);

        return $this;
    }



    public function getGridUrl()
    {
        return $this->getUrl('*/*/recipientsGrid', array('_current' => true));
    }


    /*
    public function getAdditionalJavaScript()
    {
        $object = $this->getMassactionBlock()->getJsObjectName();
        return "window.{$object} = {$object};";
    }
*/

}
