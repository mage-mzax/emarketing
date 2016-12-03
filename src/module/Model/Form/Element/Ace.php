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


class Mzax_Emarketing_Model_Form_Element_Ace extends Varien_Data_Form_Element_Abstract
{



    public function getEditorClass()
    {
        return 'mzax.ui.TextEditor';
    }



    public function getTypeClass()
    {
        return 'mage-ace-editor';
    }





    /**
     * Retrieve editor options
     *
     * @return array
     */
    public function getEditorOptions()
    {
        $options = array(
            'theme'     => $this->getConfig('theme', 'ace/theme/mage'),
            'mode'      => $this->getConfig('mode', 'ace/mode/mage'),
            'enableAce' => $this->aceEnabled(),
            'autosize'  => $this->getConfig('autosize', false),
            'readOnly'  => $this->getReadonly(),
            'snippets'  => $this->getSnippets()
        );

        return $options;
    }




    public function getElementHtml()
    {
        $helper = Mage::helper('core');

        $id = $this->getHtmlId();
        $jsId = "editor{$id}";

        $logo = $this->getLogo();
        if ($logo) {
            $logo = '<img src="' . $logo . '" class="logo" />';
        }

        $buttonsHtml = $this->_getPluginButtonsHtml();


        $toolbarHtml = <<<JS
        <div id="control-buttons-$id" class="mzax-editor-toolbar">
            $logo
            <div class="ftitle">{$this->getFullscreenTitle()}</div>
            <div class="buttons-set">
            {$buttonsHtml}
            </div>
        </div>
JS;

        $toolbarHtml = str_replace('{editor}', $jsId, $toolbarHtml);

        $classes = array('mzax-editor');
        $classes[] = $this->getClass();
        $classes[] = $this->getTypeClass();
        $classes[] = $buttonsHtml ? 'has-plugin-buttons' : 'no-plugin-buttons';

        $classes = array_filter($classes);

        $html = '<div title="' . $this->getTitle()
              . '" id="' . $id . '"'
              . ' class="mzax-editor mage-ace-editor ' . implode(' ', $classes) .'" '
              . $this->serialize($this->getHtmlAttributes()) . ' >';

        $html.= '<textarea id="' . $id . '_txt" name="' . $this->getName() . '" style="display:none;">'.$this->getEscapedValue().'</textarea>';
        $html.= $toolbarHtml;
        $html.= '<div class="editor-container"></div>';
        $html.= '</div>';

        $html.= '<script type="text/javascript">';
        $html.= $this->getJavaScript($jsId);
        $html.= '</script>';


        return $html;
    }


    public function getJavaScript($jsId)
    {
        $options = Zend_Json::encode($this->getEditorOptions());
        $options = str_replace('{editor}', $jsId, $options);

        $cls = $this->getEditorClass();

        return <<<JS
        window.$jsId = (function() {
        
            var div = $("{$this->getHtmlId()}"),
                txt = div.down('textarea'),
                editor = new {$cls}(div.down('.editor-container'), {$options});
        
            editor.setValue(txt.value);
            editor.htmlId = div.id;
        
            editor.fullscreen = function() {
                div.toggleClassName('fullscreen');
                document.body.toggleClassName('mzax-fullscreen');
                editor.refreshUi();
            };
        
            varienGlobalEvents.attachEventHandler('formSubmit', function(event) {
                txt.value = editor.getValue();
            });
                        
            {$this->getExtaScript()}
        
            return editor;
        })();
JS;
    }



    public function getExtaScript()
    {
        return '';
    }




