<?php

class DB1_AnyMarket_Model_System_Config_Source_General_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
        $retornArray[] = array( 'label' => 'Imediato', 'value' => '0' );
        $retornArray[] = array( 'label' => 'Fila Integração', 'value' => '1' );

        return $retornArray;
    }
}