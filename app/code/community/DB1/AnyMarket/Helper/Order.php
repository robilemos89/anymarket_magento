<?php

class DB1_AnyMarket_Helper_Order extends DB1_AnyMarket_Helper_Data
{

    /**
     * get status order AM to MG from configs
     *
     * @param $OrderRowData
     * @return string
     */
    private function getStatusAnyMarketToMageOrderConfig($OrderRowData){
        if($OrderRowData == null){
            $OrderRowData = "new";
        }

        $storeID = $this->getCurrentStoreView();
        $StatusOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_am_mg_field', $storeID);
        $OrderReturn = 'ERROR: 1 Não há uma configuração válida para '.$OrderRowData;
        $StateReturn = "";
        if ($StatusOrder && $StatusOrder != 'a:0:{}') {
            $StatusOrder = unserialize($StatusOrder);
            if (is_array($StatusOrder)) {
                foreach($StatusOrder as $StatusOrderRow) {
                    if($StatusOrderRow['orderStatusAM'] == $OrderRowData){
                        $OrderReturn = $StatusOrderRow['orderStatusMG'];
                        $statuses = Mage::getModel('sales/order_status')->getCollection()->joinStates()
                            ->addStatusFilter($OrderReturn);

                        $StateReturn = $statuses->getFirstItem()->getData('state');
                        break;
                    }

                }
            }
        }

        return array("status" => $OrderReturn, "state" => $StateReturn);
    }

    /**
     * create log order magento
     *
     * @param $fieldFilter
     * @param $fieldDataFilter
     * @param $statusInt
     * @param $descError
     * @param $idSeqAnyMarket
     * @param $IDOrderAnyMarket
     * @param $nmoIdOrder
     * @param $storeID
     */
    private function saveLogOrder($fieldFilter, $fieldDataFilter, $statusInt, $descError, $idSeqAnyMarket, $IDOrderAnyMarket, $nmoIdOrder, $storeID){
        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->setStoreId($storeID);
        $anymarketorders->load($fieldDataFilter, $fieldFilter);

        $anymarketorders->setStatus("0");
        $anymarketorders->setNmoStatusInt($statusInt);
        $anymarketorders->setNmoDescError($descError);
        $anymarketorders->setNmoIdSeqAnymarket( $idSeqAnyMarket );
        $anymarketorders->setNmoIdAnymarket( $IDOrderAnyMarket );
        $anymarketorders->setNmoIdOrder($nmoIdOrder);
        $anymarketorders->setStores(array($storeID));
        $anymarketorders->save();

        if( $descError != "" ) {
            Mage::getSingleton('adminhtml/session')->addError($descError);
        }
    }

    /**
     * get status order MG to AM from configs
     *
     * @param $OrderRowData
     * @return string
     */
    private function getStatusMageToAnyMarketOrderConfig($OrderRowData){
        if($OrderRowData == null){
            $OrderRowData = "new";
        }

        $StatusOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_mg_am_field', $this->getCurrentStoreView());
        $OrderReturn = 'ERROR: 2 Não há uma configuração válida para '.$OrderRowData;
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
        }

