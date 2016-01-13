<?php

class DB1_AnyMarket_Helper_Order extends DB1_AnyMarket_Helper_Data
{
    /**
     * get status order AM to MG from configs
     *
     * @access private
     * @param $OrderRowData
     * @return string
     * 
     */
    private function getStatusAnyMarketToMageOrderConfig($OrderRowData){
        $storeID = Mage::app()->getStore()->getId();
        $StatusOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_am_mg_field', $storeID);
        $OrderReturn = 'ERROR: Não há uma configuração válida para '.$OrderRowData;
        if ($StatusOrder && $StatusOrder != 'a:0:{}') {
            $StatusOrder = unserialize($StatusOrder);
            if (is_array($StatusOrder)) {
                foreach($StatusOrder as $StatusOrderRow) {
                    if($StatusOrderRow['orderStatusAM'] == $OrderRowData){
                        $OrderReturn = $StatusOrderRow['orderStatusMG'];
                        break;
                    }
                }
            }
        }

        return $OrderReturn;
    }

    /**
     * get status order MG to AM from configs
     *
     * @access private
     * @param $OrderRowData
     * @return string
     * 
     */
    private function getStatusMageToAnyMarketOrderConfig($OrderRowData){
        $StatusOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_mg_am_field', Mage::app()->getStore()->getId());
        $OrderReturn = '';
        if ($StatusOrder && $StatusOrder != 'a:0:{}') {
            $StatusOrder = unserialize($StatusOrder);
            if (is_array($StatusOrder)) {
                foreach($StatusOrder as $StatusOrderRow) {
                    if($StatusOrderRow['orderStatusMG'] == $OrderRowData){
                        $OrderReturn = $StatusOrderRow['orderStatusAM'];
                        break;
                    }

                }
            }
        }else{
            $OrderReturn = 'ERROR: Não há uma configuração válida para '.$OrderRowData;
        }

        return $OrderReturn;
    }

    /**
     * create order in MG
     *
     * @access private
     * @param $products, $customer, $IDAnyMarket, $IDSeqAnyMarket, $infoMetPag, $Billing, $Shipping
     * @return order
     * 
     */
    private function create_order($products, $customer, $IDAnyMarket, $IDSeqAnyMarket, $infoMetPag, $Billing, $Shipping, $shippValue)
    {
        $orderGenerator = Mage::helper('db1_anymarket/ordergenerator');
        $orderGenerator->_storeId = 1;

        $orderGenerator->setShippingMethod('freeshipping_freeshipping');
        $orderGenerator->setPaymentMethod('db1_anymarket');
        $orderGenerator->setAdditionalInformation($infoMetPag);
        $orderGenerator->setShippingValue($shippValue);
        $orderGenerator->setShipAddress($Shipping);
        $orderGenerator->setBillAddress($Billing);
        $orderGenerator->setCustomer($customer);

        $CodOrder = $orderGenerator->createOrder( $products );

        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
        $anymarketorders->setStatus("1");
        $anymarketorders->setNmoStatusInt('Integrado');
        $anymarketorders->setNmoDescError('');
        $anymarketorders->setNmoIdAnymarket($IDAnyMarket);
        $anymarketorders->setNmoIdSeqAnymarket($IDSeqAnyMarket);
        $anymarketorders->setNmoIdOrder($CodOrder);        
        $anymarketorders->save();

        return $CodOrder;
    }

    public function getFeedOrdersFromAnyMarket(){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $returnProd = $this->CallAPICurl("GET", "http://sandbox-api.anymarket.com.br/v2/orders/feeds/", $headers, null);

        if($returnProd['error'] == '1'){
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( 'Error on get feed orders '. $returnProd['return'] );
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
        }else{
            $listOrders = $returnProd['return'];
            foreach ($listOrders as  $order) {
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($order->id, 'nmo_id_seq_anymarket');
                $this->getSpecificOrderFromAnyMarket($anymarketorders->getData('nmo_id_anymarket'), $order->id, $anymarketorders->getData('nmo_id_order'));
            }
        }
    }

