<?php

class DB1_AnyMarket_Block_System_Config_Source_Orders_Statusmgam_Values  extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $magentoAttributes;

//    public function _prepareToRender()
    public function __construct()
    {
        $this->addColumn('orderStatusMG', array(
            'label' => Mage::helper('adminhtml')->__('Status Order Magento'),
            'size'  => 28,
        ));
        $this->addColumn('orderStatusAM', array(
            'label' => Mage::helper('adminhtml')->__('Status Order Anymarket'),
            'size'  => 28
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add new Status');
        
        parent::__construct();
        $this->setTemplate('db1/anymarket/system/config/form/field/array_dropdown.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        $rendered = '<select name="'.$inputName.'">';
        if ($columnName == 'orderStatusAM') {
            $rendered .= '<option value="CONCLUDED">Concluido (CONCLUDED)</option>';
            $rendered .= '<option value="PENDING">Pendente (PENDING)</option>';
            $rendered .= '<option value="INVOICED">Faturado (INVOICED)</option>';
            $rendered .= '<option value="PAID_WAITING_DELIVERY">Enviado (PAID_WAITING_DELIVERY)</option>';
            $rendered .= '<option value="PAID_WAITING_SHIP">Pago (PAID_WAITING_SHIP)</option>';
            $rendered .= '<option value="CANCELED">Cancelado (CANCELED)</option>';
        } else {
            $orderStatusCollection = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
            foreach($orderStatusCollection as $orderStatus) {
                    $rendered .= '<option value="'.$orderStatus['status'].'">'.$orderStatus['label'].' ('.$orderStatus['status'].')</option>';
            }
            $rendered .= '<option value="new">New (new)</option>';
        }
        $rendered .= '</select>';

        return $rendered;
    }

}