    public function getSnippets()
    {
        $data = $this->getConfig('snippets');

        /* @var $store Mage_Core_Model_Store */
        $store = $this->getConfig('store', Mage::app()->getStore(true));

        if (!$data instanceof Mzax_Emarketing_Model_Medium_Email_Snippets) {
            $data = new Mzax_Emarketing_Model_Medium_Email_Snippets;
        }


        /* @var $variable Mage_Core_Model_Variable */
        foreach (Mage::getResourceModel('core/variable_collection') as $variable) {
            $data->add(array(
                'title'       => $this->translate('%s', $variable['name']),
                'description' => $this->translate('A custom variable, see Magento Admin -> System -> Custom Variables'),
                'snippet'     => '{{customVar code=' . $variable['code'] . '}}',
                'value'       => 'mage.custom_var.' . $variable['code'],
                'text'        => $variable->getValue($variable::TYPE_TEXT),
                'html'        => $variable->getValue($variable::TYPE_HTML),
                'shortcut'    => null
            ));
        }


        $storeContactVariabls = Mage::getModel('core/source_email_variables')->toOptionArray(false);
        foreach ($storeContactVariabls as $var) {
            $path = preg_replace('/\{\{config path="(.*)"\}\}/i', '$1', $var['value']);
            $data->add(array(
                'title'       => $var['label'],
                'description' => $var['label'],
                'snippet'     => $var['value'],
                'value'       => 'mage.config.' . str_replace('/', '.', $path),
                'text'        => $store->getConfig($path),
                'shortcut'    => null
            ));
        }


        $data->add(array(
            'title'       => $this->translate("Magento Template Block"),
            'description' => $this->translate('Insert html from a php block class'),
            'snippet'     => '{{block type="${1:core/template}" area="${3:frontend}" template="${2}" }}',
            'value'       => 'mage.block.insert',
            'shortcut'    => 'block'
        ));


        if ($this->getConfig('add_widgets', true) && $this->getConfig('widget_window_url')) {
            $data->add(array(
                'title'       => $this->translate('Magento Widget'),
                'description' => $this->translate('Start magento widget helper tool'),
                'snippet'     => '{{js:insertWidget}}',
                'value'       => 'mage.widget',
                'shortcut'    => 'widget'
            ));
        }

        if ($this->getConfig('add_images', true) && $this->getConfig('files_browser_window_url')) {
            $data->add(array(
                'title'       => $this->translate('Magento Image'),
                'description' => $this->translate('Start magento image browser'),
                'snippet'     => '{{js:browserMedia}}',
                'value'       => 'mage.image',
                'shortcut'    => 'image'
            ));
        }


        return $data->toArray();
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
                'onclick'   => "{editor}.execCommand('mage', {source: 'mage'});",
                'class'     => 'mzax-variable'
            ));
        }

        // Button to widget insertion window
        if (!$this->getReadonly() && $this->getConfig('add_widgets', true) && $this->getConfig('widget_window_url')) {
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Insert Widget'),
                'onclick'   => "{editor}.openWidgetTools();",
                'class'     => 'mzax-widget'
            ));
        }

        // Button to media images insertion window
        if (!$this->getReadonly() && $this->getConfig('add_images', true) && $this->getConfig('files_browser_window_url')) {
            $buttonsHtml .= $this->_getButtonHtml(array(
                'title'     => $this->translate('Insert Image'),
                'onclick'   => "{editor}.openMediabrowser();",
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

        $buttons = $this->getConfig('buttons');
        if (is_array($buttons)) {
            foreach ($buttons as $button) {
                $buttonsHtml .= $this->_getButtonHtml($button);
            }
        }


        return $buttonsHtml;
    }




    /**
     * Return custom button HTML
     *
     * @param array $data Button params
     * @return string
     */
    protected function _getButtonHtml($data)
    {
        $html = '<button type="button"';
        $html.= ' class="scalable '.(isset($data['class']) ? $data['class'] : '').'"';
        $html.= isset($data['onclick']) ? ' onclick="'.$data['onclick'].'"' : '';
        $html.= isset($data['style']) ? ' style="'.$data['style'].'"' : '';
        $html.= isset($data['id']) ? ' id="'.$data['id'].'"' : '';
        $html.= '>';
        $html.= isset($data['title']) ? '<span>'.$data['title'].'</span>' : '';
        $html.= '</button>';

        return $html;
    }





    /**
     * Editor config retriever
     *
     * @param string $key Config var key
     * @return mixed
     */
    public function getConfig($key = null, $default = null)
    {
        if ( !($this->_getData('config') instanceof Varien_Object) ) {
            $config = new Varien_Object();
            $this->setConfig($config);
        }

        if ($key !== null) {
            $data = $this->_getData('config')->getData($key);
            if ($data !== null) {
                return $data;
            }
            $data = $this->getData($key);
            if ($data !== null) {
                return $data;
            }
            return $default;
        }
        return $this->_getData('config');
    }



    /**
     * Is ACE editor enabled
     *
     * @return boolean
     */
    public function aceEnabled()
    {
        $enabled = Mage::getStoreConfigFlag('mzax_emarketing/content_management/enable_ace');
        if (!$enabled) {
            return 0;
        }
        return (int) $this->getConfig('enable_ace', 1);
    }


    /**
     * Is CKEditor enabled
     *
     * @return boolean
     */
    public function ckeEnabled()
    {
        $enabled =  Mage::getStoreConfigFlag('mzax_emarketing/content_management/enable_ckeditor');
        if (!$enabled) {
            return 0;
        }
        return (int) $this->getConfig('enable_ckeditor', 1);
    }



    /**
     * Translate string using defined helper
     *
     * @param string $string String to be translated
     * @return string
     */
    public function translate($string)
    {
        $translator = $this->getConfig('translator');
        if (is_object($translator) && is_callable(array($translator, '__'))) {
            $result = call_user_func_array(array($translator, '__'), func_get_args());
            if (is_string($result)) {
                return $result;
            }
        }

        return $string;
    }
}
