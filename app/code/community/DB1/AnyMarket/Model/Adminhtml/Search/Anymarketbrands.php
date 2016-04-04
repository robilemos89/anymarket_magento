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
/**
 * Admin search model
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Model_Adminhtml_Search_Anymarketbrands extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return DB1_AnyMarket_Model_Adminhtml_Search_Anymarketbrands
     
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('db1_anymarket/anymarketbrands_collection')
            ->addFieldToFilter('brd_id', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $anymarketbrands) {
            $arr[] = array(
                'id'          => 'anymarketbrands/1/'.$anymarketbrands->getId(),
                'type'        => Mage::helper('db1_anymarket')->__('Anymarketbrands'),
                'name'        => $anymarketbrands->getBrdId(),
                'description' => $anymarketbrands->getBrdId(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/anymarket_anymarketbrands/edit',
                    array('id'=>$anymarketbrands->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
