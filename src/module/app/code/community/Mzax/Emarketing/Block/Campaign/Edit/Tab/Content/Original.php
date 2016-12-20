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
 * Class Mzax_Emarketing_Block_Campaign_Edit_Tab_Content_Original
 */
class Mzax_Emarketing_Block_Campaign_Edit_Tab_Content_Original extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return $this
     */
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('campaign_');
        $form->setFieldNameSuffix('campaign');

        $this->setForm($form);

        return $this;
    }

    /**
     *
     * @return Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract
     */
    public function getContentForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('campaign_mediumdata_');
        $form->setFieldNameSuffix('campaign[medium_data]');

        /* @var $mediumForm Mzax_Emarketing_Block_Campaign_Edit_Medium_Abstract */
        $mediumForm = $this->getLayout()->createBlock('mzax_emarketing/campaign_edit_medium_email');
        $mediumForm->setContent($this->getContent());
        $mediumForm->initForm($form);

        return $mediumForm;
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        $html = $this->getContentForm()->getFormHtml();
        $html.= parent::getFormHtml();

        return $html;
    }
}
