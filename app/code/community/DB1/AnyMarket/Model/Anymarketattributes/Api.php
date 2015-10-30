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
class DB1_AnyMarket_Model_Anymarketattributes_Api extends Mage_Api_Model_Resource_Abstract
{


    /**
     * init anymarket attributes
     *
     * @access protected
     * @param $anymarketattributesId
     * @return DB1_AnyMarket_Model_Anymarketattributes
    
     */
    protected function _initAnymarketattributes($anymarketattributesId)
    {
        $anymarketattributes = Mage::getModel('db1_anymarket/anymarketattributes')->load($anymarketattributesId);
        if (!$anymarketattributes->getId()) {
            $this->_fault('anymarketattributes_not_exists');
        }
        return $anymarketattributes;
    }

    /**
     * get anymarket attributes
     *
     * @access public
     * @param mixed $filters
     * @return array
     
     */
    public function items($filters = null)
    {
        $collection = Mage::getModel('db1_anymarket/anymarketattributes')->getCollection();
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
        foreach ($collection as $anymarketattributes) {
            $result[] = $this->_getApiData($anymarketattributes);
        }
        return $result;
    }

    /**
     * Add anymarket attributes
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
            $anymarketattributes = Mage::getModel('db1_anymarket/anymarketattributes')
                ->setData((array)$data)
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $anymarketattributes->getId();
    }

    /**
     * Change existing anymarket attributes information
     *
     * @access public
     * @param int $anymarketattributesId
     * @param array $data
     * @return bool
     
     */
    public function update($anymarketattributesId, $data)
    {
        $anymarketattributes = $this->_initAnymarketattributes($anymarketattributesId);
        try {
            $data = (array)$data;
            $anymarketattributes->addData($data);
            $anymarketattributes->save();
        }
        catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * remove anymarket attributes
     *
     * @access public
     * @param int $anymarketattributesId
     * @return bool
     
     */
    public function remove($anymarketattributesId)
    {
        $anymarketattributes = $this->_initAnymarketattributes($anymarketattributesId);
        try {
            $anymarketattributes->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }
        return true;
    }

    /**
     * get info
     *
     * @access public
     * @param int $anymarketattributesId
     * @return array
     
     */
    public function info($anymarketattributesId)
    {
        $result = array();
        $anymarketattributes = $this->_initAnymarketattributes($anymarketattributesId);
        $result = $this->_getApiData($anymarketattributes);
        return $result;
    }

    /**
     * get data for api
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Anymarketattributes $anymarketattributes
     * @return array()
     
     */
    protected function _getApiData(DB1_AnyMarket_Model_Anymarketattributes $anymarketattributes)
    {
        return $anymarketattributes->getData();
    }
}
