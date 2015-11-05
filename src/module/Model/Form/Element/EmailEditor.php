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
 * Email Editor
 * 
 * The email editor requires a template to work.
 *
 * @author Jacob Siefer
 * @license {{license}}
 * @version {{version}}
 */
class Mzax_Emarketing_Model_Form_Element_EmailEditor 
    extends Mzax_Emarketing_Model_Form_Element_TemplateEditor
{
    
    
    /**
     * The JS editor class
     * 
     * @return string
     */
    public function getEditorClass()
    {
        return 'mzax.ui.PreviewFrame';
    }
    
    
    
    /**
     * CSS class for this editor
     * 
     * @return string
     */
    public function getTypeClass()
    {
        return 'mage-email-editor';
    }
    
    
    
    
    /**
     * Retrieve editor options
     *
     * @return array
     */
    public function getEditorOptions()
    {
        $storeId = $this->getConfig('store_id');
        $store = Mage::app()->getStore($storeId);
        $storeParam = null !== $storeId ? 'store/' . $this->getConfig('store_id') . '/' : '';
    
        $options = parent::getEditorOptions();
        
        $options['quicksaveUrl'] = $this->getConfig('quicksave_url');
        $options['quicksaveFields'] = $this->getQuicksaveFields();
        $options['templateLoadUrl'] = $this->getConfig('template_load_url');
        $options['html'] = $this->getTemplateHtml();
        $options['fieldName'] = $this->getData('name');
        $options['enableCKEditor'] = $this->ckeEnabled();
        $options['startEdit'] = true;
        
        /* Good enough - only for preview, no fallback required
         * http://www.example.com/skin/frontend/{package}/{theme}/ 
         */
        $skinPath = array();
        $skinPath[] = $store->getBaseUrl('skin') . 'frontend';
        $skinPath[] = $store->getConfig('design/package/name');
        $skinPath[] = $store->getConfig('design/theme/layout');
        
        $options['skinUrl']  = implode('/', $skinPath) . '/';
        $options['mediaUrl'] = $store->getBaseUrl($store::URL_TYPE_MEDIA);
        $options['storeUrl'] = $store->getBaseUrl($store::URL_TYPE_WEB);

        $options['ckeditorSrc'] = $store->getBaseUrl($store::URL_TYPE_JS) . 'mzax/ckeditor/ckeditor.js';
        $options['editorCss']   = Mage::getDesign()->getSkinUrl('mzax/editor.css');

        return $options;
    }
    
    
    
    

    public function getExtaScript()
    {
        $templateField = $this->getConfig('template_field') ? $this->getConfig('template_field')->getHtmlId() : '';
        
        if($templateField) {
            return <<<JS
            var templateField = $('$templateField');
            if(templateField) {
                templateField.on('change', function() {
                    editor.loadTemplate(this.value);
                });
            }
JS;
        }
        return '';
    }
    
    
    
    protected function getQuicksaveFields()
    {
        $fields = $this->getConfig('quicksave_fields');
        $quicksaveFields = array();
        if(is_array($fields)) {
            foreach($fields as $field) {
                if($field instanceof Varien_Data_Form_Element_Abstract) {
                    $quicksaveFields[$field->getData('name')] = $field->getHtmlId();
                }
            }
        }
        return $quicksaveFields;
    }
    
    
    
    public function getTemplateHtml()
    {
        if($this->getTemplate()) {
            $html = (string) $this->getTemplate()->getBody();
        }
        else {
            $html = '';
        }
        return $html;
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
        
        if ($this->getConfig('allow_fullscreen', true)) {
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Fullscreen'),
                'onclick'   => "{editor}.fullscreen();",
                'class'     => 'mzax-fullscreen'
            ));
        }
        
        $buttons = $this->getConfig('buttons');
        if(is_array($buttons)) {
            foreach($buttons as $button) {
                $buttonsHtml .= $this->_getButtonHtml($button);
            }
        }
            
            
        return $buttonsHtml;
    }
    
    
    
    
}
