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
 * Class Mzax_Emarketing_Block_Campaign_View
 */
class Mzax_Emarketing_Block_Campaign_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Mzax_Emarketing_Block_Campaign_View constructor.
     */
    public function __construct()
    {
        $this->_blockGroup = 'mzax_emarketing';
        $this->_controller = 'campaign';
        $this->_headerText = Mage::helper('mzax_emarketing')->__('Manage Campaigns');
        $this->_addButtonLabel = Mage::helper('mzax_emarketing')->__('New Campaign');

        parent::__construct();
    }
}
