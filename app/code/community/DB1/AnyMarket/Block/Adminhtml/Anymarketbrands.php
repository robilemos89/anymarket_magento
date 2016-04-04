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
 * Anymarketbrands admin block
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketbrands extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_anymarketbrands';
        $this->_blockGroup         = 'db1_anymarket';
        parent::__construct();
        $this->_headerText         = Mage::helper('db1_anymarket')->__('Anymarket Brands');
        //$this->_updateButton('add', 'label', Mage::helper('db1_anymarket')->__('Add Brands'));
        $this->_removeButton('add');

        $this->_addButton('sinc_brands', array(
            'label'   => Mage::helper('db1_anymarket')->__('Synchronize Brands'),
            'onclick' => "setLocation('{$this->getUrl('*/*/sincBrands')}')",
            'class'   => 'add'
        ));

    }
}
