<?php

class DB1_AnyMarket_Model_System_Config_Source_General_Invoice_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
        $retornArray[] = array( 'label' => 'Estático', 'value' => '0' );
        $retornArray[] = array( 'label' => 'Dinâmico', 'value' => '1' );

        return $retornArray;
    }
}