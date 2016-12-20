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
 * Class Mzax_Emarketing_Model_Object_Address
 */
abstract class Mzax_Emarketing_Model_Object_Address extends Mzax_Emarketing_Model_Object_Abstract
{
    /**
     * @return Mzax_Emarketing_Db_Select
     */
    public function getQuery()
    {
        $query = parent::getQuery();
        $query->addBinding('region', 'region');
        $query->addBinding('country_id', 'country_id');
        $query->addBinding('postcode', 'postcode');
        $query->addBinding('street', 'street');
        $query->addBinding('city', 'city');
        $query->addBinding('telephone', 'telephone');
        $query->addBinding('company', 'company');
        $query->addBinding('firstname', 'firstname');
        $query->addBinding('lastname', 'lastname');
        $query->addBinding('email', 'email');

        return $query;
    }

    /**
     * @param string $id
     *
     * @return null
     */
    public function getAdminUrl($id)
    {
        return null;
    }

    /**
     * @param Mzax_Emarketing_Model_Object_Collection $collection
     *
     * @return void
     */
    public function prepareCollection(Mzax_Emarketing_Model_Object_Collection $collection)
    {
        parent::prepareCollection($collection);

        $adapter = $this->getResourceHelper()->getAdapter();

        $nameExpr[] = "LTRIM(RTRIM({firstname}))";
        $nameExpr[] = "LTRIM(RTRIM({lastname}))";
        $nameExpr = $adapter->getConcatSql($nameExpr, ' ');

        $collection->addField('name', $nameExpr);
        $collection->addField('email', 'email');
        $collection->addField('city', 'city');
        $collection->addField('postcode', 'postcode');
    }

    /**
     * @param Mzax_Emarketing_Block_Filter_Object_Grid $grid
     *
     * @return void
     */
    public function prepareGridColumns(Mzax_Emarketing_Block_Filter_Object_Grid $grid)
    {
        $grid->addColumn('name', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Name'),
            'index'     => 'name'
        ));
        $grid->addColumn('email', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Email'),
            'index'     => 'email',
        ));
        $grid->addColumn('city', array(
            'header'    => Mage::helper('mzax_emarketing')->__('City'),
            'index'     => 'city',
        ));
        $grid->addColumn('postcode', array(
            'header'    => Mage::helper('mzax_emarketing')->__('Postcode'),
            'index'     => 'postcode',
        ));
    }
}
