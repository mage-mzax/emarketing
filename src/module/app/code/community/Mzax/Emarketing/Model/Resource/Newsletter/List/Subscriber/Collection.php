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
 * Class Mzax_Emarketing_Model_Resource_Newsletter_List_Subscriber_Collection
 */
class Mzax_Emarketing_Model_Resource_Newsletter_List_Subscriber_Collection
    extends Mage_Newsletter_Model_Resource_Subscriber_Collection
{
    /**
     * @var Mzax_Emarketing_Model_Newsletter_List
     */
    protected $_list;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_map['fields']['list_id']         = 'list.list_id';
        $this->_map['fields']['list_changed_at'] = 'list.changed_at';
        $this->_map['fields']['list_status']     = $this->getResource()->getReadConnection()
            ->getIfNullSql('list.list_status', Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE);
    }

    /**
     * Set newsletter list
     *
     * @param Mzax_Emarketing_Model_Newsletter_List $list
     * @return $this
     */
    public function setList(Mzax_Emarketing_Model_Newsletter_List $list)
    {
        $this->_list = $list;
        $this->addBindParam('list_id', $this->_list->getId());
        return $this;
    }

    /**
     * Init collection select
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(array('list' => $this->getTable('mzax_emarketing/newsletter_list_subscriber')),
                '`main_table`.`subscriber_id` = `list`.`subscriber_id` AND `list`.`list_id` = :list_id', null)
            ->columns(array(
                'list_status' => new Zend_Db_Expr($this->_getMappedField('list_status')),
                'list_changed_at' => 'list.changed_at'
            ));

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function bindListId()
    {
        if (!$this->_list) {
            throw new Exception("No newsletter list defined");
        }

        if (!$this->_list->getId()) {
            throw new Exception("No valid newsletter list defined");
        }

        $this->addBindParam('list_id', $this->_list->getId());
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSelectCountSql()
    {
        $this->bindListId();
        return parent::getSelectCountSql();
    }

    /**
     * Retrieve all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $this->bindListId();

        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * @param Varien_Db_Select $select
     *
     * @return string
     * @throws Exception
     */
    protected function _prepareSelect(Varien_Db_Select $select)
    {
        $this->bindListId();
        return parent::_prepareSelect($select);

    }

    /**
     * @return void
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $item->setList($this->_list);
        }
    }
}
