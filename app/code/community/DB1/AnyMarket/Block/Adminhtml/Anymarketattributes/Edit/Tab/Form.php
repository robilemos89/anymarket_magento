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
 * Anymarket Attributes edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Edit_Tab_Form
     
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketattributes_');
        $form->setFieldNameSuffix('anymarketattributes');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketattributes_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Anymarket Attributes'))
        );

        $fieldset->addField(
            'nma_id_attr',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Attribute Code'),
                'name'  => 'nma_id_attr',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'nma_desc',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Attribute Description'),
                'name'  => 'nma_desc',
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
            Mage::registry('current_anymarketattributes')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketattributes')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketattributesData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketattributesData());
            Mage::getSingleton('adminhtml/session')->setAnymarketattributesData(null);
        } elseif (Mage::registry('current_anymarketattributes')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketattributes')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
