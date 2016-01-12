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
 * AnyMarket Log edit form tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketlog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketlog_Edit_Tab_Form
     * 
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('anymarketlog_');
        $form->setFieldNameSuffix('anymarketlog');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketlog_form',
            array('legend' => Mage::helper('db1_anymarket')->__('AnyMarket Log'))
        );

        $fieldset->addField(
            'created_at',
            'text',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Created At'),
                'name'  => 'created_at',
            'readonly' => true,
           )
        );

        $fieldset->addField(
            'log_desc',
            'textarea',
            array(
                'label' => Mage::helper('db1_anymarket')->__('Log Description'),
                'name'  => 'log_desc',
            'readonly' => true,
            'style'   => "width: 600px",
           )
        );

        $fieldset->addField(
            'log_json',
            'textarea',
            array(
                'label' => Mage::helper('db1_anymarket')->__('JSON'),
                'name'  => 'log_json',
            'readonly' => true,
            'style'   => "width: 600px",
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
            Mage::registry('current_anymarketlog')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_anymarketlog')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAnymarketlogData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAnymarketlogData());
            Mage::getSingleton('adminhtml/session')->setAnymarketlogData(null);
        } elseif (Mage::registry('current_anymarketlog')) {
            $formValues = array_merge($formValues, Mage::registry('current_anymarketlog')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
