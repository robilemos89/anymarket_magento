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
class DB1_AnyMarket_Model_Adminhtml_Search_Anymarketattributes extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return DB1_AnyMarket_Model_Adminhtml_Search_Anymarketattributes
     
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('db1_anymarket/anymarketattributes_collection')
            ->addFieldToFilter('nma_desc', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $anymarketattributes) {
            $arr[] = array(
                'id'          => 'anymarketattributes/1/'.$anymarketattributes->getId(),
                'type'        => Mage::helper('db1_anymarket')->__('Anymarket Attributes'),
                'name'        => $anymarketattributes->getNmaDesc(),
                'description' => $anymarketattributes->getNmaDesc(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/anymarket_anymarketattributes/edit',
                    array('id'=>$anymarketattributes->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
