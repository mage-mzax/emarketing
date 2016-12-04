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
 * Class Mzax_Emarketing_Model_Object_Recipient
 */
class Mzax_Emarketing_Model_Object_Recipient extends Mzax_Emarketing_Model_Object_Abstract
{

    public function _construct()
    {
        $this->_init('mzax_emarketing/recipient');
    }




    public function getName()
    {
        return $this->__('Recipient');
    }





    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('recipient_id', 'recipient_id');
        $query->addBinding('recipient_sent_at', 'sent_at');
        $query->addBinding('campaign_id', 'campaign_id');
        $query->addBinding('variation_id', 'variation_id');

        return $query;
    }




    public function prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::prepareCollection($collection);

        $collection->addField('sent_at');
        $collection->addField('object_id');
        $collection->addField('variation_id');
        $collection->addField('campaign_id');
    }

    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        $grid->addColumn('sent_at', array(
            'header' => Mage::helper('sales')->__('Sent at'),
            'index'  => 'sent_at',
            'type'   => 'datetime',
            'width'  => '150px',
        ));

        $grid->addColumn('object_id', array(
            'header' => Mage::helper('sales')->__('Object ID'),
            'index'  => 'object_id',
            'type'   => 'number',
        ));

        $grid->addColumn('campaign_id', array(
            'header' => Mage::helper('sales')->__('Campaign ID'),
            'index'  => 'campaign_id',
            'type'   => 'number',
        ));

        $grid->addColumn('variation_id', array(
            'header' => Mage::helper('sales')->__('Variation ID'),
            'index'  => 'variation_id',
            'type'   => 'number',
        ));


    }


}
