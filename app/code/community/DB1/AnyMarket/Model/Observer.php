<?php

class DB1_AnyMarket_Model_Observer {

    public function sendProdAnyMarket($observer) {
        $ExportProdSession = Mage::getSingleton('core/session')->getImportProdsVariable();
        if( $ExportProdSession != 'false' ) {
           	$productOld = $observer->getEvent()->getProduct();
            $storeID = $productOld->getStoreId();
            if($storeID == null){
                $storeID = 0;
            }

            $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load($productOld->getId());
            if( $product->getData('integra_anymarket') == 1 ){
               	$sincronize = false;
                $parentIds = null;
               	if($product->getTypeID() == "configurable"){
                    if($product->getStatus() == 1){ //se nao esta com o status enabled
                        Mage::getModel('catalog/product_type_configurable')->getProduct($product)->unsetData('_cache_instance_products');
                   		$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

                   		if(count($childProducts) > 0){
                   			$sincronize = true;
                   		}
                    }
               	}else{
                    if($product->getStatus() == 1){ //se nao esta com o status enabled
                        if($product->getVisibility() == 1){ //nao exibido individualmente
                            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );

                            if ($parentIds) {
                                $sincronize = true;
                            }else if( $product->getTypeID() != 'simple'){
                                $sincronize = true;
                                $parentIds = 0;
                            }
                        }else{
                            $sincronize = true;
                            $parentIds = 0;
                        }
                    }

               	}

               	if($sincronize == true){
                    Mage::app()->setCurrentStore($storeID);

                    $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
                    $sendProd = false;
                    if( ($typeSincProd == 0) && (!$parentIds) ){
          	   		    $sendProd = Mage::helper('db1_anymarket/product')->sendProductToAnyMarket( $product->getId());
                    }

                    if($sendProd){
                        $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                        if ($product->getData($filter) != $productOld->getOrigData($filter)){
                            if( $typeSincProd == 0 ){
                                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($product->getId(), null, $product->getData($filter));
                            }
                        }
                    }
             	}
            }
        }

    }

    public function updateOrderAnyMarketObs($observer){
        $ImportOrderSession = Mage::getSingleton('core/session')->getImportOrdersVariable();

        $OrderID = $observer->getEvent()->getOrder()->getIncrementId();
        if(Mage::registry('order_save_observer_executed_'.$OrderID )){
            return $this;
        }

        Mage::register('order_save_observer_executed_'.$OrderID, true);
        Mage::app()->setCurrentStore( $observer->getEvent()->getOrder()->getStoreId() );
        Mage::helper('db1_anymarket/order')->updateOrderAnyMarket( $observer->getEvent()->getOrder() );
    }

    public function removeProdAnyMarketControl($observer){
        $product = $observer->getEvent()->getProduct();

        $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->getCollection()
                                ->addFieldToFilter('nmp_sku', $product->getSku());

        foreach( $anymarketproducts as $item ){
            $item->delete();
        }
    }

    public function catalogInventorySave($observer){
        $ImportOrderSession = Mage::getSingleton('core/session')->getImportOrdersVariable();
        if( $ImportOrderSession != 'false' ) {
            $event = $observer->getEvent();
            $_item = $event->getItem();

            $storeID = $_item->getData('store_id');
            if($storeID == null){
                $storeID = 0;
            }

            Mage::app()->setCurrentStore($storeID);
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
            if($typeSincProd == 0){
                if ((int)$_item->getData('qty') != (int)$_item->getOrigData('qty')) {
                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($_item->getProductId(), $_item->getQty(), null);
                }
            }
        }
    }

    public function subtractQuoteInventory($observer){
        $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());
        if($typeSincProd == 0){
            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $itemSold = $item->getTotalQty();
                $qty = $item->getProduct()->getStockItem()->getQty();
                $qtyNow = $qty-$itemSold;

                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $qtyNow, null);
            }
        }
    }

    public function revertQuoteInventory($observer){
        $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());
        if($typeSincProd == 0){
            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $qty = $item->getProduct()->getStockItem()->getQty();
                $itemRevert = ($item->getTotalQty());
                $qtyNow = $qty+$itemRevert;

                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $qtyNow, null);
            }
        }
    }

    public function cancelOrderItem($observer){
        $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());
        if($typeSincProd == 0){
            $item = $observer->getEvent()->getItem();
            $storeID = $item->getStoreId();
            Mage::app()->setCurrentStore($storeID);

            $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

            Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $item->getProduct()->getStockItem()->getQty(), null);
        }
    }

    public function refundOrderInventory($observer){
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $storeID = $creditmemo->getStoreId();
        if($storeID == null){
            $storeID = 0;
        }
        Mage::app()->setCurrentStore($storeID);

        $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
        if($typeSincProd == 0){
            foreach ($creditmemo->getAllItems() as $item) {
                if($item->getData('back_to_stock') == 1){
                    $ProdLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->load($item->getProductId());
                    $stockQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($ProdLoaded)->getQty();

                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $stockQty+(int)$item->getQty(), null);
                }
            }
        }
    }

}
?>