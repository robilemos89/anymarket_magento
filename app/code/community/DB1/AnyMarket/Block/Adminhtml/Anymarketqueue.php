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
 * Anymarket Queue admin block
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketqueue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_anymarketqueue';
        $this->_blockGroup         = 'db1_anymarket';
        parent::__construct();
        $this->_headerText         = Mage::helper('db1_anymarket')->__('Anymarket Queue');
        $this->_removeButton('add');

    }
}
