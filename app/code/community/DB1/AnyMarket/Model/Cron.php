<?php
class DB1_AnyMarket_Model_Cron{

    public function sincOrders(){
        Mage::getSingleton('core/session')->setImportOrdersVariable('false');

        $allStores = Mage::helper('db1_anymarket')->getAllStores();
        foreach ($allStores as $store) {
            $storeID = $store['store_id'];
            Mage::app()->setCurrentStore($storeID);

            $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
            if($ConfigOrder == 1){
                Mage::helper('db1_anymarket/order')->getFeedOrdersFromAnyMarket();
            }

            $colAnyOrders = Mage::getResourceModel('db1_anymarket/anymarketorders_collection')
                ->addFieldToFilter('nmo_status_int', array('like' => 'ERROR%'))
                ->load();

            foreach ($colAnyOrders->getItems() as $anymarketorders) {
                $anymarketorder = Mage::getModel('db1_anymarket/anymarketorders')->load($anymarketorders->getId() );
                if (is_array($anymarketorder->getData('store_id')) && in_array($storeID, $anymarketorder->getData('store_id'))){
                    if($anymarketorders->getData('nmo_status_int') == 'ERROR 01'){
                        Mage::helper('db1_anymarket/queue')->addQueue($anymarketorder->getNmoIdAnymarket(), 'IMP', 'ORDER');
                    }else if($anymarketorders->getData('nmo_status_int') == 'ERROR 02'){
                        Mage::helper('db1_anymarket/queue')->addQueue($anymarketorder->getNmoIdOrder(), 'EXP', 'ORDER');
                    }
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
                $colAnyProds = Mage::getResourceModel('db1_anymarket/anymarketproducts_collection')
                    ->addFieldToFilter('nmp_status_int', array('neq' => 'Integrado'))
                    ->load();

                foreach ($colAnyProds->getItems() as $anymarketproducts) {
                    if($anymarketproducts->getData('nmp_sku') != null){

                        $anymarketprod = Mage::getModel('db1_anymarket/anymarketproducts')->load($anymarketproducts->getData('nmp_id'), 'nmp_id');
                        if (is_array($anymarketprod->getData('store_id')) && in_array($storeID, $anymarketprod->getData('store_id'))){

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

    public function executeQueue(){
        Mage::helper('db1_anymarket/queue')->processQueue();
    }

}