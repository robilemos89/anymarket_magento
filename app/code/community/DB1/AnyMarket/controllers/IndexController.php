<?php

// /anymarketcallback/index/sinc
class DB1_AnyMarket_IndexController extends Mage_Core_Controller_Front_Action {        
    public function sincAction() {
		$value = json_decode(file_get_contents('php://input'));

		if( $value && isset($value->type) ){
			$anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
			$anymarketlog->setLogDesc( 'Callback received');
			$anymarketlog->setLogJson(file_get_contents('php://input'));
			$anymarketlog->setStatus("1");
			$anymarketlog->save();

			$allStores = Mage::helper('db1_anymarket')->getTokenByOi( $value->content->oi );
			if( !empty($allStores) ) {
				foreach ($allStores as $store) {
					$storeID = $store['storeID'];
					$TOKEN = $store['token'];

					if ($TOKEN != '') {
						if ($value->type == 'ORDER') {
							$cache = Mage::app()->getCache();
							sleep(1);

							$lastProcOrder = $cache->load('order_' . $value->content->id);
							$lastProc = true;
							if ($lastProcOrder != null) {
								$dateOld = new DateTime(date('y-m-d H:i:s', strtotime($lastProcOrder)));
								$dateNew = new DateTime(date('y-m-d H:i:s'));

								$difDate = $dateOld->diff($dateNew);
								if ($difDate->h <= 0 && $difDate->i <= 0 && $difDate->s <= 20) {
									$lastProc = false;
								}
							}

							if ($lastProc) {
								$cache->save(date('y-m-d H:i:s'), 'order_' . $value->content->id, array($value->content->id), 60 * 60);

								Mage::helper('db1_anymarket/order')->getSpecificOrderFromAnyMarket($value->content->id, "notoken", $storeID);
							}
						} elseif ($value->type == 'TRANSMISSION') {
							$listTransmissions = array();

							$HOST = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);

							$headers = array(
								"Content-type: application/json",
								"Accept: */*",
								"gumgaToken: " . $TOKEN
							);

							array_push($listTransmissions, array(
									"id" => $value->content->id,
									"token" => "notoken"
								)
							);

							$JSON = json_encode($listTransmissions);
							$transRet = Mage::helper('db1_anymarket/product')->getSpecificFeedProduct(json_decode($JSON), $headers, $HOST, $storeID);

							echo $transRet;
						}
					} else {
						$anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
						$anymarketlog->setLogDesc('OI nao encontrado');
						$anymarketlog->setLogJson($value->content->oi);
						$anymarketlog->setStatus("1");
						$anymarketlog->save();

						echo "OI NOT FOUND";
					}
				}
			}else{
				echo "OI NOT FOUND";
			}

		}

    }
}

?>