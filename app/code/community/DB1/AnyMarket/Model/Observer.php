<?php

class DB1_AnyMarket_Model_Observer {

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
     */
    public function sendProdAnyMarket($observer) {
        $ExportProdSession = Mage::getSingleton('core/session')->getImportProdsVariable();
        if( $ExportProdSession != 'false' ) {
            $productOld = $observer->getEvent()->getProduct();
            $QuickCreate = Mage::getSingleton('core/session')->getQuickCreateProdVariable();
            if($QuickCreate == null || $QuickCreate == "" || $QuickCreate != $productOld->getSku() ){
                $storeID = ($productOld->getStoreId() !== null) ? $productOld->getStoreId() : 1;

                $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
                if($typeSincProd == 0){
                    Mage::app()->setCurrentStore($storeID);

                    $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load($productOld->getId());
                    if( $product->getData('integra_anymarket') == 1 && $product->getStatus() == 1 ){

                        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                        $stockQty = $stock->getQty();
                        if($product->getTypeID() == "configurable"){
                            //PRODUTO CONFIGURAVEL
                            Mage::getModel('catalog/product_type_configurable')->getProduct($product)->unsetData('_cache_instance_products');
                            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                            if(count($childProducts) > 0){
                                Mage::helper('db1_anymarket/product')->sendProductToAnyMarket( $product->getId());
                            }
                        }else{
                            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                            if($parentIds){
                                //PRODUTO SIMPLES FILHO DE UM CONFIG
                                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                                $ean    = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_ean_field', $storeID);

                                if($filter == 'final_price'){
                                    $stkPrice = $product->getData($filter);
                                }else{
                                    $stkPrice = $product->getFinalPrice();
                                }

                                $attributeOptions = array();
                                foreach ($parentIds as $parentId) {
                                    $productConfig = Mage::getModel('catalog/product')->load($parentId);

                                    if( $productConfig->getId() ) {
                                        foreach ($productConfig->getTypeInstance()->getConfigurableAttributes() as $attribute) {
                                            $value = $product->getAttributeText($attribute->getProductAttribute()->getAttributeCode());
                                            $attributeOptions[$attribute->getLabel()] = $value;
                                        }

                                        foreach ($parentIds as $parentId) {
                                            $arrSku = array(
                                                "variations" => $attributeOptions,
                                                "price" => $stkPrice,
                                                "amount" => $stockQty,
                                                "ean" => $product->getData($ean),
                                                "partnerId" => $product->getSku(),
                                                "title" => $product->getName(),
                                                "idProduct" => $product->getData('id_anymarket'),
                                                "internalIdProduct" => $product->getId(),
                                            );
                                            Mage::helper('db1_anymarket/product')->sendImageSkuToAnyMarket($product, array($arrSku), $storeID);
                                        }
                                    }
                                }
                            }else{
                                //PRODUTO SIMPLES E OUTROS
                                $sendProd = Mage::helper('db1_anymarket/product')->sendProductToAnyMarket( $product->getId());

                                if($sendProd){
                                    $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($product->getId(), $stockQty, $product->getData($filter));
                                }
                            }

                        }

                    }
                }
            }else{
                Mage::getSingleton('core/session')->setQuickCreateProdVariable('');
            }

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
        $storeID = Mage::helper('db1_anymarket')->getCurrentStoreView();
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
        $category = $observer->getEvent()->getCategory();
        $storeID = Mage::helper('db1_anymarket')->getCurrentStoreView();

        if( $category->getData('categ_integra_anymarket') == 1 ){
            //Mage::helper('db1_anymarket/category')->deleteCategs($category, $storeID);
        }
    }

    /**
     * @param $observer
     * @return $this
     */
    public function updateOrderAnyMarketObs($observer){
        $OrderID = $observer->getEvent()->getOrder()->getIncrementId();
        if(Mage::registry('order_save_observer_executed_'.$OrderID )){
            return $this;
        }

        Mage::register('order_save_observer_executed_'.$OrderID, true);
        Mage::app()->setCurrentStore( $observer->getEvent()->getOrder()->getStoreId() );
        $order = $observer->getEvent()->getOrder();
        Mage::helper('db1_anymarket/order')->updateOrderAnyMarket( $order );

        //DECREMENTA STOCK ANYMARKET
        $orderItems = $order->getItemsCollection();
        $storeID = Mage::helper('db1_anymarket')->getCurrentStoreView();
        $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
        foreach ($orderItems as $item){
            $product_id = $item->product_id;
            $_product = Mage::getModel('catalog/product')->load($product_id);

            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
            Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($product_id, $stock->getQty(), $_product->getData($filter));
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

			$storeID = ($_item->getData('store_id') !== null) ? $_item->getData('store_id') : 0;

            Mage::app()->setCurrentStore($storeID);
            $product = Mage::getModel('catalog/product')->load( $_item->getProductId() );
            if ( $product->getId() ) {
                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($_item->getProductId(), $_item->getQty(), $product->getData($filter));
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
		        $itemSold = $item->getTotalQty();
		        $qty = $item->getProduct()->getStockItem()->getQty();
		        $qtyNow = $qty - $itemSold;
		
		        Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $qtyNow, null);
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
                $qty = $item->getProduct()->getStockItem()->getQty();
                $itemRevert = ($item->getTotalQty());
                $qtyNow = $qty + $itemRevert;

                Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $qtyNow, null);
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
            $storeID = ($item->getStoreId() !== null) ? $item->getStoreId() : 0;
            Mage::app()->setCurrentStore($storeID);

            Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($product->getId(), $product->getStockItem()->getQty(), null);
        }
    }

    /**
     * @param $observer
     */
    public function refundOrderInventory($observer){
        $creditmemo = $observer->getEvent()->getCreditmemo();
		$storeID = ($creditmemo->getStoreId() !== null) ? $creditmemo->getStoreId() : 0;

        Mage::app()->setCurrentStore($storeID);
        foreach ($creditmemo->getAllItems() as $item) {
            $product = Mage::getModel('catalog/product')->load( $item->getProductId() );
            if ( $product->getId() ) {
                if ($item->getData('back_to_stock') == 1){
                    $ProdLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->load($item->getProductId());
                    $stockQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($ProdLoaded)->getQty();

                    Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($item->getProductId(), $stockQty + (int)$item->getQty(), null);
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