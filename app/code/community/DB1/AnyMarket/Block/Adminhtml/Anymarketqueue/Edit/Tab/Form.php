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
 * Anymarket Queue edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Edit_Tab_Form
     
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketqueue_');
        $form->setFieldNameSuffix('anymarketqueue');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketqueue_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Anymarket Queue'))
        );

        $fieldset->addField(
            'nmq_id',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Item code that is waiting in the queue'),
                'name'  => 'nmq_id',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nmq_type',
            'select',
            array(
                'label'  => Mage::helper('db1_anymarket')->__('Type of Operation'),
                'name'   => 'nmq_type',
                'required'  => true,
                'values' => array(
                    array(
                        'value' => 'IMP',
                        'label' => Mage::helper('db1_anymarket')->__('IMP'),
                    ),
                    array(
                        'value' => 'EXP',
                        'label' => Mage::helper('db1_anymarket')->__('EXP'),
                    ),
                ),
            )
        );

        $fieldset->addField(
            'nmq_table',
            'select',
            array(
                'label'  => Mage::helper('db1_anymarket')->__('Source table'),
                'name'   => 'nmq_table',
                'required'  => true,
                'values' => array(
                    array(
                        'value' => 'ORDER',
                        'label' => Mage::helper('db1_anymarket')->__('Order'),
                    ),
                    array(
                        'value' => 'PRODUCT',
                        'label' => Mage::helper('db1_anymarket')->__('Product'),
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
            Mage::registry('current_anymarketqueue')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketqueue')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketqueueData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketqueueData());
            Mage::getSingleton('adminhtml/session')->setAnymarketqueueData(null);
        } elseif (Mage::registry('current_anymarketqueue')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketqueue')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
