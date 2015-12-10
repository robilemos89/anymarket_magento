<?php

class DB1_AnyMarket_Model_System_Config_Source_Attributes_Customer_Group_Values
{
    public function toOptionArray()
    {
        $customerGroup = Mage::getModel('customer/group')->getCollection();
        $retornArray = array();
        foreach ($customerGroup as $group) {
            $retornArray[] = array( 'value' => $group->getData('customer_group_id'), 'label' => $group->getData('customer_group_code') );
        }

        return $retornArray;
    }
}