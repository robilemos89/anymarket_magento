<?php

class DB1_AnyMarket_Model_System_Config_Source_Orders_Values
{
    public function toOptionArray()
    {
        $retornArray = array();
//        $retornArray[] = array( 'label' => 'Qualquer Status', 'value' => 'ANY' );
        $retornArray[] = array( 'label' => 'Concluido (CONCLUDED)', 'value' => 'CONCLUDED' );
        $retornArray[] = array( 'label' => 'Pendente (PENDING)', 'value' => 'PENDING' );
        $retornArray[] = array( 'label' => 'Faturado (INVOICED)', 'value' => 'INVOICED' );
        $retornArray[] = array( 'label' => 'Enviado (PAID_WAITING_DELIVERY)', 'value' => 'PAID_WAITING_DELIVERY' );
        $retornArray[] = array( 'label' => 'Pago (PAID_WAITING_SHIP)', 'value' => 'PAID_WAITING_SHIP' );
        $retornArray[] = array( 'label' => 'Cancelado (CANCELED)', 'value' => 'CANCELED' );

        return $retornArray;
    }
}