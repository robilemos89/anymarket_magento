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
 * Anymarket Products edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketproducts_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketproducts_Edit_Tab_Form
     * 
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketproducts_');
        $form->setFieldNameSuffix('anymarketproducts');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketproducts_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Anymarket Products'))
        );

        $fieldset->addField(
            'nmp_sku',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Product SKU'),
                'name'  => 'nmp_sku',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nmp_name',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Product Name'),
                'name'  => 'nmp_name',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nmp_desc_error',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Error Description'),
                'name'  => 'nmp_desc_error',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'nmp_status_int',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Integration status'),
                'name'  => 'nmp_status_int',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('db1_anymarket')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('db1_anymarket')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('db1_anymarket')->__('Disabled'),
                    ),
                ),
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
            Mage::registry('current_anymarketproducts')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketproducts')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketproductsData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketproductsData());
            Mage::getSingleton('adminhtml/session')->setAnymarketproductsData(null);
        } elseif (Mage::registry('current_anymarketproducts')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketproducts')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
