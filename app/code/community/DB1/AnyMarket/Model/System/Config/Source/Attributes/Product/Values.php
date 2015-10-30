<?php

class DB1_AnyMarket_Model_System_Config_Source_Attributes_Product_Values
{
    public function toOptionArray()
    {
        $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
        $retornArray = array();
        foreach ($productAttrs as $productAttr) {
            if($productAttr->getFrontendLabel() != null){
                $attrCheck =  Mage::getModel('db1_anymarket/anymarketattributes')->load($productAttr->getAttributeCode(), 'nma_id_attr');

                if($attrCheck->getData('nma_id_attr') == null){
                    $retornArray[] = array( 'value' => $productAttr->getAttributeCode(), 'label' => $productAttr->getFrontendLabel() );
                }
            }
        }

        return $retornArray;
    }
}