<?php

class DB1_AnyMarket_Model_Observer {

    private function asyncMode($storeID){
        return Mage::getStoreConfig('anymarket_section/anymarket_general_group/anymarket_operation_type_field', $storeID);
    }

    /**
     * @param $observer
     */
    public function setQuickCreateFlag($observer) {
        $request = $observer->getControllerAction()->getRequest();
        $simpleProductRequest = $request->getParam('simple_product');

        if( isset($simpleProductRequest['sku_autogenerate']) ){
            $configurableProduct = Mage::getModel('catalog/product')
                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
                ->load($request->getParam('product'));

            $product = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                ->setAttributeSetId($configurableProduct->getAttributeSetId());
            $product->addData( $request->getParam('simple_product', array()) );

            foreach ($configurableProduct->getTypeInstance()->getConfigurableAttributes() as $attribute) {
                $value = $product->getAttributeText($attribute->getProductAttribute()->getAttributeCode());
                $autogenerateOptions[] = $value;
            }

            $sku = $configurableProduct->getSku() . '-' . implode('-', $autogenerateOptions);

            Mage::getSingleton('core/session')->setQuickCreateProdVariable($sku);
        }else{
            Mage::getSingleton('core/session')->setQuickCreateProdVariable($simpleProductRequest['sku']);
        }
    }

    /**
     * @param $observer
	 * @return array
     */
    public function sendProdAnyMarket($observer) {
        $productOld = $observer->getEvent()->getProduct();
        $storeID = ($productOld->getStoreId() != null && $productOld->getStoreId() != "0") ? $productOld->getStoreId() : Mage::app()->getDefaultStoreView()->getId();
        try{
            $ExportProdSession = Mage::getSingleton('core/session')->getImportProdsVariable();
            if( $ExportProdSession == 'false' ) {
                return false;
            }

            $QuickCreate = Mage::getSingleton('core/session')->getQuickCreateProdVariable();
            if($QuickCreate != null || $QuickCreate != "" || $QuickCreate == $productOld->getSku() ) {
                Mage::getSingleton('core/session')->setQuickCreateProdVariable('');
                return false;
            }

            if( Mage::registry('prod_save_observer_executed_'.$productOld->getId()) ){
                Mage::unregister( 'prod_save_observer_executed_'.$productOld->getId() );
                return $this;
            }
            Mage::register('prod_save_observer_executed_'.$productOld->getId(), true);

            $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load($productOld->getId());
            if( $this->asyncMode($storeID) && $product->getData('integra_anymarket') == 1 ) {
                Mage::helper('db1_anymarket/queue')->addQueue($storeID, $product->getId(), 'EXP', 'PRODUCT');
                return false;
            }

            Mage::helper('db1_anymarket/product')->prepareForSendProduct($storeID, $product);

        } catch (Exception $e) {
            Mage::unregister( 'prod_save_observer_executed_'.$productOld->getId() );
            Mage::logException($e);
        }
    }

    /**
     * @param $observer
     */
    public function updateCategory($observer){
        $category = $observer->getEvent()->getCategory();
        if( !$category->getName() ) {
            $category = Mage::getModel('catalog/category')->load($category->getId());
        }

        $storeID = Mage::app()->getRequest()->getParam('store') == 0 ? Mage::helper('db1_anymarket')->getCurrentStoreView() : Mage::app()->getRequest()->getParam('store');
        if( $category->getData('categ_integra_anymarket') == 1 ){
            $amCategParent = Mage::getModel('db1_anymarket/anymarketcategories')->load($category->getParentId(), 'nmc_id_magento');
            if( $amCategParent->getData('nmc_cat_id') ){
                Mage::helper('db1_anymarket/category')->exportSpecificCategory($category, $amCategParent->getData('nmc_cat_id'), $storeID);
            }else{
                Mage::helper('db1_anymarket/category')->exportSpecificCategory($category, null, $storeID);
            }

            if($category->getChildren() != ''){
                Mage::helper('db1_anymarket/category')->exportCategRecursively($category, $storeID);
            }
        }elseif( $category->getData('categ_integra_anymarket') == 0 ){
            //Mage::helper('db1_anymarket/category')->deleteCategs($category, $storeID);
        }
    }