        return $OrderReturn;
    }

    /**
     * create order in Magento
     *
     * @param $anymarketordersSpec
     * @param $products
     * @param $customer
     * @param $IDAnyMarket
     * @param $IDSeqAnyMarket
     * @param $infoMetPag
     * @param $Billing
     * @param $Shipping
     * @param $shippValue
     * @return integer
     */
    private function create_order($anymarketordersSpec, $products, $customer, $IDAnyMarket, $IDSeqAnyMarket, $infoMetPag, $Billing, $Shipping, $shippValue, $storeID)
    {
        if( ($anymarketordersSpec->getData('nmo_id_anymarket') == null) ||
            ($anymarketordersSpec->getData('nmo_status_int') == "ERROR 01") ) {
            $AttrToDoc = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_doc_type_field', $storeID));

            $orderGenerator = Mage::helper('db1_anymarket/ordergenerator');
            $orderGenerator->_storeId = $storeID;

            $orderGenerator->setShippingMethod('freeshipping_freeshipping');
            $orderGenerator->setPaymentMethod('db1_anymarket');
            $orderGenerator->setAdditionalInformation($infoMetPag);
            $orderGenerator->setShippingValue($shippValue);
            $orderGenerator->setShipAddress($Shipping);
            $orderGenerator->setBillAddress($Billing);
            $orderGenerator->setCustomer($customer);
            $orderGenerator->setCpfCnpj($customer->getData($AttrToDoc));

            $CodOrder = $orderGenerator->createOrder($products);

            $this->saveLogOrder('nmo_id_anymarket', $IDAnyMarket, 'Integrado', '', $IDSeqAnyMarket, $IDAnyMarket, $CodOrder, $storeID);

            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc('Order Created: ' . $CodOrder . ' ID Anymarket: ' . $IDAnyMarket);
            $anymarketlog->setStatus("1");
            $anymarketlog->setStores(array($storeID));
            $anymarketlog->save();
        }else{
            $CodOrder = $anymarketordersSpec->getData('nmo_id_order');
        }

        return $CodOrder;
    }

    /**
     * get all order in feed AnyMarket
     */
    public function getFeedOrdersFromAnyMarket(){
        $storeID = $this->getCurrentStoreView();
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $returnProd = $this->CallAPICurl("GET", $HOST."/v2/orders/feeds?limit=100", $headers, null);

        if($returnProd['error'] == '1'){
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( 'Error on get feed orders '. $returnProd['return'] );
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
        }else{
            $listOrders = $returnProd['return'];

            foreach ($listOrders as  $order) {
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($order->id, 'nmo_id_seq_anymarket');
                if( $anymarketorders->getData('nmo_id_anymarket') != null ){
                    $this->getSpecificOrderFromAnyMarket($anymarketorders->getData('nmo_id_seq_anymarket'), '', $storeID);
                }else{
                    $this->getSpecificOrderFromAnyMarket($order->id, $order->token, $storeID);                    
                }
            }
        }
    }

    /**
     * get specific order from AnyMarket
     *
     * @param $idSeqAnyMarket
     * @param $tokenFeed
     * @param $storeID
     */
    public function getSpecificOrderFromAnyMarket($idSeqAnyMarket, $tokenFeed, $storeID){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);
        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $stateProds = true;
        $returnOrderItens = $this->CallAPICurl("GET", $HOST."/v2/orders/".$idSeqAnyMarket, $headers, null);
        if($returnOrderItens['error'] == '0'){
            $OrderJSON = $returnOrderItens['return'];
            $IDOrderAnyMarket = $OrderJSON->marketPlaceId;
            $anymarketordersSpec = Mage::getModel('db1_anymarket/anymarketorders');
            $anymarketordersSpec->load($idSeqAnyMarket, 'nmo_id_seq_anymarket');


            if( ($anymarketordersSpec->getData('nmo_id_anymarket') == null) ||
                ($anymarketordersSpec->getData('nmo_status_int') == "Não integrado (AnyMarket)") ||
                ($anymarketordersSpec->getData('nmo_status_int') == "ERROR 01") ){
                $STATUSIMPORT = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_stauts_order_field', $storeID);
                if (strpos($STATUSIMPORT, $OrderJSON->status) !== false) {
                    $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
                    if($ConfigOrder == 1) {
                        $statsConfig = $this->getStatusAnyMarketToMageOrderConfig($OrderJSON->status);
                        $statusMage = $statsConfig["status"];

                        if (strpos($statusMage, 'ERROR:') === false) {
                            //TRATA OS PRODUTOS
                            $_products = array();
                            foreach ($OrderJSON->items as $item) {
                                $productLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $item->sku->partnerId);
                                if ($productLoaded) {
                                    $arrayTMP = array(
                                        'product' => $productLoaded->getId(),
                                        'price' => $item->unit,
                                        'qty' => $item->amount,
                                    );
                                    array_push($_products, $arrayTMP);
                                } else {
                                    if ($anymarketordersSpec->getData('nmo_id_anymarket') == null) {
                                        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                                    } else {
                                        $anymarketorders = $anymarketordersSpec;
                                    }

                                    $this->saveLogOrder('nmo_id_seq_anymarket',
                                        $idSeqAnyMarket,
                                        'ERROR 01',
                                        Mage::helper('db1_anymarket')->__('Product is not registered') . ' (SKU: ' . $item->sku->partnerId . ')',
                                        $idSeqAnyMarket,
                                        $IDOrderAnyMarket,
                                        '',
                                        $storeID);

                                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                    $anymarketlog->setLogDesc(Mage::helper('db1_anymarket')->__('Product is not registered') . ' (Order: ' . $idSeqAnyMarket . ', SKU : ' . $item->sku->partnerId . ')');
                                    $anymarketlog->setStores(array($storeID));
                                    $anymarketlog->setStatus("1");
                                    $anymarketlog->save();

                                    $this->addMessageInBox(Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
                                        Mage::helper('db1_anymarket')->__('Error synchronizing order number: ') . "Anymarket(" . $IDOrderAnyMarket . ") <br/>" .
                                        Mage::helper('db1_anymarket')->__('Product is not registered') . ' (SKU: ' . $item->sku->partnerId . ')',
                                        '');
                                    $stateProds = false;
                                    break;
                                }
                            }

                            //verifica se criou o produto
                            if ($stateProds) {
                                //TRATA O CLIENTE
                                $document = null;
                                if (isset($OrderJSON->buyer->document)) {
                                    $document = $OrderJSON->buyer->document;
                                }

                                if ($document != null) {
                                    try {
                                        $AttrToDoc = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_doc_type_field', $storeID));
                                        $groupCustomer = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_customer_group_field', $storeID);

                                        $email = $OrderJSON->buyer->email;
                                        $customer = Mage::getModel('customer/customer')
                                            ->getCollection()
                                            ->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId())
                                            ->addFieldToFilter($AttrToDoc, $document)->load()->getFirstItem();

                                        $AddressShipBill = null;

                                        $firstName = $OrderJSON->buyer->name;
                                        $lastName = 'Lastname';
                                        if ($firstName != '') {
                                            $nameComplete = explode(" ", $firstName);

                                            $lastNameP = array_slice($nameComplete, 1);
                                            $lastNameImp = implode(" ", $lastNameP);

                                            $firstName = array_shift($nameComplete);
                                            $lastName = $lastNameImp == '' ? 'Lastname' : $lastNameImp;
                                        }

                                        if ($customer->getId() == null) {
                                            $_DataCustomer = array(
                                                'account' => array(
                                                    'firstname' => $firstName,
                                                    'lastname' => $lastName,
                                                    'email' => $email,
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
                                                        'firstname' => $firstName,
                                                        'lastname' => $lastName,
                                                        'street' => array(
                                                            0 => (isset($OrderJSON->shipping->street)) ? $OrderJSON->shipping->street : $OrderJSON->shipping->address,
                                                            1 => (isset($OrderJSON->shipping->number)) ? $OrderJSON->shipping->number : '',
                                                            2 => (isset($OrderJSON->shipping->neighborhood)) ? $OrderJSON->shipping->neighborhood : '',
                                                            3 => (isset($OrderJSON->shipping->comment)) ? $OrderJSON->shipping->comment : '',
                                                        ),
                                                        'city' => $OrderJSON->shipping->city,
                                                        'country_id' => 'BR',
                                                        'region_id' => '12', //BRASIL
                                                        'region' => $OrderJSON->shipping->state,
                                                        'postcode' => $OrderJSON->shipping->zipCode,
                                                        'telephone' => $OrderJSON->buyer->phone,
                                                    ),
                                                ),
                                            );

                                            $customerRet = Mage::helper('db1_anymarket/customergenerator')->createCustomer($_DataCustomer);
                                            $customer = $customerRet['customer'];
                                            $AddressShipBill = $customerRet['addr'];
                                        } else {
                                            //PERCORRE OS ENDERECOS PARA VER SE JA HA CADASTRADO O INFORMADO
                                            $needRegister = true;
                                            foreach ($customer->getAddresses() as $address) {
                                                if (($address->postcode == $OrderJSON->shipping->zipCode) && ($address->street == $OrderJSON->shipping->address)) {
                                                    $AddressShipBill = $address;
                                                    $needRegister = false;
                                                    break;
                                                }
                                            }

                                            //CRIA O ENDERECO CASO NAO TENHA O INFORMADO
                                            if ($needRegister) {
                                                $address = Mage::getModel('customer/address');

                                                $addressData = array(
                                                    'firstname' => $firstName,
                                                    'lastname' => $lastName,
                                                    'street' => array(
                                                        0 => (isset($OrderJSON->shipping->street)) ? $OrderJSON->shipping->street : $OrderJSON->shipping->address,
                                                        1 => (isset($OrderJSON->shipping->number)) ? $OrderJSON->shipping->number : '',
                                                        2 => (isset($OrderJSON->shipping->neighborhood)) ? $OrderJSON->shipping->neighborhood : '',
                                                        3 => (isset($OrderJSON->shipping->comment)) ? $OrderJSON->shipping->comment : '',
                                                    ),
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
                                        foreach ($OrderJSON->payments as $payment) {
                                            $infoMetPag = $payment->method;
                                        }

                                        if ($OrderJSON->shipping->zipCode != null) {
                                            $OrderIDMage = $this->create_order($anymarketordersSpec, $_products, $customer, $IDOrderAnyMarket, $idSeqAnyMarket, $infoMetPag, $AddressShipBill, $AddressShipBill, $OrderJSON->freight, $storeID);
                                            $OrderCheck = Mage::getModel('sales/order')->loadByIncrementId($OrderIDMage);

                                            $this->changeFeedOrder($HOST, $headers, $idSeqAnyMarket, $tokenFeed);

                                            if ($OrderCheck->getId()) {
                                                $this->changeStatusOrder($OrderJSON, $OrderIDMage);
                                            }
                                        } else {
                                            $this->saveLogOrder('nmo_id_seq_anymarket',
                                                $idSeqAnyMarket,
                                                'ERROR 01',
                                                Mage::helper('db1_anymarket')->__('Sale not have a valid shipping address.'),
                                                $idSeqAnyMarket,
                                                $IDOrderAnyMarket,
                                                '',
                                                $storeID);

                                            $this->addMessageInBox(Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
                                                Mage::helper('db1_anymarket')->__('Error synchronizing order number: ') . "Anymarket(" . $IDOrderAnyMarket . ") <br/>" .
                                                Mage::helper('db1_anymarket')->__('Sale not have a valid shipping address.'),
                                                '');
                                        }
                                    } catch (Exception $e) {
                                        $this->saveLogOrder('nmo_id_seq_anymarket',
                                            $idSeqAnyMarket,
                                            'ERROR 01',
                                            'System: ' . $e->getMessage(),
                                            $idSeqAnyMarket,
                                            $IDOrderAnyMarket,
                                            '',
                                            $storeID);

                                    }
                                } else {
                                    $this->saveLogOrder('nmo_id_seq_anymarket',
                                        $idSeqAnyMarket,
                                        'ERROR 01',
                                        Mage::helper('db1_anymarket')->__('Customer invalid or blank document.'),
                                        $idSeqAnyMarket,
                                        $IDOrderAnyMarket,
                                        '',
                                        $storeID);

                                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                    $anymarketlog->setLogDesc('Error on import Order: ' . Mage::helper('db1_anymarket')->__('Customer invalid or blank document.'));
                                    $anymarketlog->setStatus("1");
                                    $anymarketlog->setStores(array($storeID));
                                    $anymarketlog->save();

                                    $this->addMessageInBox(Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
                                        Mage::helper('db1_anymarket')->__('Error synchronizing order number: ') . "Anymarket(" . $IDOrderAnyMarket . ") <br/>" .
                                        Mage::helper('db1_anymarket')->__('Customer invalid or blank document.'),
                                        '');
                                }
                            }
                        } else {
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc($statusMage);
                            $anymarketlog->setLogId($IDOrderAnyMarket);
                            $anymarketlog->setStatus("1");
                            $anymarketlog->save();
                        }

                        if ($tokenFeed != null) {
                            $paramFeed = array(
                                "token" => $tokenFeed
                            );

                            $this->CallAPICurl("PUT", $HOST . "/rest/api/v2/orders/feeds/" . $idSeqAnyMarket, $headers, $paramFeed);
                        }
                    }
                }
            }else{
                $STATUSIMPORT = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_stauts_order_field', $storeID);
                if (strpos($STATUSIMPORT, $OrderJSON->status) !== false) {
                    if ($anymarketordersSpec->getData('nmo_id_order') != null) {
                        $this->changeStatusOrder($OrderJSON, $anymarketordersSpec->getData('nmo_id_order'));
                    }
                }
            }
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( 'Error on import Order: '.$idSeqAnyMarket.'  '.$returnOrderItens['return'] );
            $anymarketlog->setStatus("1");
            $anymarketlog->save();

            $this->addMessageInBox(Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
                                   Mage::helper('db1_anymarket')->__('Error synchronizing order number: ')."Anymarket(".$idSeqAnyMarket.")",
                                   '');
        }
    }

    /**
     * change status feed order
     *
     * @param $HOST
     * @param $headers
     * @param $IDFeed
     * @param $tokenFeed
     */
    private function changeFeedOrder($HOST, $headers, $IDFeed, $tokenFeed){
        if($tokenFeed != 'notoken'){
            $paramsFeeds = array(
                "token" => $tokenFeed
            );

            $returnChangeTrans = $this->CallAPICurl("PUT", $HOST."/v2/orders/feeds/".$IDFeed, $headers, $paramsFeeds);
            if($returnChangeTrans['error'] == '1'){
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error update feed order.'));
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }
        }

    }

    /**
     * change status order
     *
     * @param $JSON
     * @param $IDOrderMagento
     */
    private function changeStatusOrder($JSON, $IDOrderMagento){
        $storeID = $this->getCurrentStoreView();
        $StatusPedAnyMarket = $JSON->status;

        $statsConfig = $this->getStatusAnyMarketToMageOrderConfig( $StatusPedAnyMarket );
        $stateMage  = $statsConfig["state"];
        $statusMage = $statsConfig["status"];

        if (strpos($statusMage, 'ERROR:') === false) {
            Mage::getSingleton('core/session')->setImportOrdersVariable('false');

            $order = Mage::getModel('sales/order')->loadByIncrementId( $IDOrderMagento );
            $itemsarray = null;
            if(isset($JSON->invoice)){
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

                    if( !$order->hasInvoices() ) {
                        $nfeString = 'nfe:'.$nfe.', emissao:'.$fixedDate;
                        Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $itemsarray ,$nfeString ,0,0);
                    }
                }
            }

            if(isset($JSON->tracking)){
                if( $order->canShip() && !$order->hasShipments() ){
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

            if($stateMage != Mage_Sales_Model_Order::STATE_NEW){
                if($stateMage == Mage_Sales_Model_Order::STATE_COMPLETE){
                    $history = $order->addStatusHistoryComment('Finalizado pelo AnyMarket.', false);
                    $history->setIsCustomerNotified(false);
                }
                $order->setData('state', $stateMage);
                $order->setStatus($statusMage, true);
                $order->save();
            }

            $this->saveLogOrder('nmo_id_anymarket',
                                 $JSON->marketPlaceId, 
                                 'Integrado', 
                                 '', 
                                 $JSON->id, 
                                 $JSON->marketPlaceId, 
                                 $IDOrderMagento, 
                                 $storeID);

            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc('Order Updated: ' . $IDOrderMagento . ' ID Anymarket: ' . $JSON->marketPlaceId . ' Status: ' . $statusMage);
            $anymarketlog->setStatus("1");
            $anymarketlog->setStores(array($storeID));
            $anymarketlog->save();

            Mage::getSingleton('core/session')->setImportOrdersVariable('true');
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( $statusMage );
            $anymarketlog->setLogId( $IDOrderMagento ); 
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
/*
            $this->addMessageInBox(Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
                                   Mage::helper('db1_anymarket')->__('Error synchronizing order number: ')."Magento(".$IDOrderMagento.") <br/>".
                                   $statusMage,
                                   '');
*/
        }
    }

    /**
     * get invoice order
     *
     * @param $Order
     * @return array
     */
    public function getInvoiceOrder($Order){
        $nfeID = "";
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

        return array("number" => $nfeID, "date" => $date, "accessKey" => $nfeID);
    }

    /**
     * get tracking order
     *
     * @param $Order
     * @return array
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
     * @param $Order
     */
    public function updateOrderAnyMarket($Order){
        $storeID = $this->getCurrentStoreView();
        $ImportOrderSession = Mage::getSingleton('core/session')->getImportOrdersVariable();
        if( $ImportOrderSession != 'false' ) {
            $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
            $idOrder = $Order->getIncrementId();
            $status = $Order->getStatus();
            $anymarketorderupdt = Mage::getModel('db1_anymarket/anymarketorders')->load($idOrder, 'nmo_id_order');

            if( ($ConfigOrder == 0) ||
                ($anymarketorderupdt->getData('nmo_status_int') == 'Integrado') || 
                ($anymarketorderupdt->getData('nmo_status_int') == 'ERROR 02')){

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

                        if ($invoiceData['number'] != '') {
                            $params["invoice"] = $invoiceData;
                        }

                        if ($trackingData['number'] != '') {
                            $params["tracking"] = $trackingData;
                        }

                        if( ($statuAM == "CONCLUDED" || $statuAM == "CANCELED" || $statuAM == "PAID_WAITING_SHIP") ||
                            (isset($params["tracking"]) || isset($params["invoice"])) ){
                            $IDOrderAnyMarket = $anymarketorderupdt->getData('nmo_id_seq_anymarket');

                            $returnOrder = $this->CallAPICurl("PUT", $HOST."/v2/orders/".$IDOrderAnyMarket, $headers, $params);

                            if($returnOrder['error'] == '1'){
                                $anymarketorderupdt->setStatus("0");
                                $anymarketorderupdt->setNmoStatusInt('ERROR 02');
                                $anymarketorderupdt->setNmoDescError($returnOrder['return']);
                                $anymarketorderupdt->setStores(array($storeID));
                                $anymarketorderupdt->save();
                            }

                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc( json_encode($returnOrder['return']) );
                            $anymarketlog->setLogId( $idOrder );
                            $anymarketlog->setLogJson( json_encode($returnOrder['json']) );
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->setStatus("1");
                            $anymarketlog->save();
                        }else{
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('There was some error getting data Invoice or Tracking.') );
                            $anymarketlog->setLogId( $idOrder );
                            $anymarketlog->setLogJson('');
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->setStatus("1");
                            $anymarketlog->save();
                        }
                    }else{
                        if($ConfigOrder == 0){
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setStatus("1");
                            $anymarketlog->setLogDesc( $statuAM );
                            $anymarketlog->setLogId( $idOrder );
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->save();
                        }
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
     * @param $idOrder
     * @param $HOST
     * @param $TOKEN
     */
    private function sendOrderToAnyMarket($idOrder, $HOST, $TOKEN){
        $storeID = $this->getCurrentStoreView();
        $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID); 
        if($ConfigOrder == 0){
            $Order = Mage::getModel('sales/order')->setStoreId($storeID)->loadByIncrementId( $idOrder );

            //TRATA OS ITEMS
            $orderedItems = $Order->getAllVisibleItems();
            $orderedProductIds = array();

            foreach ($orderedItems as $item) {
                $orderedProductIds[] = array(
                    "sku" => array(
                        "partnerId" => $item->getData('sku')
                    ),
                    "amount" => $item->getData('qty_ordered'),
                    "unit" => $item->getData('original_price'),
                    "discount" => $item->getData('discount_amount')
                );
            }

            //OBTEM OS DADOS DO PAGAMENTO
            $payment = $Order->getPayment();

            //OBTEM OS DADOS DA ENTREGA
            $shipping = $Order->getShippingAddress();

            $docField = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_doc_type_field', $storeID));
            $docData = "";
            if(!$Order->getCustomerIsGuest() || $Order->getCustomerId() != null ){
                $customer = Mage::getModel("customer/customer")->load($Order->getCustomerId());
                $docData = $customer->getData( $docField );
            }

            if( $docData == "" ){
                if($Order->getCustomerTaxvat()){
                    $docData = $Order->getCustomerTaxvat();
                }
            }

            $statusOrder = $Order->getStatus();
            if($statusOrder == 'pending'){
                $statuAM = $this->getStatusMageToAnyMarketOrderConfig('new');
            }else{
                $statuAM = $this->getStatusMageToAnyMarketOrderConfig($statusOrder);
            }

            if( (strpos($statuAM, 'ERROR:') === false) && ($statuAM != '') ) {
                $params = array(
                    'marketPlaceId' => $idOrder,
                    "createdAt" => gmdate('Y-m-d\TH:i:s\Z', strtotime( $Order->getData('created_at') )),
                    "status" =>  $statuAM,
                    "marketPlace" => "ECOMMERCE",
                    "marketPlaceStatus" => $statuAM,
                    "marketPlaceUrl" => null,
                    "shipping" => array(
                        "city" => $shipping->getCity(),
                        "state" => $shipping->getRegion(),
                        "country" => $shipping->getCountry(),
                        "address" => $shipping->getStreetFull(),
                        "zipCode" => $shipping->getPostcode()
                    ),
                    "buyer" => array(
                        "id" => 0,
                        "name" => $Order->getCustomerFirstname()." ".$Order->getCustomerLastname(),
                        "email" => $Order->getCustomerEmail(),
                        "document" =>  $docData,
                        "documentType" => $this->getDocumentType($docData),
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
                    "discount" => $Order->getDiscountAmount(),
                    "freight" => $Order->getShippingAmount(),
                    "gross" => $Order->getBaseGrandTotal(),
                    "total" => $Order->getBaseGrandTotal()
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

                $returnOrder = $this->CallAPICurl("POST", $HOST."/v2/orders/", $headers, $params);

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( json_encode($returnOrder['return']) );

                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($idOrder, 'nmo_id_order');
                $anymarketorders->setStatus("1");
                $anymarketorders->setStores(array($storeID));
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

                $anymarketlog->setStores(array($storeID));
                $anymarketlog->setLogJson( $returnOrder['json'] );
                $anymarketlog->setStatus("1");
                $anymarketlog->save();

            }else{
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->load($idOrder, 'nmo_id_order');
                $anymarketorders->setNmoStatusInt('ERROR 02');
                $anymarketorders->setStores(array($storeID));
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
     * List Order from AnyMarket
     *
     * @return int
     */
    public function listOrdersFromAnyMarketMagento(){
        $storeID = $this->getCurrentStoreView();

        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);
        $STATUSIMPORT = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_stauts_order_field', $storeID);

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
            $returnOrder = $this->CallAPICurl("GET", $HOST."/v2/orders/?offset=".$startRec."&limit=30", $headers, null);
            if($returnOrder['error'] == '1'){
                $startRec = 1;
                $countRec = 0;

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error on import order from anymarket '). $returnOrder['return'] );
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }else {
                $JsonReturn = $returnOrder['return'];

                $startRec = $startRec + $JsonReturn->page->size;
                $countRec = $JsonReturn->page->totalElements;

                foreach ($JsonReturn->content as $value) {
                    $IDOrderAnyMarket = $value->marketPlaceId;

                    if (strpos($STATUSIMPORT, $value->status) !== false) {
                        $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->setStoreId($storeID);
                        $anymarketorders->load($IDOrderAnyMarket, 'nmo_id_anymarket');
                        if ($anymarketorders->getData('nmo_id_anymarket') == null || (is_array($anymarketorders->getData('store_id')) && !in_array($storeID, $anymarketorders->getData('store_id')))) {
                            $idAnyMarket = $value->id;

                            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                            $anymarketorders->setStatus("0");
                            $anymarketorders->setNmoStatusInt('Não integrado (AnyMarket)');
                            $anymarketorders->setNmoDescError('');
                            $anymarketorders->setNmoIdSeqAnymarket($idAnyMarket);
                            $anymarketorders->setNmoIdAnymarket($IDOrderAnyMarket);
                            $anymarketorders->setNmoIdOrder('');
                            $anymarketorders->setStores(array($storeID));
                            $anymarketorders->save();

                            $contPed = $contPed + 1;
                        }

                    }
                }
            }
        }

        $salesCollection = Mage::getModel("sales/order")->getCollection();
        foreach($salesCollection as $order){
            $orderId = $order->getIncrementId();
            $storeID = $order->getStoreId();

            $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders')->setStoreId($storeID);
            $anymarketorders->load($orderId, 'nmo_id_order');
            if($anymarketorders->getData('nmo_id_order') == null || (is_array($anymarketorders->getData('store_id')) && !in_array($storeID, $anymarketorders->getData('store_id')) ) ){
                $anymarketorders = Mage::getModel('db1_anymarket/anymarketorders');
                $anymarketorders->setStatus("0");
                $anymarketorders->setNmoStatusInt('Não integrado (Magento)');
                $anymarketorders->setNmoDescError('');
                $anymarketorders->setNmoIdSeqAnymarket('');
                $anymarketorders->setNmoIdAnymarket('');
                $anymarketorders->setNmoIdOrder( $orderId );
                $anymarketorders->setStores(array($storeID));
                $anymarketorders->save();

                $contPed = $contPed+1;
            }
        }

        return $contPed;

    }

}
