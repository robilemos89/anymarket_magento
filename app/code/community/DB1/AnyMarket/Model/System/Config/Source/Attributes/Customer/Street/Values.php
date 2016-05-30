<?php

class DB1_AnyMarket_Model_System_Config_Source_Attributes_Customer_Street_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
        $retornArray[] = array( 'label' => 'Rua', 'value' => 'street' );
        $retornArray[] = array( 'label' => 'Número', 'value' => 'number' );
        $retornArray[] = array( 'label' => 'Bairro', 'value' => 'neighborhood' );
        $retornArray[] = array( 'label' => 'Complemento', 'value' => 'comment' );

        return $retornArray;
    }
}