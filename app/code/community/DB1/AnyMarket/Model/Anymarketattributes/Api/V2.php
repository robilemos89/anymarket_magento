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
class DB1_AnyMarket_Model_Anymarketattributes_Api_V2 extends DB1_AnyMarket_Model_Anymarketattributes_Api
{
    /**
     * Anymarket Attributes info
     *
     * @access public
     * @param int $anymarketattributesId
     * @return object
     
     */
    public function info($anymarketattributesId)
    {
        $result = parent::info($anymarketattributesId);
        $result = Mage::helper('api')->wsiArrayPacker($result);
        return $result;
    }
}
