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
class DB1_AnyMarket_Model_Anymarketcategories_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarket categories
     *
     * @access protected
     * @param $anymarketcategoriesId
     * @return DB1_AnyMarket_Model_Anymarketcategories
    
     */
    protected function _initAnymarketcategories($anymarketcategoriesId)
    {
        $anymarketcategories = Mage::getModel('db1_anymarket/anymarketcategories')->load($anymarketcategoriesId);
        if (!$anymarketcategories->getId()) {
            $this->_fault('anymarketcategories_not_exists');
        }
        return $anymarketcategories;
    }

    /**
     * get anymarket categories
     *
     * @access public
     * @param mixed $filters
     * @return array
     
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketcategories')->getCollection();
        $apiHelper = Mage::helper('api');
        $filters = $apiHelper->parseFilters($filters);
        try {
            foreach ($filters as $field => $value) {
                $collection->addFieldToFilter($field, $value);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        $result = array();
        foreach ($collection as $anymarketcategories) {
            $result[] = $this->_getApiData($anymarketcategories);
        }
        return $result;
    }

    /**
     * Add anymarket categories
     *
     * @access public
     * @param array $data
     * @return array
     
     */
    public function add($data)
    {
        try {
            if (is_null($data)) {
                throw new Exception(Mage::helper('db1_anymarket')->__("Data cannot be null"));
            }
            $data = (array)$data;
            $anymarketcategories = Mage::getModel('db1_anymarket/anymarketcategories')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketcategories->getId();
    }

    /**
     * Change existing anymarket categories information
     *
     * @access public
     * @param int $anymarketcategoriesId
     * @param array $data
     * @return bool
     
     */
    public function update($anymarketcategoriesId, $data)
    {
        $anymarketcategories = $this->_initAnymarketcategories($anymarketcategoriesId);
        try {
            $data = (array)$data;
            $anymarketcategories->addData($data);
            $anymarketcategories->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarket categories
     *
     * @access public
     * @param int $anymarketcategoriesId
     * @return bool
     
     */
    public function remove($anymarketcategoriesId)
    {
        $anymarketcategories = $this->_initAnymarketcategories($anymarketcategoriesId);
        try {
            $anymarketcategories->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketcategoriesId
     * @return array
     
     */
    public function info($anymarketcategoriesId)
    {
        $result = array();
        $anymarketcategories = $this->_initAnymarketcategories($anymarketcategoriesId);
        $result = $this->_getApiData($anymarketcategories);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketcategories $anymarketcategories
     * @return array()
     
     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketcategories $anymarketcategories)
    {
        return $anymarketcategories->getData();
    }
}
