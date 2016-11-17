<?php

class DB1_Anymarket_Adminhtml_AnymarketController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Return some checking result
	 *
	 * @return void
	 */
	public function checkconfigAction()
	{
		$errors = array();
		$returnStr = "Configurações OK";
		$storeID = $_GET['store'];

		$HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
		if( $HOST == "" ){
			array_push($errors, " Host inválido.");
		}
		$TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);
		if( $TOKEN == "" ){
			array_push($errors, " Token inválido.");
		}
		$OI = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_oi_field', $storeID);
		if( $OI == "" ){
			array_push($errors, " OI inválido.");
		}

		$schedules_pending = Mage::getModel('cron/schedule')->getCollection()
			->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
			->load();
		$schedules_complete = Mage::getModel('cron/schedule')->getCollection()
			->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_SUCCESS)
			->load();

		if (sizeof($schedules_pending) == 0 ||
			sizeof($schedules_complete) == 0) {
			array_push($errors, " CRON Provavelmente desabilitado.");
		}

		$ConfigDescProd = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_desc_field', $storeID);
		if ($ConfigDescProd == 'a:0:{}') {
			array_push($errors, " Configuração de Descrição Inválida.");
		}
		$StatusOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_mg_am_field', $storeID);
		if ($StatusOrder == 'a:0:{}') {
			array_push($errors, " Não ha nada configurado em Status Magento-Anymarket.");
		}
		$StatusOrderAM = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_am_mg_field', $storeID);
		if ($StatusOrderAM == 'a:0:{}') {
			array_push($errors, " Não ha nada configurado em Status Anymarket-Magento.");
		}
		$categsAM = Mage::getModel('db1_anymarket/anymarketcategories')->getCollection();
		if( $categsAM == null ){
			array_push($errors, " Tabela Categoria Anymarket não encontrada.");
		}elseif( sizeof($categsAM) == 0 ){
			array_push($errors, " Categorias não sincronizadas.");
		}
		$categsBRAND = Mage::getModel('db1_anymarket/anymarketbrands')->getCollection();
		if( $categsBRAND == null ){
			array_push($errors, " Tabela Brand Anymarket não encontrada.");
		}elseif( sizeof($categsBRAND) == 0 ){
			array_push($errors, " Marcas não sincronizadas.");
		}

		if( count($errors) > 0 ){
			$returnStr = "Ha inconsistências nas configurações: \n";
			$returnStr .= implode("\n",$errors);
		}

		Mage::app()->getResponse()->setBody($returnStr);
	}


}