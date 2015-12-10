<?php
class DB1_AnyMarket_Model_Cron{

	public function sincOrders(){
        Mage::getSingleton('core/session')->setImportOrdersVariable('false');

        $allStores = Mage::helper('db1_anymarket')->getAllStores();
        foreach ($allStores as $store) {
            $storeID = $store['store_id'];
            Mage::app()->setCurrentStore($storeID);

            $arrayofOrders = array();
            $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
            if($ConfigOrder == 1){
                Mage::helper('db1_anymarket/order')->getFeedOrdersFromAnyMarket();
            }else{
                $orders =  Mage::getModel('sales/order')->getCollection();
                foreach($orders as $order) {
                    Mage::helper('db1_anymarket/order')->updateOrderAnyMarket($order);
                }
            }

        }
        Mage::getSingleton('core/session')->setImportOrdersVariable('true');

	} 

	public function sincProducts(){
        $allStores = Mage::helper('db1_anymarket')->getAllStores();
        foreach ($allStores as $store) {
            $storeID = $store['store_id'];
            Mage::app()->setCurrentStore($storeID);

            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
            if($typeSincProd == 1){
            	Mage::helper('db1_anymarket/product')->getFeedProdsFromAnyMarket();
        	}else{
                $products = Mage::getModel('catalog/product')->getCollection();
                foreach($products as $product) {

                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');
                    if($anymarketproducts->getData('nmp_id') != null){
                        if( strtolower($anymarketproducts->getData('nmp_status_int')) != 'integrado'){

                            $ProdLoaded = Mage::getModel('catalog/product')->load( $product->getId() );
                            if( ($ProdLoaded->getStatus() == 1) && ($ProdLoaded->getData('integra_anymarket') == 1) ){
                                Mage::helper('db1_anymarket/product')->sendProductToAnyMarket( $product->getId() );

                                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', Mage::app()->getStore()->getId()));
                                $ProdStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($product->getId(), $ProdStock->getQty(), $ProdLoaded->getData($filter));
                            }
                        }
                    }

                }
            }
        }
	}

    public function executeQueue(){
        Mage::helper('db1_anymarket/queue')->processQueue();
    }

}