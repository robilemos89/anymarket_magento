<?php

class DB1_AnyMarket_Model_System_Config_Source_TypeSync_Order_Values
{
    public function toOptionArray()
    {
       $retornArray[] = array( 'value' => 0, 'label' => 'Magento to AnyMarket');
       $retornArray[] = array( 'value' => 1, 'label' => 'AnyMarket to Magento');

        return $retornArray;
    }
}