    /**
     * @param $observer
     */
    public function deleteCategory($observer){
/*
        $category = $observer->getEvent()->getCategory();
        $storeID = Mage::helper('db1_anymarket')->getCurrentStoreView();

        if( $category->getData('categ_integra_anymarket') == 1 ){
            Mage::helper('db1_anymarket/category')->deleteCategs($category, $storeID);
        }
*/
    }

    /**
     * @param $observer
     * @return $this
     */
    public function updateOrderAnyMarketObs($observer){
        $storeID = $observer->getEvent()->getOrder()->getStoreId();
        $OrderID = $observer->getEvent()->getOrder()->getIncrementId();
        try {
            if(Mage::registry('order_save_observer_executed_'.$OrderID )){
                Mage::unregister( 'order_save_observer_executed_'.$OrderID );
                return $this;
            }

            Mage::register('order_save_observer_executed_'.$OrderID, true);
            $order = $observer->getEvent()->getOrder();

            if( $this->asyncMode($storeID) ){
                Mage::helper('db1_anymarket/queue')->addQueue($storeID, $OrderID, 'EXP', 'ORDER');
            }else{
                Mage::helper('db1_anymarket/order')->updateOrderAnyMarket($storeID, $order );
            }

            //DECREMENTA STOCK ANYMARKET
            $orderItems = $order->getItemsCollection();
            $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
            foreach ($orderItems as $item){
                $product_id = $item->product_id;
                if( $this->asyncMode($storeID) ) {
                    Mage::helper('db1_anymarket/queue')->addQueue($storeID, $product_id, 'EXP', 'STOCK');
                }else {
                    $_product = Mage::getModel('catalog/product')->load($product_id);

                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($storeID, $product_id, $stock->getQty(), $_product->getData($filter));
                }
            }
            Mage::unregister( 'order_save_observer_executed_'.$OrderID );
        } catch (Exception $e) {
            Mage::unregister( 'order_save_observer_executed_'.$OrderID );
            Mage::logException($e);
        }

    }

    /**
     * @param $observer
     */
    public function removeProdAnyMarketControl($observer){
        $product = $observer->getEvent()->getProduct();

        $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->getCollection()
                                ->addFieldToFilter('nmp_sku', $product->getSku());

        foreach( $anymarketproducts as $item ){
            $item->delete();
        }
    }

    /**
     * @param $observer
     */
    public function catalogInventorySave($observer){
        $ImportOrderSession = Mage::getSingleton('core/session')->getImportOrdersVariable();
        if( $ImportOrderSession != 'false' ) {
            $event = $observer->getEvent();
            $_item = $event->getItem();

			$storeID = ($_item->getData('store_id') != null && $_item->getData('store_id') != "0") ? $_item->getData('store_id') : Mage::app()->getDefaultStoreView()->getId();
            if( $this->asyncMode($storeID) ){
                Mage::helper('db1_anymarket/queue')->addQueue($storeID, $_item->getProductId(), 'EXP', 'STOCK');
                return false;
            }

            $product = Mage::getModel('catalog/product')->load( $_item->getProductId() );
            if ( $product->getId() ) {
                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($storeID, $_item->getProductId(), $_item->getQty(), $product->getData($filter));
            }
        }
    }

    /**
     * @param $observer
     */
    public function subtractQuoteInventory($observer){
		$quote = $observer->getEvent()->getQuote();
		foreach ($quote->getAllItems() as $item) {
		    $product = Mage::getModel('catalog/product')->load( $item->getProductId() );
		    if ( $product->getId() ) {
                $storeID = ($item->getStoreId() != null && $item->getStoreId() != "0") ? $item->getStoreId() : Mage::app()->getDefaultStoreView()->getId();
                if( $this->asyncMode($storeID) ){
                    Mage::helper('db1_anymarket/queue')->addQueue($storeID, $item->getProductId(), 'EXP', 'STOCK');
                }else {
                    $itemSold = $item->getTotalQty();
                    $qty = $item->getProduct()->getStockItem()->getQty();
                    $qtyNow = $qty - $itemSold;

                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($storeID, $item->getProductId(), $qtyNow, null);
                }
            }
		}

    }

