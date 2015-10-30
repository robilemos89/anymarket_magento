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
class DB1_AnyMarket_Model_Anymarketorders_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarket orders
     *
     * @access protected
     * @param $anymarketordersId
     * @return DB1_AnyMarket_Model_Anymarketorders
    
     */
    protected function _initAnymarketorders($anymarketordersId)
    {
        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($anymarketordersId);
        if (!$anymarketorders->getId()) {
            $this->_fault('anymarketorders_not_exists');
        }
        return $anymarketorders;
    }

    /**
     * get anymarket orders
     *
     * @access public
     * @param mixed $filters
     * @return array

     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketorders')->getCollection();
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
        foreach ($collection as $anymarketorders) {
            $result[] = $this->_getApiData($anymarketorders);
        }
        return $result;
    }

    /**
     * Add anymarket orders
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
            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketorders->getId();
    }

    /**
     * Change existing anymarket orders information
     *
     * @access public
     * @param int $anymarketordersId
     * @param array $data
     * @return bool

     */
    public function update($anymarketordersId, $data)
    {
        $anymarketorders = $this->_initAnymarketorders($anymarketordersId);
        try {
            $data = (array)$data;
            $anymarketorders->addData($data);
            $anymarketorders->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarket orders
     *
     * @access public
     * @param int $anymarketordersId
     * @return bool

     */
    public function remove($anymarketordersId)
    {
        $anymarketorders = $this->_initAnymarketorders($anymarketordersId);
        try {
            $anymarketorders->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketordersId
     * @return array

     */
    public function info($anymarketordersId)
    {
        $result = array();
        $anymarketorders = $this->_initAnymarketorders($anymarketordersId);
        $result = $this->_getApiData($anymarketorders);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketorders $anymarketorders
     * @return array()

     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketorders $anymarketorders)
    {
        return $anymarketorders->getData();
    }
}
