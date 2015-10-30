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
class DB1_AnyMarket_Model_Anymarketlog_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarket log
     *
     * @access protected
     * @param $anymarketlogId
     * @return DB1_AnyMarket_Model_Anymarketlog
    
     */
    protected function _initAnymarketlog($anymarketlogId)
    {
        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog')->load($anymarketlogId);
        if (!$anymarketlog->getId()) {
            $this->_fault('anymarketlog_not_exists');
        }
        return $anymarketlog;
    }

    /**
     * get anymarket log
     *
     * @access public
     * @param mixed $filters
     * @return array
     * 
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketlog')->getCollection();
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
        foreach ($collection as $anymarketlog) {
            $result[] = $this->_getApiData($anymarketlog);
        }
        return $result;
    }

    /**
     * Add anymarket log
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
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketlog->getId();
    }

    /**
     * Change existing anymarket log information
     *
     * @access public
     * @param int $anymarketlogId
     * @param array $data
     * @return bool
     * 
     */
    public function update($anymarketlogId, $data)
    {
        $anymarketlog = $this->_initAnymarketlog($anymarketlogId);
        try {
            $data = (array)$data;
            $anymarketlog->addData($data);
            $anymarketlog->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarket log
     *
     * @access public
     * @param int $anymarketlogId
     * @return bool
     * 
     */
    public function remove($anymarketlogId)
    {
        $anymarketlog = $this->_initAnymarketlog($anymarketlogId);
        try {
            $anymarketlog->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketlogId
     * @return array
     * 
     */
    public function info($anymarketlogId)
    {
        $result = array();
        $anymarketlog = $this->_initAnymarketlog($anymarketlogId);
        $result = $this->_getApiData($anymarketlog);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketlog $anymarketlog
     * @return array()
     * 
     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketlog $anymarketlog)
    {
        return $anymarketlog->getData();
    }
}
