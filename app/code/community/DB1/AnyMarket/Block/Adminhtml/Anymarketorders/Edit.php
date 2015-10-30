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
 * Anymarket Orders admin edit form
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void

     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'db1_anymarket';
        $this->_controller = 'adminhtml_anymarketorders';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('db1_anymarket')->__('Save Anymarket Orders')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('db1_anymarket')->__('Delete Anymarket Orders')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('db1_anymarket')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string

     */
    public function getHeaderText()
    {
        if (Mage::registry('current_anymarketorders') && Mage::registry('current_anymarketorders')->getId()) {
            return Mage::helper('db1_anymarket')->__(
                "Edit Anymarket Orders '%s'",
                $this->escapeHtml(Mage::registry('current_anymarketorders')->getNmoIdOrder())
            );
        } else {
            return Mage::helper('db1_anymarket')->__('Add Anymarket Orders');
        }
    }
}
