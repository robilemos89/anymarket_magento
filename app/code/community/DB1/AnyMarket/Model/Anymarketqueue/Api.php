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
class DB1_AnyMarket_Model_Anymarketqueue_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarket queue
     *
     * @access protected
     * @param $anymarketqueueId
     * @return DB1_AnyMarket_Model_Anymarketqueue
    "
     */
    protected function _initAnymarketqueue($anymarketqueueId)
    {
        $anymarketqueue = Mage::getModel('db1_anymarket/anymarketqueue')->load($anymarketqueueId);
        if (!$anymarketqueue->getId()) {
            $this->_fault('anymarketqueue_not_exists');
        }
        return $anymarketqueue;
    }

    /**
     * get anymarket queues
     *
     * @access public
     * @param mixed $filters
     * @return array
     
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketqueue')->getCollection();
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
        foreach ($collection as $anymarketqueue) {
            $result[] = $this->_getApiData($anymarketqueue);
        }
        return $result;
    }

    /**
     * Add anymarket queue
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
            $anymarketqueue = Mage::getModel('db1_anymarket/anymarketqueue')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketqueue->getId();
    }

    /**
     * Change existing anymarket queue information
     *
     * @access public
     * @param int $anymarketqueueId
     * @param array $data
     * @return bool
     
     */
    public function update($anymarketqueueId, $data)
    {
        $anymarketqueue = $this->_initAnymarketqueue($anymarketqueueId);
        try {
            $data = (array)$data;
            $anymarketqueue->addData($data);
            $anymarketqueue->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarket queue
     *
     * @access public
     * @param int $anymarketqueueId
     * @return bool
     
     */
    public function remove($anymarketqueueId)
    {
        $anymarketqueue = $this->_initAnymarketqueue($anymarketqueueId);
        try {
            $anymarketqueue->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketqueueId
     * @return array
     
     */
    public function info($anymarketqueueId)
    {
        $result = array();
        $anymarketqueue = $this->_initAnymarketqueue($anymarketqueueId);
        $result = $this->_getApiData($anymarketqueue);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketqueue $anymarketqueue
     * @return array()
     
     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketqueue $anymarketqueue)
    {
        return $anymarketqueue->getData();
    }
}
