<?php

class DB1_AnyMarket_Block_System_Config_Source_Products_Variations_Values  extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $magentoAttributes;

    public function __construct()
    {
        $this->addColumn('attrMGVariation', array(
            'label' => Mage::helper('adminhtml')->__('Atributo Magento'),
            'size' => 28
        ));

        $this->addColumn('variationTypeAnymarket', array(
            'label' => Mage::helper('adminhtml')->__('Variação Anymarket'),
            'size' => 28,
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new Variation');

        parent::__construct();
        $this->setTemplate('db1/anymarket/system/config/form/field/array_dropdown.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        $rendered = '<select name="' . $inputName . '">';
        if ($columnName == 'attrMGVariation') {
            $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');

            foreach ($productAttrs as $productAttr) {
                $descAttr = $productAttr->getFrontendLabel();
                if($descAttr != ''){
                    $descAttr = str_replace("'", "", $descAttr);
                    $rendered .= '<option value="'.$productAttr->getAttributeCode().'">'.$descAttr.' ('.$productAttr->getAttributeCode().')</option>';
                }
            }

        } else {
            $code = Mage::getSingleton('adminhtml/config_data')->getStore();
            $storeId = Mage::getModel('core/store')->load($code)->getId();

            $customVariation = Mage::helper('db1_anymarket/variation')->getVariations($storeId);
            foreach ($customVariation as $variation) {
                $rendered .= '<option value="'.$variation['name'].'">'.$variation['name'].'</option>';
            }
        }

        $rendered .= '</select>';
        return $rendered;
    }
}
