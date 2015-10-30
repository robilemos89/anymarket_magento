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
 * Anymarket Orders edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Edit_Tab_Form

     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketorders_');
        $form->setFieldNameSuffix('anymarketorders');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketorders_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Anymarket Orders'))
        );

        $fieldset->addField(
            'nmo_id_anymarket',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Code Anymarket'),
                'name'  => 'nmo_id_anymarket',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nmo_id_order',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Code Order Magento'),
                'name'  => 'nmo_id_order',
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
            Mage::registry('current_anymarketorders')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketorders')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketordersData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketordersData());
            Mage::getSingleton('adminhtml/session')->setAnymarketordersData(null);
        } elseif (Mage::registry('current_anymarketorders')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketorders')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
