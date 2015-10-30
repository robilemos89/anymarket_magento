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
class DB1_AnyMarket_Model_Anymarketproducts_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarket products
     *
     * @access protected
     * @param $anymarketproductsId
     * @return DB1_AnyMarket_Model_Anymarketproducts
    
     */
    protected function _initAnymarketproducts($anymarketproductsId)
    {
        $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->load($anymarketproductsId);
        if (!$anymarketproducts->getId()) {
            $this->_fault('anymarketproducts_not_exists');
        }
        return $anymarketproducts;
    }

    /**
     * get anymarket products
     *
     * @access public
     * @param mixed $filters
     * @return array
     * 
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketproducts')->getCollection();
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
        foreach ($collection as $anymarketproducts) {
            $result[] = $this->_getApiData($anymarketproducts);
        }
        return $result;
    }

    /**
     * Add anymarket products
     *
     * @access public
     * @param array $data
     * @return array
     * 
     */
    public function add($data)
    {
        try {
            if (is_null($data)) {
                throw new Exception(Mage::helper('db1_anymarket')->__("Data cannot be null"));
            }
            $data = (array)$data;
            $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketproducts->getId();
    }

    /**
     * Change existing anymarket products information
     *
     * @access public
     * @param int $anymarketproductsId
     * @param array $data
     * @return bool
     * 
     */
    public function update($anymarketproductsId, $data)
    {
        $anymarketproducts = $this->_initAnymarketproducts($anymarketproductsId);
        try {
            $data = (array)$data;
            $anymarketproducts->addData($data);
            $anymarketproducts->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarket products
     *
     * @access public
     * @param int $anymarketproductsId
     * @return bool
     * 
     */
    public function remove($anymarketproductsId)
    {
        $anymarketproducts = $this->_initAnymarketproducts($anymarketproductsId);
        try {
            $anymarketproducts->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketproductsId
     * @return array
     * 
     */
    public function info($anymarketproductsId)
    {
        $result = array();
        $anymarketproducts = $this->_initAnymarketproducts($anymarketproductsId);
        $result = $this->_getApiData($anymarketproducts);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketproducts $anymarketproducts
     * @return array()
     * 
     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketproducts $anymarketproducts)
    {
        return $anymarketproducts->getData();
    }
}
