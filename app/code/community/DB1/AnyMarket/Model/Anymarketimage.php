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
 * Anymarketbrands model
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Model_Anymarketimage extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'db1_anymarket_anymarketimage';
    const CACHE_TAG = 'db1_anymarket_anymarketimage';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'db1_anymarket_anymarketimage';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'anymarketimage';

    /**
     * constructor
     *
     * @access public
     * @return void
     
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('db1_anymarket/anymarketimage');
    }

    /**
     * before save anymarketimage
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketimage

     */
    protected function _beforeSave()
    {
        return parent::_beforeSave();
    }

    /**
     * save anymarketbrands relation
     *
     * @access public
     * @return DB1_AnyMarket_Model_Anymarketbrands
     
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }
    
}
