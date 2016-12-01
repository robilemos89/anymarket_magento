<?php
/**
 * DB1_AnyMarket extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       DB1
 * @package        DB1_AnyMarket
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * AnyMarket Log collection resource model
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Model_Resource_Anymarketimage_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joinedFields = array();

    /**
     * constructor
     *
     * @access public
     * @return void
     * 
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('db1_anymarket/anymarketimage');
    }

    /**
     * Add filter by store
     *
     * @access public
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return DB1_AnyMarket_Model_Resource_Anymarketimage_Collection
     * 
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Resource_Anymarketimage_Collection
     * 
     */
    protected function _renderFiltersBefore()
    {
        return parent::_renderFiltersBefore();
    }

    /**
     * get anymarket log as array
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     * 
     */
    protected function _toOptionArray($valueField='entity_id', $labelField='id_image', $additional=array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * get options hash
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @return array
     * 
     */
    protected function _toOptionHash($valueField='entity_id', $labelField='id_image')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @access public
     * @return Varien_Db_Select
     * 
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
}
