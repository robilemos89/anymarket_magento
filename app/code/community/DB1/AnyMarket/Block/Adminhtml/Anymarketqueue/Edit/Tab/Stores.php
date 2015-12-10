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
 * store selection tab
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Edit_Tab_Stores
     
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('anymarketqueue');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'anymarketqueue_stores_form',
            array('legend' => Mage::helper('db1_anymarket')->__('Store views'))
        );
        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('db1_anymarket')->__('Store Views'),
                'title'    => Mage::helper('db1_anymarket')->__('Store Views'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('current_anymarketqueue')->getData());
        return parent::_prepareForm();
    }
}