    /**
     * get all orders from AM
     *
     * @access public
     * @return array
     * 
     */
    public function getOrdersFromAnyMarket(){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());
        $STATUSIMPORT = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_stauts_order_field', Mage::app()->getStore()->getId());

        $headers = array(
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $startRec = 0;
        $countRec = 1;
        $arrOrderCod = null;
        while ($startRec <= $countRec) {
            $returnOrder = $this->CallAPICurl("GET", $HOST."/rest/api/v1/erp/orders/?start=".$startRec."&pageSize=30", $headers, null);
            $JsonReturn = $returnOrder['return'];
            if($returnOrder['error'] != '0'){
                $startRec = $JsonReturn->start+$JsonReturn->pageSize;
                $countRec = $JsonReturn->count;

                foreach ($JsonReturn->values as  $value) {
                    $IDOrderAnyMarket = $value->idInMarketPlace;
                    if (strpos($STATUSIMPORT, $value->status) !== false) {
                        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($IDOrderAnyMarket, 'nmo_id_anymarket');
                        if( ($anymarketorders->getData('nmo_status_int') == 'ERROR 01') || ($anymarketorders->getData('nmo_status_int') == 'Não integrado (AnyMarket)') ){
                            $anymarketorders->delete();
                        }

                        $idAnyMarket = $value->id;
                        $this->getSpecificOrderFromAnyMarket($idAnyMarket, $IDOrderAnyMarket, $anymarketorders->getData('nmo_id_order'));
                    }
                }
            }else{
                $startRec = 1;
                $countRec = 0;

                if(!isset($JsonReturn->count)){
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc( 'Error on Sinc Orders' );
                    $anymarketlog->setLogId(''); 
                    $anymarketlog->setStatus("1");
                    $anymarketlog->save();
                }

            }
        }
        return $arrOrderCod;

    }

