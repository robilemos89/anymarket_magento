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
 * Anymarket Orders model
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Model_Anymarketorders extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'db1_anymarket_anymarketorders';
    const CACHE_TAG = 'db1_anymarket_anymarketorders';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'db1_anymarket_anymarketorders';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'anymarketorders';

    /**
     * constructor
     *
     * @access public
     * @return void

     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('db1_anymarket/anymarketorders');
    }

    /**
     * before save anymarket orders
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketorders

     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save anymarket orders relation
     *
     * @access public
     * @return DB1_AnyMarket_Model_Anymarketorders

     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * get default values
     *
     * @access public
     * @return array

     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
