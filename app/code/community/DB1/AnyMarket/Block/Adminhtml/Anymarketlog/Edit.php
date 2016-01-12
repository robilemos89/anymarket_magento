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
 * AnyMarket Log admin edit form
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketlog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'db1_anymarket';
        $this->_controller = 'adminhtml_anymarketlog';
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * 
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_anymarketlog') && Mage::registry('current_anymarketlog')->getId()) {
            return Mage::helper('db1_anymarket')->__('Log of ID Order/Product: '.  $this->escapeHtml(Mage::registry('current_anymarketlog')->getLogId()));
        }
    }
}
