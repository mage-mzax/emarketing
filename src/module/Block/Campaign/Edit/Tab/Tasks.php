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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_Tasks
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_Tasks extends Mage_Adminhtml_Block_Template
{
    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('mzax/emarketing/campaign/tasks.phtml');

        return $this;
    }

    /**
     * Retrieve campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        return Mage::registry('current_campaign');
    }

    /**
     * Retrieve action url
     *
     * @param string $action
     *
     * @return string
     */
    public function getActionUrl($action)
    {
        return $this->getUrl('*/*/' . $action, array(
            'id' => $this->getCampaign()->getId()
        ));
    }
}
