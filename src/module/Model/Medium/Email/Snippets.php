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
 * Class Mzax_Emarketing_Model_Medium_Email_Snippets
 *
 * Snippets are used by the ACE code editor for auto completion
 */
class Mzax_Emarketing_Model_Medium_Email_Snippets
{
    /**
     * @var array[]
     */
    protected $_snippets = array();

    /**
     * @param array $snippet
     *
     * @return $this
     * @throws Exception
     */
    public function add(array $snippet)
    {
        if (!isset($snippet['value'])) {
            throw new Exception("No value property defined for snippet");
        }
        if (!isset($snippet['title'])) {
            throw new Exception("No title property defined for snippet");
        }
        if (!isset($snippet['snippet'])) {
            throw new Exception("No snippet property defined for snippet");
        }

        $this->_snippets[$snippet['value']] = $snippet;

        return $this;
    }

    /**
     * Add snippet
     *
     * @param $value
     * @param $snippet
     * @param $title
     * @param null $description
     * @param null $shortcut
     *
     * @return $this
     */
    public function addSnippets($value, $snippet, $title, $description = null, $shortcut = null)
    {
        return $this->add(array(
            'title'       => $title,
            'description' => $description,
            'snippet'     => $snippet,
            'value'       => $value,
            'shortcut'    => $shortcut
        ));
    }

    /**
     * Add var snippet
     *
     * @param $value
     * @param $title
     * @param null $description
     * @param null $shortcut
     *
     * @return $this
     */
    public function addVar($value, $title, $description = null, $shortcut = null)
    {
        return $this->addSnippets('mage.' . $value, '{{var ' . $value . '}}', $title, $description, $shortcut);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = $this->_snippets;
        ksort($data);

        return array_values($data);
    }
}
