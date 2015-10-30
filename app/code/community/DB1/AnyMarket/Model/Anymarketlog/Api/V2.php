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
class DB1_AnyMarket_Model_Anymarketlog_Api_V2 extends DB1_AnyMarket_Model_Anymarketlog_Api
{
    /**
     * AnyMarket Log info
     *
     * @access public
     * @param int $anymarketlogId
     * @return object
     * 
     */
    public function info($anymarketlogId)
    {
        $result = parent::info($anymarketlogId);
        $result = Mage::helper('api')->wsiArrayPacker($result);
        return $result;
    }
}
