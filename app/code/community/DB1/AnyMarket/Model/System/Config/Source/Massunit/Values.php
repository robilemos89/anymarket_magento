<?php

class DB1_AnyMarket_Model_System_Config_Source_Massunit_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
        $retornArray[] = array( 'label' => Mage::helper('db1_anymarket')->__('Quilogramas'), 'value' => '0' );
        $retornArray[] = array( 'label' => Mage::helper('db1_anymarket')->__('Gramas'), 'value' => '1' );

        return $retornArray;
    }
}