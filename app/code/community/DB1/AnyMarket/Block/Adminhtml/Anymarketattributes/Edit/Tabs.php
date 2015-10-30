<?php
/**
 * DB1_AnyMarket extension
 * 
 * @category       DB1
 * @package        DB1_AnyMarket
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Anymarket Attributes admin edit tabs
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketattributes_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('db1_anymarket')->__('Anymarket Attributes'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Edit_Tabs
     
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_anymarketattributes',
            array(
                'label'   => Mage::helper('db1_anymarket')->__('Anymarket Attributes'),
                'title'   => Mage::helper('db1_anymarket')->__('Anymarket Attributes'),
                'content' => $this->getLayout()->createBlock(
                    'db1_anymarket/adminhtml_anymarketattributes_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_anymarketattributes',
                array(
                    'label'   => Mage::helper('db1_anymarket')->__('Store views'),
                    'title'   => Mage::helper('db1_anymarket')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'db1_anymarket/adminhtml_anymarketattributes_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve anymarket attributes entity
     *
     * @access public
     * @return DB1_AnyMarket_Model_Anymarketattributes
     
     */
    public function getAnymarketattributes()
    {
        return Mage::registry('current_anymarketattributes');
    }
}
