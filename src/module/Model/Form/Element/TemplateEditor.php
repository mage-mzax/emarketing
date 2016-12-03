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


class Mzax_Emarketing_Model_Form_Element_TemplateEditor extends Mzax_Emarketing_Model_Form_Element_Ace
{

    public function getEditorClass()
    {
        return 'mzax.ui.TemplateEditor';
    }


    public function getTypeClass()
    {
        return 'mage-template-editor';
    }




    /**
     * Retrieve editor options
     *
     * @return array
     */
    public function getEditorOptions()
    {
        $storeId = $this->getConfig('store_id');
        $storeParam = null !== $storeId ? 'store/' . $this->getConfig('store_id') . '/' : '';

        $options = parent::getEditorOptions();
        $options['mediaBrowserUrl'] = "{$this->getConfig('files_browser_window_url')}target_element_id/{editor}/{$storeParam}";
        $options['widgetToolsUrl']  = "{$this->getConfig('widget_window_url')}widget_target_id/{editor}";

        return $options;
    }






    /**
     * Prepare Html buttons for additional WYSIWYG features
     *
     * @param bool $visible Display button or not
     * @return void
     */
    protected function _getPluginButtonsHtml()
    {
        $buttonsHtml = '';

        if (!$this->getReadonly()) {
            // Button to widget insertion window
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Insert MageCode'),
                'onclick'   => "{editor}.execCommand('mage');",
                'class'     => 'mzax-variable'
            ));
        }

        // Button to widget insertion window
        if (!$this->getReadonly() && $this->getConfig('add_widgets', true) && $this->getConfig('widget_window_url')) {
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Insert Widget'),
                'onclick'   => "{editor}.insertWidget();",
                'class'     => 'mzax-widget'
            ));
        }

        // Button to media images insertion window
        if (!$this->getReadonly() && $this->getConfig('add_images', true) && $this->getConfig('files_browser_window_url')) {
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Insert Image'),
                'onclick'   => "{editor}.browserMedia();",
                'class'     => 'mzax-image'
            ));
        }


        if ($this->getConfig('allow_fullscreen', true)) {
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Fullscreen'),
                'onclick'   => "{editor}.fullscreen();",
                'class'     => 'mzax-fullscreen'
            ));
        }

        $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Switch Vert/Horz'),
                'onclick'   => "{editor}.switchLayout();",
                'class'     => 'mzax-layout-mode'
        ));


        $buttons = $this->getConfig('buttons');
        if (is_array($buttons)) {
            foreach ($buttons as $button) {
                $buttonsHtml .= $this->_getButtonHtml($button);
            }
        }


        return $buttonsHtml;
    }




}
