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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract
 */
class Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Retrieve current campaign
     *
     * @return Mzax_Emarketing_Model_Campaign
     */
    public function getCampaign()
    {
        return Mage::registry('current_campaign');
    }

    /**
     * Retrieve campaign content
     * Usually the campaign it self or a variation object
     *
     * @return Mzax_Emarketing_Model_Campaign_Content
     */
    public function getContent()
    {
        return $this->getData('content');
    }

    /**
     *
     * @param Varien_Data_Form $form
     *
     * @return $this
     */
    public function initForm(Varien_Data_Form $form)
    {
        $this->setForm($form);
        $this->_prepareForm();

        return $this;
    }
}
