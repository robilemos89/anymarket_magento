<?php

class DB1_AnyMarket_Block_System_Config_Source_Products_Values  extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $magentoAttributes;

//    public function _prepareToRender()
    public function __construct()
    {
        $this->addColumn('descProduct', array(
            'label' => Mage::helper('adminhtml')->__('Description Product'),
            'size'  => 28
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new Field');
        
        parent::__construct();
        $this->setTemplate('db1/anymarket/system/config/form/field/array_dropdown.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        $rendered = '<select name="'.$inputName.'">';
        if ($columnName == 'descProduct') {
            $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');

            foreach ($productAttrs as $productAttr) {
                if($productAttr->getFrontendLabel() != null){
                    $attrCheck =  Mage::getModel('db1_anymarket/anymarketattributes')->load($productAttr->getAttributeCode(), 'nma_id_attr');

                    if($attrCheck->getData('nma_id_attr') == null){
                        $descAttr = $productAttr->getFrontendLabel();
                        if($descAttr != ''){
                            $descAttr = str_replace("'", "", $descAttr);
                            $rendered .= '<option value="'.$productAttr->getAttributeCode().'">'.$descAttr.' ('.$productAttr->getAttributeCode().')</option>';
                        }
                    }
                }

            }
        }
        $rendered .= '</select>';

        return $rendered;
    }

}