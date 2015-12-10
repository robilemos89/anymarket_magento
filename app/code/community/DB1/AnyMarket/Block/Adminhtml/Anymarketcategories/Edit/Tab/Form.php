<?php
/**
 * DB1_AnyMarket extension
 * 
 * 
 * @category       DB1
 * @package        DB1_AnyMarket
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Anymarket Categories edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Edit_Tab_Form
     
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketcategories_');
        $form->setFieldNameSuffix('anymarketcategories');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketcategories_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Anymarket Categories'))
        );

        $fieldset->addField(
            'nmc_cat_id',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Complete code Category'),
                'name'  => 'nmc_cat_id',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nmc_cat_root_id',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Code of predecessor category'),
                'name'  => 'nmc_cat_root_id',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nmc_cat_desc',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Category description'),
                'name'  => 'nmc_cat_desc',
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
            Mage::registry('current_anymarketcategories')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketcategories')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketcategoriesData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketcategoriesData());
            Mage::getSingleton('adminhtml/session')->setAnymarketcategoriesData(null);
        } elseif (Mage::registry('current_anymarketcategories')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketcategories')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
