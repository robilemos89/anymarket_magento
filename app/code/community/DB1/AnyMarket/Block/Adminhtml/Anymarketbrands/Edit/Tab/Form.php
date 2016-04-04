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
 * Anymarketbrands edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Edit_Tab_Form
     
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketbrands_');
        $form->setFieldNameSuffix('anymarketbrands');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketbrands_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Brand'))
        );

        $fieldset->addField(
            'brd_id',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Brand Code'),
                'name'  => 'brd_id',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'brd_name',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Brand Description'),
                'name'  => 'brd_name',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_anymarketbrands')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketbrands')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketbrandsData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketbrandsData());
            Mage::getSingleton('adminhtml/session')->setAnymarketbrandsData(null);
        } elseif (Mage::registry('current_anymarketbrands')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketbrands')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
