<?php

class DB1_AnyMarket_Model_System_Config_Source_Measurementunit_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
        $retornArray[] = array( 'label' => Mage::helper('db1_anymarket')->__('Centímetro'), 'value' => '0' );
        $retornArray[] = array( 'label' => Mage::helper('db1_anymarket')->__('Metro'), 'value' => '1' );
        $retornArray[] = array( 'label' => Mage::helper('db1_anymarket')->__('Decímetro'), 'value' => '2' );
        $retornArray[] = array( 'label' => Mage::helper('db1_anymarket')->__('Milímetro'), 'value' => '3' );

        return $retornArray;
    }
}