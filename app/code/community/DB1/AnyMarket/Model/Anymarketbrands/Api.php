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
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
class DB1_AnyMarket_Model_Anymarketbrands_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarketbrands
     *
     * @access protected
     * @param $anymarketbrandsId
     * @return DB1_AnyMarket_Model_Anymarketbrands
    
     */
    protected function _initAnymarketbrands($anymarketbrandsId)
    {
        $anymarketbrands = Mage::getModel('db1_anymarket/anymarketbrands')->load($anymarketbrandsId);
        if (!$anymarketbrands->getId()) {
            $this->_fault('anymarketbrands_not_exists');
        }
        return $anymarketbrands;
    }

    /**
     * get anymarketbrand
     *
     * @access public
     * @param mixed $filters
     * @return array
     
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketbrands')->getCollection();
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
        foreach ($collection as $anymarketbrands) {
            $result[] = $this->_getApiData($anymarketbrands);
        }
        return $result;
    }

    /**
     * Add anymarketbrands
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
            $anymarketbrands = Mage::getModel('db1_anymarket/anymarketbrands')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketbrands->getId();
    }

    /**
     * Change existing anymarketbrands information
     *
     * @access public
     * @param int $anymarketbrandsId
     * @param array $data
     * @return bool
     
     */
    public function update($anymarketbrandsId, $data)
    {
        $anymarketbrands = $this->_initAnymarketbrands($anymarketbrandsId);
        try {
            $data = (array)$data;
            $anymarketbrands->addData($data);
            $anymarketbrands->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarketbrands
     *
     * @access public
     * @param int $anymarketbrandsId
     * @return bool
     
     */
    public function remove($anymarketbrandsId)
    {
        $anymarketbrands = $this->_initAnymarketbrands($anymarketbrandsId);
        try {
            $anymarketbrands->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketbrandsId
     * @return array
     
     */
    public function info($anymarketbrandsId)
    {
        $result = array();
        $anymarketbrands = $this->_initAnymarketbrands($anymarketbrandsId);
        $result = $this->_getApiData($anymarketbrands);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketbrands $anymarketbrands
     * @return array()
     
     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketbrands $anymarketbrands)
    {
        return $anymarketbrands->getData();
    }
}
