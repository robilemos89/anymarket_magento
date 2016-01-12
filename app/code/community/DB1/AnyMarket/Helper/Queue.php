<?php

class DB1_AnyMarket_Helper_Queue extends DB1_AnyMarket_Helper_Data
{

    public function addQueue($IdItem, $typeItem, $tableItem){
        $storeID = Mage::app()->getStore()->getId(); 

        $queueItem = Mage::getModel('db1_anymarket/anymarketqueue');
        $queueItem->setNmqId($IdItem);
        $queueItem->setNmqType($typeItem);
        $queueItem->setNmqTable($tableItem);
        $queueItem->setStores(array($storeID));
        $queueItem->save();
    }

    public function removeQueue($idItem){
        $anymarketQueueDel = Mage::getModel('db1_anymarket/anymarketqueue');
        $anymarketQueueDel->setId($idItem)->delete();
    }

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
            Mage::app()->setCurrentStore($storeID);
            $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);

            if($item['nmq_table'] == 'ORDER'){
                try {
                    Mage::getSingleton('core/session')->setImportOrdersVariable('true');

                    $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                    if($ConfigOrder == 1){
                        $anymarketorders->load($IdItemQueue, 'nmo_id_anymarket');
                        //Import
                        if( $anymarketorders->getNmoStatusInt() != "Não integrado (Magento)") {
                            $idAnyMarket = $anymarketorders->getNmoIdSeqAnymarket();
                            if($idAnyMarket){
                                $IDOrderAnyMarket = $anymarketorders->getNmoIdAnymarket();
                                $idReg = $anymarketorders->getId();
                                $idOrderMage = $anymarketorders->getNmoIdOrder();

                                $anymarketordersDel = Mage::getModel('db1_anymarket/anymarketorders');
                                $anymarketordersDel->setId( $idReg )->delete();
    
                                Mage::helper('db1_anymarket/order')->getSpecificOrderFromAnyMarket($idAnyMarket, $IDOrderAnyMarket, $idOrderMage);
                            }
                        }

                    }else{
                        $anymarketorders->load($IdItemQueue, 'nmo_id_order');
                        //Export
                        if( $anymarketorders->getNmoStatusInt() != "Não integrado (AnyMarket)" ){
                            $Order = Mage::getModel('sales/order')->loadByIncrementId( $anymarketorders->getNmoIdOrder() );
                            Mage::helper('db1_anymarket/order')->updateOrderAnyMarket($Order); 
                        }
                    }

                } catch (Exception $e) {
                    Mage::logException($e);
                }
                Mage::getSingleton('core/session')->setImportProdsVariable('false'); 
            }else if($item['nmq_table'] == 'PRODUCT'){
                $typImp = $item['nmq_type'];
                // IMPORT PRODUCT
                if( ($typImp == 'IMP') && ($typeSincProd == 1) ){

                     try {
                        Mage::getSingleton('core/session')->setImportProdsVariable('false');

                        $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                        $anymarketproducts->load($IdItemQueue, 'nmp_id');

                        $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load( $anymarketproducts->getNmpId() );
                        $needDel = false;

                        if($product->getIdAnymarket() != ''){
                            $idProd = $product->getIdAnymarket();
                        }else{
                            $needDel = true;
                            $idProd = $IdItemQueue;
                        }

                        $idReg = $anymarketproducts->getId();
                        $prod = Mage::helper('db1_anymarket/product')->getSpecificProductAnyMarket($idProd);
                        if($prod != null){
                            if($needDel){
                                //$anymarketproductsDel = Mage::getModel('db1_anymarket/anymarketproducts');
                                //$anymarketproductsDel->setId( $idReg )->delete();
                                $anymarketproductsUpdt = Mage::getModel('db1_anymarket/anymarketproducts')->load($idReg);
                                $anymarketproductsUpdt->setNmpStatusInt("Integrado");
                                $anymarketproductsUpdt->setNmpDescError("");
                                $anymarketproductsUpdt->setNmpSku( $prod->getSku() );
                                $anymarketproductsUpdt->save();
                            }
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    Mage::getSingleton('core/session')->setImportProdsVariable('true');

                }else if( ($typImp == 'EXP') && ($typeSincProd == 0) ){
                    try {
                        $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                        $anymarketproducts->load($IdItemQueue, 'nmp_id');

                        $anymarketproducts->setStatus('1')->setIsMassupdate(true)->save();

                        $product = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $anymarketproducts->getNmpSku());
                        if($product != null){
                            Mage::getSingleton('core/session')->setImportProdsVariable('false');
                            $amProd = Mage::helper('db1_anymarket/product');

                            $amProd->sendProductToAnyMarket( $product->getId() );
                            Mage::getSingleton('core/session')->setImportProdsVariable('true');

                            $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', Mage::app()->getStore()->getId()));
                            $ProdStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            $amProd->updatePriceStockAnyMarket($product->getId(), $ProdStock->getQty(), $product->getData($filter));

                        }

                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    Mage::getSingleton('core/session')->setImportProdsVariable('true'); 
                }

            }

            $this->removeQueue($item['entity_id']);
        }

    }

}