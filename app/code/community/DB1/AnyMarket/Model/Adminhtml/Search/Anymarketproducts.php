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
 * Admin search model
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Model_Adminhtml_Search_Anymarketproducts extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return DB1_AnyMarket_Model_Adminhtml_Search_Anymarketproducts
     * 
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('db1_anymarket/anymarketproducts_collection')
            ->addFieldToFilter('nmp_status_int', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $anymarketproducts) {
            $arr[] = array(
                'id'          => 'anymarketproducts/1/'.$anymarketproducts->getId(),
                'type'        => Mage::helper('db1_anymarket')->__('Anymarket Products'),
                'name'        => $anymarketproducts->getNmpStatusInt(),
                'description' => $anymarketproducts->getNmpStatusInt(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/anymarket_anymarketproducts/edit',
                    array('id'=>$anymarketproducts->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
