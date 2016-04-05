<?php

class DB1_AnyMarket_Model_System_Config_Source_Attributes_Set_Values
{
    public function toOptionArray()
    {
        $attributeSetCollection = Mage::getModel('catalog/product_attribute_set_api')->items();
        foreach ($attributeSetCollection as $attributeSet) {
            $retornArray[] = array( 'value' => $attributeSet['set_id'], 'label' => $attributeSet['name'] );
        }

        return $retornArray;
    }
}