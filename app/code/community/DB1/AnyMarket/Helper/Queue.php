<?php

class DB1_AnyMarket_Helper_Queue extends DB1_AnyMarket_Helper_Data
{

    /**
     * add item in current queue
     *
     * @param $IdItem
     * @param $typeItem
     * @param $tableItem
     */
    public function addQueue($IdItem, $typeItem, $tableItem){
        $storeID = $this->getCurrentStoreView();

        $queueItem = Mage::getModel('db1_anymarket/anymarketqueue');
        $queueItem->setNmqId($IdItem);
        $queueItem->setNmqType($typeItem);
        $queueItem->setNmqTable($tableItem);
        $queueItem->setStores(array($storeID));
        $queueItem->save();
    }

    /**
     * remove item from queue
     *
     * @param $idItem
     */
    public function removeQueue($idItem){
        $anymarketQueueDel = Mage::getModel('db1_anymarket/anymarketqueue');
        $anymarketQueueDel->setId($idItem)->delete();
    }

    /**
     * process queue
     */
    public function processQueue(){
        $qtyItensImport = (int)Mage::getConfig()->getNode('default/queue_qty/qty');
        $itens = Mage::getModel('db1_anymarket/anymarketqueue')
                ->getCollection()
                ->setPageSize($qtyItensImport)
                ->setCurPage(1);

        foreach($itens->getData() as $item) {
            $IdItemQueue = $item['nmq_id'];

            $anymarketQueue = Mage::getModel('db1_anymarket/anymarketqueue')->load($item['entity_id']);
            $arrValueStore = array_values($anymarketQueue->getStoreId());
            $storeID = array_shift($arrValueStore);

            $cronEnabled = Mage::getStoreConfig('anymarket_section/anymarket_cron_group/anymarket_queue_field', $storeID);
            if($cronEnabled == '1') {
                Mage::app()->setCurrentStore($storeID);
                $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);

                $typImp = $item['nmq_type'];
                if ($item['nmq_table'] == 'ORDER') {
                    try {
                        if ($typImp == 'IMP') {
                            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->setStoreId($storeID);
                            $anymarketorders->load($IdItemQueue, 'nmo_id_anymarket');
                            //Import
                            if ($anymarketorders->getNmoStatusInt() != "Não integrado (Magento)") {
                                $idAnyMarket = $anymarketorders->getNmoIdSeqAnymarket();
                                if ($idAnyMarket) {
                                    $idReg = $anymarketorders->getId();
                                    Mage::helper('db1_anymarket/order')->getSpecificOrderFromAnyMarket($idAnyMarket, "notoken", $storeID);
                                }
                            }
                        } else {
                            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->setStoreId($storeID);
                            $anymarketorders->load($IdItemQueue, 'nmo_id_order');
                            //Export
                            if ($anymarketorders->getNmoStatusInt() != "Não integrado (AnyMarket)") {
                                $Order = Mage::getModel('sales/order')->loadByIncrementId($anymarketorders->getNmoIdOrder());
                                Mage::helper('db1_anymarket/order')->updateOrderAnyMarket($Order);
                            }
                        }

                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                } else if ($item['nmq_table'] == 'PRODUCT') {
                    // EXPORT PRODUCT
                    $typeSincOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->setStoreId($storeID);
                    $anymarketproducts->load($IdItemQueue, 'nmp_id');

                    $product = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $anymarketproducts->getNmpSku());
                    if (($typImp == 'EXP') && ($typeSincProd == 0)) {
                        try {
                            $anymarketproducts->setStatus('1')->setIsMassupdate(true)->save();
                            if ($product != null) {
                                Mage::helper('db1_anymarket/product')->sendProductToAnyMarket($product->getId());
                            }

                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }

                    // TRATA STOCK
                    if ($product) {
                        if ($typeSincOrder == 1) {
                            $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $this->getCurrentStoreView()));
                            $ProdStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            Mage::helper('db1_anymarket/product')->updatePriceStockAnyMarket($product->getId(), $ProdStock->getQty(), $product->getData($filter));
                        } else {
                            Mage::helper('db1_anymarket/product')->getStockProductAnyMarket($product->getId(), $storeID);
                        }
                    }

                }
                $this->removeQueue($item['entity_id']);
            }
        }

    }

    /**
     * process Orders by CRON
     */
    public function processOrders(){
        Mage::getSingleton('core/session')->setImportOrdersVariable('false');

        $allStores = Mage::helper('db1_anymarket')->getAllStores();
        foreach ($allStores as $store) {
            $storeID = $store['store_id'];

            $cronEnabled = Mage::getStoreConfig('anymarket_section/anymarket_cron_group/anymarket_order_field', $storeID);
            if( $cronEnabled == '1' ) {
                Mage::app()->setCurrentStore($storeID);

                $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
                if ($ConfigOrder == 1) {
                    Mage::helper('db1_anymarket/order')->getFeedOrdersFromAnyMarket();
                }

                $colAnyOrders = Mage::getResourceModel('db1_anymarket/anymarketorders_collection')
                    ->addFieldToFilter('nmo_status_int', array('like' => 'ERROR%'))
                    ->load();

                foreach ($colAnyOrders->getItems() as $anymarketorders) {
                    $anymarketorder = Mage::getModel('db1_anymarket/anymarketorders')->load($anymarketorders->getId());
                    if (is_array($anymarketorder->getData('store_id')) && in_array($storeID, $anymarketorder->getData('store_id'))) {
                        if ($anymarketorders->getData('nmo_status_int') == 'ERROR 01') {
                            Mage::helper('db1_anymarket/queue')->addQueue($anymarketorder->getNmoIdAnymarket(), 'IMP', 'ORDER');
                        } else if ($anymarketorders->getData('nmo_status_int') == 'ERROR 02') {
                            Mage::helper('db1_anymarket/queue')->addQueue($anymarketorder->getNmoIdOrder(), 'EXP', 'ORDER');
                        }
                    }
                }
            }

        }
        Mage::getSingleton('core/session')->setImportOrdersVariable('true');

    }

    /**
     * process Products By CRON
     */
    public function processProducts(){
        $allStores = Mage::helper('db1_anymarket')->getAllStores();
        foreach ($allStores as $store) {
            $storeID = $store['store_id'];
            $cronEnabled = Mage::getStoreConfig('anymarket_section/anymarket_cron_group/anymarket_product_field', $storeID);
            if( $cronEnabled == '1' ) {
                Mage::app()->setCurrentStore($storeID);

                $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
                if ($typeSincProd == 1) {
                    Mage::helper('db1_anymarket/product')->getFeedProdsFromAnyMarket();
                } else {
                    $colAnyProds = Mage::getResourceModel('db1_anymarket/anymarketproducts_collection')
                        ->addFieldToFilter('nmp_status_int', array('neq' => 'Integrado'))
                        ->load();

                    foreach ($colAnyProds->getItems() as $anymarketproducts) {
                        if ($anymarketproducts->getData('nmp_sku') != null) {
                            $anymarketprod = Mage::getModel('db1_anymarket/anymarketproducts')->load($anymarketproducts->getData('nmp_id'), 'nmp_id');
                            if (is_array($anymarketprod->getData('store_id')) && in_array($storeID, $anymarketprod->getData('store_id'))) {

                                if($anymarketprod->getData('nmp_status_int') == "Erro" ) {
                                    $ProdLoaded = Mage::getModel('catalog/product')->loadByAttribute('sku', $anymarketproducts->getData('nmp_sku'));
                                    if ($ProdLoaded) {
                                        if (($ProdLoaded->getStatus() == 1) && ($ProdLoaded->getData('integra_anymarket') == 1)) {
                                            Mage::helper('db1_anymarket/queue')->addQueue($anymarketproducts->getData('nmp_id'), 'EXP', 'PRODUCT');
                                        }
                                    }
                                }

                            }
                        }
                    }

                }
            }
        }
    }

    /**
     * process Clean Logs By CRON
     */
    public function processCleanLogs(){
        $from = date("Y-m-d H:m:s", strtotime("-3 months"));
        $to   = date("Y-m-d H:m:s", strtotime("-73 years"));
        $collection = Mage::getResourceModel('db1_anymarket/anymarketlog_collection')
            ->addFieldToFilter('updated_at', array('from'=> $to, 'to'=> $from ))
            ->load();

        $contLogs = 0;
        foreach ($collection->getItems() as $anymarketlog) {
            $anymarketlog->delete();
            $contLogs += 1;
        }

        return $contLogs;
    }

    /**
     * process Reindex by CRON
     */
    public function processReindex()
    {
        $allStores = Mage::helper('db1_anymarket')->getAllStores();
        foreach ($allStores as $store) {
            $storeID = $store['store_id'];
            $cronEnabled = Mage::getStoreConfig('anymarket_section/anymarket_cron_group/anymarket_reindex_field', $storeID);
            if( $cronEnabled == '1' ) {
                $processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
                foreach ($processes as $process) {
                    if( $process->getData("mode") == "manual" ) {
                        $process->reindexEverything();
                    }
                }
            }
        }
    }

}