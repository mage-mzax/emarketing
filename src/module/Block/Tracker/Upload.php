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
 *
 *
 * @author Jacob Siefer
 * @license {{license}}
 */
class Mzax_Emarketing_Block_Tracker_Upload extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';

        $this->_blockGroup = 'mzax_emarketing';
        $this->_controller = 'tracker';
        $this->_mode       = 'upload';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Upload Tracker'));
        $this->removeButton('delete');

    }



    public function getHeaderText()
    {
        return $this->__('Upload New Tracker');
    }



    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/uploadPost');
    }


}
