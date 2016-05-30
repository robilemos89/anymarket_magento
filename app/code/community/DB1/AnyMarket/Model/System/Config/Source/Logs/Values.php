<?php

class DB1_AnyMarket_Model_System_Config_Source_Logs_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
        $retornArray[] = array( 'label' => 'Não Logar', 'value' => '0' );
        $retornArray[] = array( 'label' => 'Baixo', 'value' => '1' );
        $retornArray[] = array( 'label' => 'Alto', 'value' => '2' );

        return $retornArray;
    }
}