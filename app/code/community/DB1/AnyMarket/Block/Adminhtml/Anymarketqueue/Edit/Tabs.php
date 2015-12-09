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
 * Anymarket Queue admin edit tabs
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketqueue_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('db1_anymarket')->__('Anymarket Queue'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Edit_Tabs
     
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_anymarketqueue',
            array(
                'label'   => Mage::helper('db1_anymarket')->__('Anymarket Queue'),
                'title'   => Mage::helper('db1_anymarket')->__('Anymarket Queue'),
                'content' => $this->getLayout()->createBlock(
                    'db1_anymarket/adminhtml_anymarketqueue_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_anymarketqueue',
                array(
                    'label'   => Mage::helper('db1_anymarket')->__('Store views'),
                    'title'   => Mage::helper('db1_anymarket')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'db1_anymarket/adminhtml_anymarketqueue_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve anymarket queue entity
     *
     * @access public
     * @return DB1_AnyMarket_Model_Anymarketqueue
     
     */
    public function getAnymarketqueue()
    {
        return Mage::registry('current_anymarketqueue');
    }
}