    /**
     * @param $observer
     */
    public function revertQuoteInventory($observer){
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load( $item->getProductId() );
            if ( $product->getId() ) {
                $storeID = ($item->getStoreId() != null && $item->getStoreId() != "0") ? $item->getStoreId() : Mage::app()->getDefaultStoreView()->getId();
                if( $this->asyncMode($storeID) ){
                    Mage::helper('db1_anymarket/queue')->addQueue($storeID, $item->getProductId(), 'EXP', 'STOCK');
                }else{
                    $qty = $item->getProduct()->getStockItem()->getQty();
                    $itemRevert = ($item->getTotalQty());
                    $qtyNow = $qty + $itemRevert;

                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($storeID, $item->getProductId(), $qtyNow, null);
                }
            }
        }
    }

    /**
     * @param $observer
     */
    public function cancelOrderItem($observer){
        $item = $observer->getEvent()->getItem();
        $product = Mage::getModel('catalog/product')->load( $item->getProductId() );
        if ( $product->getId() ) {
            $storeID = ($item->getStoreId() != null && $item->getStoreId() != "0") ? $item->getStoreId() : Mage::app()->getDefaultStoreView()->getId();
            if( $this->asyncMode($storeID) ){
                Mage::helper('db1_anymarket/queue')->addQueue($storeID, $product->getId(), 'EXP', 'STOCK');
            }else{
                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($storeID, $product->getId(), $product->getStockItem()->getQty(), null);
            }
        }
    }

    /**
     * @param $observer
     */
    public function refundOrderInventory($observer){
        $creditmemo = $observer->getEvent()->getCreditmemo();
		$storeID = ($creditmemo->getStoreId() != null && $creditmemo->getStoreId() != "0") ? $creditmemo->getStoreId() : Mage::app()->getDefaultStoreView()->getId();

        foreach ($creditmemo->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load( $item->getProductId() );
            if ( $product->getId() ) {
                if ($item->getData('back_to_stock') == 1){
                    if( $this->asyncMode($storeID) ){
                        Mage::helper('db1_anymarket/queue')->addQueue($storeID, $item->getProductId(), 'EXP', 'STOCK');
                    }else {
                        $ProdLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->load($item->getProductId());
                        $stockQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($ProdLoaded)->getQty();

                        Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($storeID, $item->getProductId(), $stockQty + (int)$item->getQty(), null);
                    }
                }
            }
        }
    }


    /**
     * Save Admin Configurations
     */
    public function saveConfigurations(Varien_Event_Observer $observer){
        $postData = $observer->getEvent()->getData();

        if (is_null($postData['store']) && $postData['website']) {
            $scopeId = Mage::getModel('core/website')->load($postData['website'])->getId();
            $OI = Mage::app()->getWebsite($scopeId)->getConfig('anymarket_section/anymarket_acesso_group/anymarket_oi_field');
        } elseif($postData['store']) {
            $scopeId = Mage::getModel('core/store')->load($postData['store'])->getId();
            $OI = Mage::app()->getStore($scopeId)->getConfig('anymarket_section/anymarket_acesso_group/anymarket_oi_field');
        } else {
            $scopeId = 0;
            $OI  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_oi_field');
        }

        if($OI != '') {
            $configs = Mage::getModel('core/config_data')->getCollection()
                ->addFieldToFilter('path', 'anymarket_section/anymarket_acesso_group/anymarket_oi_field');

            foreach ($configs as $config) {
                if (($config->getValue() == $OI) && ($config->getScopeId() != $scopeId)) {

                    Mage::getModel('core/config')->saveConfig('anymarket_section/anymarket_acesso_group/anymarket_oi_field',
                        '',
                        'stores',
                        $scopeId);

                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('db1_anymarket')->__('This token ' . $OI . ' it is already being used.')
                    );
                    break;
                }

            }
        }


    }



}
?>