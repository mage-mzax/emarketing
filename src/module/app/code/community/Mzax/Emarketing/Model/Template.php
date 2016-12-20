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
 *
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $value)
 *
 * @method string getUpdatedAt()
 * @method $this setUpdatedAt(string $value)
 *
 * @method string getName()
 * @method $this setName(string $value)
 *
 * @method string getDescription()
 * @method $this setDescription(string $value)
 *
 * @method string getBody()
 * @method $this setBody(string $value)
 *
 * @method string getVersion()
 * @method $this setVersion(string $value)
 */
class Mzax_Emarketing_Model_Template
    extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mzax_emarketing_template';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'template';

    /**
     * @var Mzax_Emarketing_Helper_Data
     */
    protected $_helper;

    /**
     * Model Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mzax_emarketing/template');

        $this->_helper = Mage::helper('mzax_emarketing');
    }

    /**
     * Load template data from file
     *
     * @param string $filename
     *
     * @return $this
     * @throws Mage_Exception
     */
    public function loadFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new Mage_Exception("File not found ($filename)");
        }
        $this->import(file_get_contents($filename));

        return $this;
    }

    /**
     * Load template data from encoded str
     *
     * @see export()
     * @param string $str
     *
     * @return $this
     * @throws Mage_Exception
     */
    public function import($str)
    {
        try {
            $data = Zend_Json::decode(base64_decode($str));
            $this->addData($data);
        } catch (Zend_Json_Exception $e) {
            throw new Mage_Exception("Failed to decode template file");
        }
        return $this;
    }

    /**
     * Convert to template to encoded string
     *
     * @return string
     */
    public function export()
    {
        // set current extension version
        $this->setVersion($this->_helper->getVersion());

        $json = $this->toJson(array('version', 'credits','name', 'description', 'body'));
        return base64_encode($json);
    }

    /**
     * Parse template html
     *
     * @param string $html
     *
     * @return DOMDocument
     * @throws Mzax_Emarketing_Model_Template_Exception
     */
    public function parse($html)
    {
        $this->_helper->encodeMageExpr($html);

        // php loadHtml and xml namespace don't work but we can just use simple attributes
        // so just replace them (mage:id => mage-id,...)
        $html = preg_replace('/mage:([a-z]+)=["\'](.*?)["\']/i', 'mage-$1="$2"', $html);

        $document = new DOMDocument('1.0', 'utf8');
        libxml_use_internal_errors(true);
        $document->loadHTML($html);

        $errors = libxml_get_errors();
        if (!empty($errors)) {
            throw new Mzax_Emarketing_Model_Template_Exception($errors);
        }

        return $document;
    }

    /**
     * Render template html with the editor field data
     *
     * @param array $data
     *
     * @return string
     */
    public function render(array $data)
    {
        $fields = $data['fields'];

        $html = $this->getBody();

        $document = $this->parse($html);
        $xpath = new DOMXpath($document);

        $placeholders = array();
        $fieldMapping = array();

        if ($elements = $xpath->query("//*[@mage-id]")) {
            /*
             * Fetch all mage elements and insert any
             * repeated content
             */
            /** @var DOMElement $element */
            foreach ($elements as $element) {
                $id = $element->getAttribute('mage-id');
                $repeatable = $this->isTrue($element->getAttribute('mage-repeatable'));

                if (!isset($fields[$id])) {
                    continue;
                }

                $field = $fields[$id];
                if ($repeatable && count($field) > 1) {
                    for ($i = 1; $i < count($field); $i++) {
                        $copy = $element->cloneNode(true);
                        $element->parentNode->appendChild($copy);
                    }
                }
            }

            /*
             * Fetch again all fields and try to map all elements
             * to a field
             */
            /** @var DOMElement $element */
            foreach ($xpath->query("//*[@mage-id]") as $element) {
                $id = $element->getAttribute('mage-id');

                // skip if no field found
                if (!isset($fields[$id])) {
                    continue;
                }
                $field = array_shift($fields[$id]);
                $fieldMapping[] = array($element, $field);
            }

            /*
             * Now that we have a valid mapping go through it and
             * work out the elements that should get removed
             */
            foreach ($fieldMapping as $binding) {
                /** @var DOMElement $element */
                list($element, $field) = $binding;

                if (!$field) {
                    if ($element->parentNode) {
                        $element->parentNode->removeChild($element);
                        continue;
                    }
                }
                $editable  = $this->isTrue($element->getAttribute('mage-editable'));
                $removable = $this->isTrue($element->getAttribute('mage-removable'));

                $remove = isset($field['remove']) ? $field['remove'] : false;

                if ($removable && $remove) {
                    if ($element->parentNode) {
                        $element->parentNode->removeChild($element);
                        continue;
                    }
                }

                if ($editable) {
                    // don't bother inserting HTML using DOMNode,
                    // just add a placeholder and use regex later
                    $value = '?!?!----' . md5(microtime().rand(0, 10000)) . '----!?!?';
                    $placeholders[$value] = $field['value'];

                    switch ($element->nodeName) {
                        case 'img':
                            if (isset($field['value'])) {
                                $element->setAttribute('src', $value);
                            }
                            if (isset($field['alt'])) {
                                $element->setAttribute('alt', $field['alt']);
                            }
                            break;

                        default:
                            $element->nodeValue = $value;
                            break;
                    }
                }
            }
        }

        // add custom css to head tag if available
        if (isset($data['customCss'])) {
            $head = $xpath->query("//head");
            if (count($head) === 1) {
                $head = $head->item(0);

                $value = '?!?!----' . md5(microtime().rand(0, 10000)) . '----!?!?';
                $placeholders[$value] = $data['customCss']['value'];

                $style = $document->createElement("style");
                $style->setAttribute('type', 'text/css');
                $style->nodeValue = $value;
                $head->appendChild($style);
            }
        }

        $html = $document->saveHTML();

        // replace all pending placeholders
        $html = preg_replace_callback(
            '/\?!\?!----([0-9A-F]{32})----!\?!\?/i',
            function ($match) use ($placeholders) {
                if (isset($placeholders[$match[0]])) {
                    return $placeholders[$match[0]];
                }
                return '';
            },
            $html
        );

        $this->_helper->decodeMageExpr($html);

        return $html;
    }

    /**
     * A simple check if an attribute value represent true or false
     *
     * @param string $value
     * @return boolean
     */
    protected function isTrue($value)
    {
        return (bool) preg_match('/^(1|true|yes|y)$/i', $value);
    }
}