    /**
     * get specific order from AM
     *
     * @access public
     * @param $idAnyMarket, $IDOrderAnyMarket, $IDOrderMagento
     * @return void
     * 
     */
    public function getSpecificOrderFromAnyMarket($idSeqAnyMarket, $IDOrderAnyMarket, $IDOrderMagento){
        $storeID = Mage::app()->getStore()->getId();
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $stateProds = true;
        $returnOrderItens = $this->CallAPICurl("GET", $HOST."/rest/api/v1/erp/orders/".$idSeqAnyMarket, $headers, null);
        $OrderJSON = $returnOrderItens['return'];

        if($IDOrderMagento == null){
            $statusMage = $this->getStatusAnyMarketToMageOrderConfig( $OrderJSON->status );

            if (strpos($statusMage, 'ERROR:') === false) {
                //TRATA OS PRODUTOS
                $_products = array();
                foreach ($OrderJSON->items as  $item) {
                    $productLoaded = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->skuInClient);
                    if($productLoaded){
                        $arrayTMP = array(
                            'product' => $productLoaded->getId(),
                            'price' => $item->unitValue,
                            'qty' => $item->amount,
                        );
                        array_push($_products, $arrayTMP);
                    }else{
                        $productId = $item->productId;

                        $returnProd = Mage::helper('db1_anymarket')->CallAPICurl("GET", $HOST."/rest/api/v1/products/".$productId, $headers, null);
                        $ProdsJSON = $returnProd['return'];
                        if($returnProd['error'] == '0'){
                            Mage::helper('db1_anymarket/product')->createProducts($ProdsJSON);

                            if($item->skuInClient != null){
                                $skuSearch = $item->skuInClient;
                            }else{
                                $skuSearch = $item->productId;
                            }

                            $productLoaded = Mage::getModel('catalog/product')->loadByAttribute('sku', $skuSearch);
                            if(!$productLoaded){
                                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                                $anymarketorders->setStatus("0");
                                $anymarketorders->setNmoStatusInt('ERROR 01');
                                $anymarketorders->setNmoDescError('Erro no produto('.$skuSearch.'), verifique os logs.');
                                $anymarketorders->setNmoIdSeqAnymarket($idSeqAnyMarket);
                                $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
                                $anymarketorders->setNmoIdOrder('');
                                $anymarketorders->setStores(array($storeID));
                                $anymarketorders->save();
                                $stateProds = false;
                            }else{
                                $IDProdCrt = $productLoaded->getId();
                                $arrayTMP = array(
                                    'product' => $IDProdCrt,
                                    'price' => $item->unitValue,
                                    'qty' => $item->amount,
                                );
                                array_push($_products, $arrayTMP);
                            }

                        }else{
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc( 'Error on get product '.$productId );
                            $anymarketlog->setLogId( $productId  );
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->setStatus("1");
                            $anymarketlog->save();                             
                        }
                    }
                }

                //verifica se criou o produto
                if($stateProds){
                    //TRATA O CLIENTE
                    $document = $OrderJSON->buyer->document;
                    if($document != null){
                        try{
                            $AttrToDoc = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_doc_type_field', $storeID));
                            $groupCustomer = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_customer_group_field', $storeID);

                            $email = $OrderJSON->buyer->email->value;
                            $customer = Mage::getModel('customer/customer')
                                        ->getCollection()
                                        ->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId())
                                        ->addFieldToFilter($AttrToDoc, $document)->load()->getFirstItem();

                            $AddressShipBill = null;
                            if($customer->getId() == null){
                                $_DataCustomer = array (
                                    'account' => array(
                                        'firstname' => $OrderJSON->buyer->name,
                                        'lastname' => 'Lastname', //OBRIGATORIO
                                        'email' => $email,
                                        'taxvat' => '',
                                        $AttrToDoc => $document,
                                        'password' => 'a111111',
                                        'default_billing' => '_item1',
                                        'default_shipping' => '_item1',
                                        'store_id' => $storeID,
                                        'website_id' => Mage::app()->getWebsite()->getId(),
                                        'group_id' => $groupCustomer,
                                    ),
                                    'address' => array(
                                       '_item1' => array(
                                            'firstname' => $OrderJSON->buyer->name,
                                            'lastname' => 'Lastname', //OBRIGATORIO
                                            'street' => array(
                                                0 => $OrderJSON->shipping->address,
                                                1 => '',
                                            ),  
                                            'city' => $OrderJSON->shipping->city,
                                            'country_id' => 'BR',
                                            'region_id' => '12',
                                            'region' => $OrderJSON->shipping->state,
                                            'postcode' => $OrderJSON->shipping->zipCode,
                                            'telephone' => $OrderJSON->buyer->phone,
                                        ),
                                    ),
                                );

                                $customer = Mage::helper('db1_anymarket/customergenerator')->createCustomer($_DataCustomer);
                            }else{
                                //PERCORRE OS ENDERECOS PARA VER SE JA HA CADASTRADO O INFORMADO
                                $needRegister = true;
                                foreach ($customer->getAddresses() as $address){
                                    if( ($address->postcode == $OrderJSON->shipping->zipCode) && ($address->street == $OrderJSON->shipping->address) ){
                                        $AddressShipBill = $address;
                                        $needRegister = false;
                                        break;
                                    }
                                }

                                //CRIA O ENDERECO CASO NAO TENHA O INFORMADO
                                if($needRegister){
                                    $address = Mage::getModel('customer/address');

                                    $addressData =  array(
                                        'firstname' => $OrderJSON->buyer->name,
                                        'lastname' => 'Lastname', //OBRIGATORIO
                                        'street' => $OrderJSON->shipping->address,
                                        'city' => $OrderJSON->shipping->city,
                                        'country_id' => 'BR',
                                        'region' => $OrderJSON->shipping->state,
                                        'region_id' => '12',
                                        'postcode' => $OrderJSON->shipping->zipCode,
                                        'telephone' => $OrderJSON->buyer->phone
                                    );

                                    $address->setIsDefaultBilling(1);
                                    $address->setIsDefaultShipping(1);
                                    $address->addData($addressData);
                                    $address->setPostIndex('_item1');
                                    $customer->addAddress($address);
                                    $customer->save();
                                }

                            }

                            $infoMetPag = 'ANYMARKET';
                            foreach ($OrderJSON->payments as  $payment) {
                                $infoMetPag = $payment->method;
                            }

                            if ( $OrderJSON->shipping->zipCode != null ) {
                                $OrderIDMage = $this->create_order($_products, $customer, $IDOrderAnyMarket, $idSeqAnyMarket, $infoMetPag, $AddressShipBill, $AddressShipBill, $OrderJSON->shipValue);
                                $OrderCheck = Mage::getModel('sales/order')->loadByIncrementId($OrderIDMage);

                                if( $OrderCheck->getId() ){
                                    $this->changeStatusOrder($OrderJSON, $OrderIDMage);
                                }
                            }else{
                                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                                $anymarketorders->setStatus("0");
                                $anymarketorders->setNmoStatusInt('ERROR 01');
                                $anymarketorders->setNmoDescError( 'Venda nao possui um endereço de entrega válido.' );
                                $anymarketorders->setNmoIdSeqAnymarket($idSeqAnyMarket);
                                $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
                                $anymarketorders->setStores(array($storeID));
                                $anymarketorders->setNmoIdOrder('');
                                $anymarketorders->save();                        
                            }
                        }catch(Exception $e){
                            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                            $anymarketorders->setStatus("0");
                            $anymarketorders->setNmoStatusInt('ERROR 01');
                            $anymarketorders->setNmoDescError('System: '. $e->getMessage() );
                            $anymarketorders->setNmoIdSeqAnymarket($idSeqAnyMarket);
                            $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
                            $anymarketorders->setStores(array($storeID));
                            $anymarketorders->setNmoIdOrder('');
                            $anymarketorders->save();
                        }
                    }else{
                        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                        $anymarketorders->setStatus("0");
                        $anymarketorders->setNmoStatusInt('ERROR 01');
                        $anymarketorders->setNmoDescError('Cliente com Documento inválido ou em branco.');
                        $anymarketorders->setNmoIdSeqAnymarket($idSeqAnyMarket);
                        $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
                        $anymarketorders->setStores(array($storeID));
                        $anymarketorders->setNmoIdOrder('');
                        $anymarketorders->save();
                    }
                }else{
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc('Not registered product in magento.');
                    $anymarketlog->setLogId( $IDOrderAnyMarket ); 
                    $anymarketlog->setStatus("1");
                    $anymarketlog->save();                    
                }
            }else{
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                $anymarketorders->setStatus("0");
                $anymarketorders->setNmoStatusInt('ERROR 01');
                $anymarketorders->setNmoDescError($statusMage);
                $anymarketorders->setNmoIdSeqAnymarket($idSeqAnyMarket);
                $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
                $anymarketorders->setNmoIdOrder('');
                $anymarketorders->setStores(array($storeID));
                $anymarketorders->save();

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( $statusMage );
                $anymarketlog->setLogId( $IDOrderAnyMarket ); 
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }
        }else{
            $this->changeStatusOrder($OrderJSON, $IDOrderMagento);
        }

    }

    /**
     * change status order
     *
     * @access private
     * @param $JSON, $IDOrderMagento
     * @return void
     * 
     */
    private function changeStatusOrder($JSON, $IDOrderMagento){
        $StatusPedAnyMarket = $JSON->status;
        $statusMage = $this->getStatusAnyMarketToMageOrderConfig( $StatusPedAnyMarket );

        if (strpos($statusMage, 'ERROR:') === false) {
            $order = Mage::getModel('sales/order')->loadByIncrementId( $IDOrderMagento );
            $itemsarray = null;
            if($JSON->invoice){
                if( $order->canInvoice() ){
                    $nfe = $JSON->invoice->accessKey;
                    $dateNfe = $JSON->invoice->date;

                    $DateTime = strtotime($dateNfe);
                    $fixedDate = date('d/m/Y H:i:s', $DateTime);

                    $orderItems = $order->getAllItems();
                    foreach ($orderItems as $_eachItem) {
                        $opid = $_eachItem->getId();
                        $qty = $_eachItem->getQtyOrdered();
                        $itemsarray[$opid] = $qty;
                    }

                    if($order->canInvoice()) {
                        $nfeString = 'nfe:'.$nfe.', emissao:'.$fixedDate;
                        Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $itemsarray ,$nfeString ,0,0);
                    }
                }

            }

            if($JSON->tracking){
                if( $order->canShip() ){           
                    $TrNumber = $JSON->tracking->number;
                    $TrCarrier = strtolower($JSON->tracking->carrier);

                    $shipmentId = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $itemsarray ,'Create by AnyMarket' ,false,1);

                    $TracCodeArr = Mage::getModel('sales/order_shipment_api')->getCarriers($order->getIncrementId());
                    if(isset($TracCodeArr[$TrCarrier]) ){
                        $trackmodel = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId, $TrCarrier, $TrCarrier, $TrNumber);
                    }else{
                        $arrVar = array_keys($TracCodeArr);
                        $trackmodel = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId,  array_shift($arrVar), 'Não Econtrado('.$TrCarrier.')', $TrNumber);
                    }
                }
            }

            if($statusMage == Mage_Sales_Model_Order::STATE_COMPLETE){
                $order->setData('state', "complete");
                $order->setStatus("complete");       
                $history = $order->addStatusHistoryComment('Finalizado pelo AnyMarket.', false);
                $history->setIsCustomerNotified(false);
                $order->save();
            }else{
                $order->setState($statusMage, true);
                $order->save();
            }

            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($IDOrderMagento, 'nmo_id_order');
            $anymarketorders->setStatus("0");
            $anymarketorders->setNmoStatusInt('Integrado');
            $anymarketorders->setNmoDescError('');
            $anymarketorders->setNmoIdSeqAnymarket( $JSON->id );
            $anymarketorders->setNmoIdAnymarket( $JSON->idInMarketPlace );
            $anymarketorders->setNmoIdOrder($IDOrderMagento);
            $anymarketorders->save();

        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( $statusMage );
            $anymarketlog->setLogId( $IDOrderMagento ); 
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
        }
    }

    /**
     * get invoice order
     *
     * @access public
     * @param $Order
     * @return array
     * 
     */
    public function getInvoiceOrder($Order){
        $nfeID = "";
        $date = "";
        if ($Order->hasInvoices()) {
            foreach ($Order->getInvoiceCollection() as $inv) {
                $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId( $inv->getIncrementId() );
                foreach ($invoice->getCommentsCollection() as $item) {
                    $CommentCurr = $item->getComment();

                    $nfeCount = strpos($CommentCurr, 'nfe:');
                    $emissaoCount = strpos($CommentCurr, 'emiss');
                    if( (strpos($CommentCurr, 'nfe:') !== false) && (strpos($CommentCurr, 'emiss') !== false) ) {
                        $caracts = array("/", "-", ".");
                        $nfeTmp = str_replace($caracts, "", $CommentCurr );
                        $nfeID = substr( $nfeTmp, $nfeCount+4, 44);

                        $date = substr( $CommentCurr, $emissaoCount+8, 19);
                        $dateTmp = str_replace("/", "-", $date );
                        $date = gmdate('Y-m-d\TH:i:s\Z', strtotime( $dateTmp ));
                    }
                }
            }
        }

        return array("number" => $nfeID, "date" => $date );
    }

    /**
     * get tracking order
     *
     * @access public
     * @param $Order
     * @return array
     * 
     */
    public function getTrackingOrder($Order){
        $TrackNum = '';
        $TrackCode = '';
        $TrackCreate = '';
        $dateTrack = '';

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                                                    ->setOrderFilter($Order)
                                                    ->load();
        foreach ($shipmentCollection as $shipment){
            foreach($shipment->getAllTracks() as $tracknum){
                $TrackNum = $tracknum->getNumber();
                $TrackCode = $tracknum->getCarrierCode();
                $TrackCreate = $tracknum->getCreatedAt();

                $dateTmp = str_replace("/", "-", $TrackCreate );
                $dateTrack = gmdate('Y-m-d\TH:i:s\Z', strtotime( $dateTmp ));
            }
        }

        return array("number" => $TrackNum, "carrier" => $TrackCode, "date" => $dateTrack, "url" => "");
    }

    /**
     * update order in AM
     *
     * @access public
     * @param $Order
     * @return void
     * 
     */
    public function updateOrderAnyMarket($Order){
        $storeID = Mage::app()->getStore()->getId();
        $ImportOrderSession = Mage::getSingleton('core/session')->getImportOrdersVariable();
        if( $ImportOrderSession != 'false' ) {
            $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
            $idOrder = $Order->getIncrementId();
            $status = $Order->getStatus();
            $anymarketorderupdt = Mage::getModel('db1_anymarket/anymarketorders')->load($idOrder, 'nmo_id_order');
            if( ($ConfigOrder == 0) || ($anymarketorderupdt->getData('nmo_status_int') == 'Integrado') ){

                $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
                $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID); 

                $headers = array(
                    "Content-type: application/json",
                    "Accept: */*",
                    "gumgaToken: ".$TOKEN
                );

                if( ($anymarketorderupdt->getData('nmo_id_order') != null) && ($anymarketorderupdt->getData('nmo_id_anymarket') != null) ){
                    $statuAM = $this->getStatusMageToAnyMarketOrderConfig($status);
                    if (strpos($statuAM, 'ERROR:') === false) {
                        $params = array(
                          "status" => $statuAM
                        );

                        $invoiceData = $this->getInvoiceOrder($Order);
                        $trackingData = $this->getTrackingOrder($Order);

                        if( ($invoiceData['number'] != "") && ($statuAM == 'INVOICED') ){
                            $params["invoice"] = $invoiceData;
                        }else{
                            $params["tracking"] = $trackingData;
                        }

                        $IDOrderAnyMarket = $anymarketorderupdt->getData('nmo_id_seq_anymarket');
                        $returnOrder = $this->CallAPICurl("PUT", $HOST."/rest/api/v1/erp/orders/".$IDOrderAnyMarket, $headers, $params);

                        if($returnOrder['error'] == '1'){
                            $anymarketorderupdt->setStatus("0");
                            $anymarketorderupdt->setNmoStatusInt('ERROR 02');
                            $anymarketorderupdt->setNmoDescError($returnOrder['return']);
                            $anymarketorderupdt->save();
                        }
                        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                        $anymarketlog->setLogDesc( $returnOrder['return'] );
                        $anymarketlog->setLogId( $idOrder );
                        $anymarketlog->setLogJson( $returnOrder['json'] );
                        $anymarketlog->setStores(array($storeID));
                        $anymarketlog->setStatus("1");
                        $anymarketlog->save();

                    }else{
                        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                        $anymarketlog->setStatus("1");
                        $anymarketlog->setLogDesc( $statuAM );
                        $anymarketlog->setLogId( $idOrder );
                        $anymarketlog->setStores(array($storeID));
                        $anymarketlog->save();

                        $anymarketorderupdt->setStatus("0");
                        $anymarketorderupdt->setNmoStatusInt('ERROR 02');
                        $anymarketorderupdt->setNmoDescError( $statuAM );
                        $anymarketorderupdt->save();
                    }
                }else{
                    $this->sendOrderToAnyMarket($idOrder, $HOST, $TOKEN);
                }
            }
        }

    }


    /**
     * send order to AM
     *
     * @access private
     * @param $Order, $HOST, $TOKEN
     * @return void
     * 
     */
    private function sendOrderToAnyMarket($idOrder, $HOST, $TOKEN){
        $storeID = Mage::app()->getStore()->getId();
        $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID); 
        if($ConfigOrder == 0){
            $Order = Mage::getModel('sales/order')->setStoreId($storeID)->loadByIncrementId( $idOrder );

            //TRATA OS ITEMS
            $orderedItems = $Order->getAllVisibleItems();
            $orderedProductIds = array();

            foreach ($orderedItems as $item) {
                $orderedProductIds[] = array(
                    "skuId" => $item->getData('sku'),
                    "amount" => $item->getData('qty_ordered'),
                    "unitValue" => $item->getData('original_price'),
                    "discountValue" => $item->getData('discount_amount')
                );
            }

            //OBTEM OS DADOS DO PAGAMENTO
            $payment = $Order->getPayment();

            //OBTEM OS DADOS DA ENTREGA
            $shipping = $Order->getShippingAddress();

            $docField = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_doc_type_field', $storeID));
            if($Order->getCustomerIsGuest()){
                $customer = Mage::getModel("customer/customer")->load($Order->getCustomerId());
                $docData = $customer->getData( $docField );
            }else{
                $docData = '';
            }

            $statusOrder = $Order->getStatus();
            if($statusOrder == 'pending'){
                $statuAM = $this->getStatusMageToAnyMarketOrderConfig('new');
            }else{
                $statuAM = $this->getStatusMageToAnyMarketOrderConfig($statusOrder);
            }

            if( (strpos($statuAM, 'ERROR:') === false) && ($statuAM != '') ) {
                $params = array(
                    'id' => $idOrder,
                    "creation" => gmdate('Y-m-d\TH:i:s\Z', strtotime( $Order->getData('created_at') )),
                    "status" =>  $statuAM,
                    "statusInMarketplace" => $statuAM,
                    "url" => null,
                    "shipping" => array(
                        "city" => $shipping->getCity(),
                        "state" => $shipping->getRegion(),
                        "country" => $shipping->getCountry(),
                        "address" => $shipping->getStreetFull(), //$shipping->getStreet(4)
                        "zipCode" => $shipping->getPostcode()
                    ),
                    "buyer" => array(
                        "id" => '123456',
                        "name" => $Order->getCustomerFirstname()." ".$Order->getCustomerLastname(),
                        "email" => $Order->getCustomerEmail(),
                        "document" =>  $docData,
                        "documentType" => "OTHER",
                        "phone" => $shipping->getTelephone(),
                    ),
                    "items" => $orderedProductIds,
                    "payments" => array(
                                    array(
                                        "method" => $payment->getMethodInstance()->getTitle(),
                                        "status" => "Pago",
                                        "value" => $Order->getBaseGrandTotal()
                                    ),
                    ),
                    "discountValue" => $Order->getDiscountAmount(),
                    "shipValue" => $Order->getShippingAmount(),
                    "grossValue" => $Order->getBaseGrandTotal(),
                    "totalValue" => $Order->getBaseGrandTotal()
                );

                $arrTracking = $this->getTrackingOrder($Order);
                $arrInvoice = $this->getInvoiceOrder($Order);

                if($arrTracking["number"] != ''){
                    $params["tracking"] = $arrTracking;
                };

                if($arrInvoice["number"] != ''){
                    $params["invoice"] = $arrInvoice;
                };

                $headers = array(
                    "Content-type: application/json",
                    "Accept: */*",
                    "gumgaToken: ".$TOKEN
                );

                $returnOrder = $this->CallAPICurl("POST", $HOST."/rest/api/v1/ecommerce/orders/", $headers, $params);

                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($idOrder, 'nmo_id_order');
                if($anymarketorders->getData('nmo_id_order') == null){
                    $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                }

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( $returnOrder['return'] );

                $anymarketorders->setStatus("1");
                if($returnOrder['error'] == '1'){
                    $anymarketorders->setNmoStatusInt('ERROR 02');
                    $anymarketorders->setNmoDescError($returnOrder['return']);
                }else{
                    $retOrderJSON = $returnOrder['return'];
                    $anymarketorders->setNmoStatusInt('Integrado');
                    $anymarketorders->setNmoDescError('');
                    $anymarketorders->setNmoIdAnymarket( $retOrderJSON->marketPlaceId );
                    $anymarketorders->setNmoIdSeqAnymarket( $retOrderJSON->id );
                    
                    $anymarketlog->setLogId( $retOrderJSON->marketPlaceId );
                }

                $anymarketlog->setStores(array(Mage::app()->getStore()->getId()));
                $anymarketlog->setLogJson( $returnOrder['json'] );
                $anymarketlog->setStatus("1");
                $anymarketlog->save();

            }else{
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($idOrder, 'nmo_id_order');
                if($anymarketorders->getData('nmo_id_order') == null){
                    $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                }

                $anymarketorders->setNmoStatusInt('ERROR 02');
                if($statuAM != ''){
                    $anymarketorders->setNmoDescError( $statuAM );
                }else{
                    $anymarketorders->setNmoDescError( 'Status new não foi referenciado.' );
                }
            }

            $anymarketorders->setNmoIdOrder($idOrder);
            $anymarketorders->save();
        }

    }


    /**
     * send order to AM
     *
     * @access public
     * @param $idOrder, $HOST, $TOKEN
     * @return void
     * 
     */
    public function listOrdersFromAnyMarketMagento(){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());
        $STATUSIMPORT = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_stauts_order_field', Mage::app()->getStore()->getId());

        $headers = array(
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $startRec = 0;
        $countRec = 1;
        $arrOrderCod = null;

        $contPed = 0;
        while ($startRec <= $countRec) {
            $returnOrder = $this->CallAPICurl("GET", $HOST."/rest/api/v1/erp/orders/?start=".$startRec."&pageSize=30", $headers, null);
            $JsonReturn = $returnOrder['return'];

            $startRec = $JsonReturn->start+$JsonReturn->pageSize;
            $countRec = $JsonReturn->count;

            foreach ($JsonReturn->values as  $value) {
                $IDOrderAnyMarket = $value->idInMarketPlace;

                if (strpos($STATUSIMPORT, $value->status) !== false) {
                    $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($IDOrderAnyMarket, 'nmo_id_anymarket');
                    if($anymarketorders->getData('nmo_id_anymarket') == null){
                        $idAnyMarket = $value->id;

                        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                        $anymarketorders->setStatus("0");
                        $anymarketorders->setNmoStatusInt('Não integrado (AnyMarket)');
                        $anymarketorders->setNmoDescError('');
                        $anymarketorders->setNmoIdSeqAnymarket($idAnyMarket);
                        $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
                        $anymarketorders->setNmoIdOrder('');
                        $anymarketorders->save();

                        $contPed = $contPed+1;
                    }

                }
            }
        }

        $salesCollection = Mage::getModel("sales/order")->getCollection();
        foreach($salesCollection as $order){
            $orderId = $order->getIncrementId();

            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($orderId, 'nmo_id_order');
            if($anymarketorders->getData('nmo_id_order') == null){
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                $anymarketorders->setStatus("0");
                $anymarketorders->setNmoStatusInt('Não integrado (Magento)');
                $anymarketorders->setNmoDescError('');
                $anymarketorders->setNmoIdSeqAnymarket('');
                $anymarketorders->setNmoIdAnymarket('');
                $anymarketorders->setNmoIdOrder( $orderId );
                $anymarketorders->save();

                $contPed = $contPed+1;
            }
        }

        return $contPed;

    }

}
