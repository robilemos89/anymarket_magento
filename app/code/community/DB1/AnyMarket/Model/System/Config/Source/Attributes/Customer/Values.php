<?php

class DB1_AnyMarket_Model_System_Config_Source_Attributes_Customer_Values
{
    public function toOptionArray()
    {
        $attributes = Mage::getModel('customer/customer')->getAttributes();
        $retornArray = array();
        foreach ($attributes as $attribute) {
            if($attribute->getStoreLabel() != ''){
                $retornArray[] = array( 'value' => $attribute->getData('attribute_code'), 'label' => $attribute->getStoreLabel() );
            }
        }

        return $retornArray;
    }
}