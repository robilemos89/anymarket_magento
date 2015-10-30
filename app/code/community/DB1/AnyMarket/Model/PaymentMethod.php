<?php
class DB1_AnyMarket_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'db1_anymarket';
  protected $_isInitializeNeeded = true;
  protected $_canUseInternal = true;
  protected $_canUseForMultishipping = false;
  protected $_apiToken = null;
 
  public function assignData($data)
  {
    $info = $this->getInfoInstance();
     
    if ($data->getCustomFieldOne())
    {
      $info->setCustomFieldOne($data->getCustomFieldOne());
    }
     
    if ($data->getCustomFieldTwo())
    {
      $info->setCustomFieldTwo($data->getCustomFieldTwo());
    }
 
    return $this;
  }
 
  public function validate()
  {
    parent::validate();
    $info = $this->getInfoInstance();

    return $this;
  }
 
  public function getOrderPlaceRedirectUrl()
  {
    return Mage::getUrl('db1_anymarket/payment/redirect', array('_secure' => false));
  }
}