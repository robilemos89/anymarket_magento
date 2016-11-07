<?php

class DB1_AnyMarket_Helper_Order extends DB1_AnyMarket_Helper_Data
{

    /**
     * get status order AM to MG from configs
     *
     * @param $OrderRowData
     * @return string
     */
    private function getStatusAnyMarketToMageOrderConfig($storeID, $OrderRowData){
        if($OrderRowData == null){
            $OrderRowData = "new";
        }

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
                            ->addFieldToFilter('main_table.status',array('eq'=>$OrderReturn));
                        //->addStatusFilter($OrderReturn);

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

        $anymarketorders->setNmoIdSeqAnymarket($idSeqAnyMarket);
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
    private function getStatusMageToAnyMarketOrderConfig($storeID, $OrderRowData){
        if($OrderRowData == null){
            $OrderRowData = "new";
        }

        $StatusOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_status_mg_am_field', $storeID);
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
     * @param $storeID
     * @param $OrderJSON
     * @return array
     */
    public function getCompleteAddressOrder($storeID, $OrderJSON){
        $retArrStreet = array(
            0 => "Frete não especificado.",
            1 => " ",
            2 => " ",
            3 => " "
        );

        if( isset($OrderJSON->shipping) ) {
            if (isset($OrderJSON->shipping->address)) {
                $OrderJSON = json_decode(json_encode($OrderJSON), true);

                $street1 = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_add1_field', $storeID);
                $street2 = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_add2_field', $storeID);
                $street3 = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_add3_field', $storeID);
                $street4 = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_add4_field', $storeID);

                $street1 = (isset($OrderJSON['shipping'][$street1])) ? $OrderJSON['shipping'][$street1] : $OrderJSON['shipping']['address'];
                $street2 = (isset($OrderJSON['shipping'][$street2])) ? $OrderJSON['shipping'][$street2] : '';
                $street3 = (isset($OrderJSON['shipping'][$street3])) ? $OrderJSON['shipping'][$street3] : '';
                $street4 = (isset($OrderJSON['shipping'][$street4])) ? $OrderJSON['shipping'][$street4] : '';

                $retArrStreet = array(
                    0 => $street1,
                    1 => $street2,
                    2 => $street3,
                    3 => $street4
                );
            }
        }

        return $retArrStreet;
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
    private function create_order($storeID, $anymarketordersSpec, $products, $customer, $IDAnyMarket, $IDSeqAnyMarket, $infoMetPag, $Billing, $Shipping, $shippValue, $ShippingDesc)
    {
        if( ($anymarketordersSpec->getData('nmo_id_anymarket') == null) ||
            ($anymarketordersSpec->getData('nmo_status_int') == "Não integrado (AnyMarket)") ||
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
            $orderGenerator->setShippingDescription($ShippingDesc);

            $CodOrder = $orderGenerator->createOrder($storeID, $products);



            $this->saveLogOrder('nmo_id_anymarket', $IDAnyMarket, 'Integrado', '', $IDSeqAnyMarket, $IDAnyMarket, $CodOrder, $storeID);

            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc('Order Created: ' . $CodOrder . ' ID Anymarket: ' . $IDAnyMarket);
            $anymarketlog->setStatus("0");
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
    public function getFeedOrdersFromAnyMarket($storeID){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

        if( $TOKEN != '' && $TOKEN != null ) {
            $headers = array(
                "Content-type: application/json",
                "Accept: */*",
                "gumgaToken: " . $TOKEN
            );

            $returnProd = $this->CallAPICurl("GET", $HOST . "/v2/orders/feeds?limit=100", $headers, null);

            if ($returnProd['error'] == '1') {
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc('Error on get feed orders ' . $returnProd['return']);
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            } else {
                $listOrders = $returnProd['return'];
                foreach ($listOrders as $order) {
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc('Consumed Order from feed: ' . $order->id . ' with token: ' . $order->token);
                    $anymarketlog->setStatus("1");
                    $anymarketlog->save();

                    $this->getSpecificOrderFromAnyMarket($order->id, $order->token, $storeID);

                    $paramFeed = array(
                        "token" => $order->token
                    );
                    $this->CallAPICurl("PUT", $HOST . "/rest/api/v2/orders/feeds/" . $order->id, $headers, $paramFeed);
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
                        $statsConfig = $this->getStatusAnyMarketToMageOrderConfig($storeID, $OrderJSON->status);
                        $statusMage = $statsConfig["status"];

                        if (strpos($statusMage, 'ERROR:') === false) {
                            //TRATA OS PRODUTOS
                            $_products = array();
                            $shippingDesc = array();
                            foreach ($OrderJSON->items as $item) {

                                if( isset($item->shippings) ) {
                                    foreach ($item->shippings as $shippItem) {
                                        if (!in_array($shippItem->shippingtype, $shippingDesc)) {
                                            array_push($shippingDesc, $shippItem->shippingtype);
                                        }
                                    }
                                }

                                $productLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $item->sku->partnerId);
                                if ($productLoaded) {
                                    $arrayTMP = array(
                                        'product' => $productLoaded->getId(),
                                        'price' => $item->unit,
                                        'qty' => $item->amount,
                                    );

                                    if($productLoaded->getTypeID() == "bundle") {
                                        $optionsBundle = Mage::helper('db1_anymarket/product')->getDetailsOfBundle($productLoaded);

                                        $boundOpt = array();
                                        $boundOptQty = array();
                                        foreach ($optionsBundle as $detProd) {
                                            $boundOpt[$detProd['option_id']] = $detProd['selection_id'];
                                            $boundOptQty[$detProd['option_id']] = $detProd['selection_qty'];
                                        }

                                        $arrayTMP['bundle_option'] = $boundOpt;
                                        $arrayTMP['bundle_option_qty'] = $boundOptQty;
                                    }

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
                                    $anymarketlog->setStatus("0");
                                    $anymarketlog->save();

                                    $this->addMessageInBox($storeID, Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
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
                                            ->addFieldToFilter($AttrToDoc, $document)->load()->getFirstItem();

                                        //caso nao ache pelo CPF valida se nao tem mascara
                                        if(!$customer->getId()) {
                                            if (strlen($document) == 11) {
                                                $document = $this->Mask('###.###.###-##', $document);
                                            } else {
                                                $document = $this->Mask('##.###.###/####-##', $document);
                                            }

                                            $customer = Mage::getModel('customer/customer')
                                                ->getCollection()
                                                ->addFieldToFilter($AttrToDoc, $document)->load()->getFirstItem();

                                            //caso ainda nao encontrou valida se existe o email
                                            if(!$customer->getId()) {
                                                $customer = Mage::getModel('customer/customer')
                                                    ->getCollection()
                                                    ->addFieldToFilter('email', $email)->load()->getFirstItem();

                                            }
                                        }

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

                                        $addressFullData = $this->getCompleteAddressOrder($storeID, $OrderJSON);
                                        $regionCollection = Mage::getModel('directory/region')->getCollection();
                                        $regionName = (isset($OrderJSON->shipping->state)) ? $OrderJSON->shipping->state : 'Não especificado';
                                        $regionID = 0;
                                        foreach ($regionCollection as $key) {
                                            if( $key->getData('name') == $regionName){
                                                $regionID = $key->getData('region_id');
                                                break;
                                            }
                                        }

                                        $addressFullData = $this->getCompleteAddressOrder($storeID, $OrderJSON);

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
                                                        'street' => $addressFullData,
                                                        'city' => (isset($OrderJSON->shipping->city)) ? $OrderJSON->shipping->city : 'Não especificado',
                                                        'country_id' => 'BR',
                                                        'region_id' => $regionID,
                                                        'region' => (isset($OrderJSON->shipping->state)) ? $OrderJSON->shipping->state : 'Não especificado',
                                                        'postcode' => (isset($OrderJSON->shipping->zipCode)) ? $OrderJSON->shipping->zipCode : 'Não especificado',
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
                                                $zipCodeOrder = (isset($OrderJSON->shipping->zipCode)) ? $OrderJSON->shipping->zipCode : 'Não especificado';
                                                $addressOrder = (isset($OrderJSON->shipping->address)) ? $OrderJSON->shipping->address : 'Frete não especificado.';
                                                if (($address->getData('postcode') == $zipCodeOrder) && ($address->getData('street') == $addressOrder)) {
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
                                                    'street' => $addressFullData,
                                                    'city' => (isset($OrderJSON->shipping->city)) ? $OrderJSON->shipping->city : 'Não especificado',
                                                    'country_id' => 'BR',
                                                    'region' => (isset($OrderJSON->shipping->state)) ? $OrderJSON->shipping->state : 'Não especificado',
                                                    'region_id' => $regionID,
                                                    'postcode' => (isset($OrderJSON->shipping->zipCode)) ? $OrderJSON->shipping->zipCode : 'Não especificado',
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
                                        $infoMetPagCom = array();
                                        if( isset($OrderJSON->payments) ) {
                                            foreach ($OrderJSON->payments as $payment) {
                                                $infoMetPag = $payment->method;
                                                if($payment->paymentMethodNormalized) {
                                                    array_push($infoMetPagCom, $payment->paymentMethodNormalized." - Parcelas: ".$payment->installments);
                                                }
                                            }
                                        }

                                        //REFACTOR
                                        $OrderIDMage = $this->create_order($storeID, $anymarketordersSpec, $_products, $customer, $IDOrderAnyMarket, $idSeqAnyMarket, $infoMetPag, $AddressShipBill, $AddressShipBill, $OrderJSON->freight, implode(",", $shippingDesc) );
                                        $OrderCheck = Mage::getModel('sales/order')->loadByIncrementId($OrderIDMage);

                                        $this->changeFeedOrder($HOST, $headers, $idSeqAnyMarket, $tokenFeed);

                                        if ($OrderCheck->getId()) {
                                            $comment = '<b>Código do Pedido no Canal de Vendas: </b>'.$OrderJSON->marketPlaceNumber.'<br>';
                                            $comment .= '<b>Canal de Vendas: </b>'.$OrderJSON->marketPlace.'<br>';

                                            if( count($infoMetPagCom) > 0 ) {
                                                foreach ($infoMetPagCom as $iMetPag) {
                                                    $comment .= '<b>Forma de Pagamento: </b>' . $iMetPag . '<br>';
                                                }
                                            }else{
                                                $comment .= '<b>Forma de Pagamento: </b>Inf. não disponibilizada pelo marketplace.<br>';
                                            }

                                            $addressComp = (isset($OrderJSON->shipping->address)) ? $OrderJSON->shipping->address : 'Não especificado';
                                            $comment .= '<b>Endereço Completo: </b>'.$addressComp;

                                            $OrderCheck->addStatusHistoryComment( $comment );
                                            $OrderCheck->setEmailSent(false);
                                            $OrderCheck->save();


                                            $this->changeStatusOrder($storeID, $OrderJSON, $OrderIDMage);
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
                                    $anymarketlog->setStatus("0");
                                    $anymarketlog->setStores(array($storeID));
                                    $anymarketlog->save();

                                    $this->addMessageInBox($storeID, Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
                                        Mage::helper('db1_anymarket')->__('Error synchronizing order number: ') . "Anymarket(" . $IDOrderAnyMarket . ") <br/>" .
                                        Mage::helper('db1_anymarket')->__('Customer invalid or blank document.'),
                                        '');
                                }
                            }
                        } else {
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc($statusMage);
                            $anymarketlog->setLogId($IDOrderAnyMarket);
                            $anymarketlog->setStatus("0");
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
                        $this->changeStatusOrder($storeID, $OrderJSON, $anymarketordersSpec->getData('nmo_id_order'));
                    }
                }
            }
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( 'Error on import Order: '.$idSeqAnyMarket.'  '.$returnOrderItens['return'] );
            $anymarketlog->setStatus("0");
            $anymarketlog->save();

            $this->addMessageInBox($storeID, Mage::helper('db1_anymarket')->__('Error on synchronize order.'),
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

    private function checkIfCanCreateInvoice($Order){
        $continueOrder = true;
        foreach ($Order->getInvoiceCollection() as $inv) {
            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId( $inv->getIncrementId() );
            foreach ($invoice->getCommentsCollection() as $item) {
                $CommentCurr = $item->getComment();
                if ((strpos($CommentCurr, 'Registro de Pagamento criado por Anymarket') !== false)) {
                    $continueOrder = false;
                    break;
                }

            }
        }

        return $continueOrder;
    }

    /**
     * change status order
     *
     * @param $storeID
     * @param $JSON
     * @param $IDOrderMagento
     *
     * @return boolean
     */
    private function changeStatusOrder($storeID, $JSON, $IDOrderMagento){
        $StatusPedAnyMarket = $JSON->status;

        $statsConfig = $this->getStatusAnyMarketToMageOrderConfig($storeID, $StatusPedAnyMarket );
        $stateMage  = $statsConfig["state"];
        $statusMage = $statsConfig["status"];

        if (strpos($statusMage, 'ERROR:') === false) {
            Mage::getSingleton('core/session')->setImportOrdersVariable('false');
            $order = Mage::getModel('sales/order')->loadByIncrementId( $IDOrderMagento );

            if( $order->getData('state') == $stateMage ){
                return false;
            }

            $createRegPay = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_create_reg_pay_field', $storeID);
            $itemsarray = null;

            if( $createRegPay == "1" && $StatusPedAnyMarket == 'PAID_WAITING_SHIP' ){
                if( $order->canInvoice() ){
                    if( $this->checkIfCanCreateInvoice($order) ) {
                        $orderItems = $order->getAllItems();
                        foreach ($orderItems as $_eachItem) {
                            $opid = $_eachItem->getId();
                            $qty = $_eachItem->getQtyOrdered();
                            $itemsarray[$opid] = $qty;
                        }
                        $nfeString = "Registro de Pagamento criado por Anymarket";
                        Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $itemsarray, $nfeString, 0, 0);
                    }
                }
            }

            if( isset($JSON->invoice) && $StatusPedAnyMarket == 'INVOICED' ){
                if( $order->canInvoice() ){
                    if(isset($JSON->invoice->accessKey) ) {
                        $nfe = $JSON->invoice->accessKey;
                        $dateNfe = $JSON->invoice->date;

                        $DateTime = strtotime($dateNfe);
                        $fixedDate = date('d/m/Y H:i:s', $DateTime);

                        if($itemsarray == null) {
                            $orderItems = $order->getAllItems();
                            foreach ($orderItems as $_eachItem) {
                                $opid = $_eachItem->getId();
                                $qty = $_eachItem->getQtyOrdered();
                                $itemsarray[$opid] = $qty;
                            }
                        }

                        if (!$order->hasInvoices()) {
                            $nfeString = 'nfe:' . $nfe . ', emissao:' . $fixedDate;
                            Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $itemsarray, $nfeString, 0, 0);
                        }else{
                            $firstInvoiceID = $order->getInvoiceCollection()->getFirstItem()->getIncrementId();
                            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId( $firstInvoiceID );
                            $addComment = true;
                            foreach ($invoice->getCommentsCollection() as $item) {
                                $CommentCurr = $item->getComment();
                                if ((strpos($CommentCurr, 'Adicionado por Anymarket - nfe:') !== false)) {
                                    $addComment = false;
                                    break;
                                }
                            }

                            if( $addComment ){
                                $nfeString = 'Adicionado por Anymarket - nfe:' . $nfe . ', emissao:' . $fixedDate;

                                $invoice->addComment($nfeString, "");
                                $invoice->setEmailSent(false);
                                $invoice->save();
                            }
                        }
                    }
                }
            }

            if( isset($JSON->tracking) && $StatusPedAnyMarket == 'PAID_WAITING_DELIVERY' ){
                if( $order->canShip() && !$order->hasShipments() ){
                    if(isset($JSON->tracking->number)) {
                        $TrNumber = $JSON->tracking->number;
                        $TrCarrier = strtolower($JSON->tracking->carrier);

                        $shipmentId = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $itemsarray, 'Create by AnyMarket', false, 1);

                        $TracCodeArr = Mage::getModel('sales/order_shipment_api')->getCarriers($order->getIncrementId());
                        if (isset($TracCodeArr[$TrCarrier])) {
                            Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId, $TrCarrier, $TrCarrier, $TrNumber);
                        } else {
                            $arrVar = array_keys($TracCodeArr);
                            Mage::getModel('sales/order_shipment_api')->addTrack($shipmentId, array_shift($arrVar), 'Não Econtrado(' . $TrCarrier . ')', $TrNumber);
                        }
                    }
                }
            }

            if($statusMage != Mage_Sales_Model_Order::STATE_NEW){
                if($stateMage == Mage_Sales_Model_Order::STATE_COMPLETE){
                    $history = $order->addStatusHistoryComment('Finalizado pelo AnyMarket.', false);
                    $history->setIsCustomerNotified(false);
                }
                $order->setData('state', $stateMage);
                $order->setStatus($statusMage, true);

                if($stateMage == Mage_Sales_Model_Order::STATE_CANCELED) {
                    foreach ($order->getAllItems() as $item) {
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct( $item->getProductId() );
                        if ($stockItem->getManageStock()) {
                            $stockItem->setData('qty', $stockItem->getQty() + $item->getQtyOrdered());
                        }
                        $stockItem->save();

                        $item->setQtyCanceled($item->getQtyOrdered());
                        $item->save();
                    }
                }

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
            $anymarketlog->setStatus("0");
            $anymarketlog->setStores(array($storeID));
            $anymarketlog->save();

            Mage::getSingleton('core/session')->setImportOrdersVariable('true');
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( $statusMage );
            $anymarketlog->setLogId( $IDOrderMagento ); 
            $anymarketlog->setStatus("0");
            $anymarketlog->save();
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
        $date = "";
        $chaveAcID = "";
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
                        $chaveAcID = substr( $nfeTmp, $nfeCount+4, 44);
                        $nfeID = $chaveAcID;

                        $date = substr( $CommentCurr, $emissaoCount+8, 19);
                        $dateTmp = str_replace("/", "-", $date );

                        $date = $this->formatDateTimeZone($dateTmp);
                    }
                }
            }
        }

        if( $chaveAcID == "" ) {
            foreach ($Order->getStatusHistoryCollection() as $item) {
                $CommentCurr = $item->getComment();

                $CommentCurr = str_replace(array(" ", "<b>", "</b>"), "", $CommentCurr );
                $CommentCurr = str_replace(array("<br>"), "<br/>", $CommentCurr );
                $chaveAcesso = strpos($CommentCurr, 'ChavedeAcesso:');
                if( (strpos($CommentCurr, 'ChavedeAcesso:') !== false) ) {
                    $chaveAcID = substr( $CommentCurr, $chaveAcesso+14, 44);

                    $notaFiscal = strpos($CommentCurr, 'Notafiscal:');
                    if( (strpos($CommentCurr, 'Notafiscal:') !== false) ) {
                        $endNF = strpos($CommentCurr, '<br/>');
                        $nfeID = substr( $CommentCurr, $notaFiscal+11, $endNF-11);

                        if( $nfeID == "" ){
                            $nfeID = $chaveAcID;
                        }
                    }
					
					$dateTmp =  new DateTime(str_replace("/", "-", $item->getData('created_at') ));
					$date = date_format($dateTmp, 'Y-m-d\TH:i:s\Z');					
                    break;
                }
            }
        }

        if( $chaveAcID == "" ) {
            if ($Order->hasShipments()){
                foreach ($Order->getShipmentsCollection() as $ship) {
                    $shippment = Mage::getModel('sales/order_shipment')->loadByIncrementId( $ship->getIncrementId() );
                    foreach ($shippment->getCommentsCollection() as $item) {
                        $CommentCurr = $item->getComment();

                        $nfeCount = strpos($CommentCurr, 'nfe:');
                        $emissaoCount = strpos($CommentCurr, 'emiss');
                        if( (strpos($CommentCurr, 'nfe:') !== false) && (strpos($CommentCurr, 'emiss') !== false) ) {
                            $caracts = array("/", "-", ".");
                            $nfeTmp = str_replace($caracts, "", $CommentCurr );
                            $chaveAcID = substr( $nfeTmp, $nfeCount+4, 44);
                            $nfeID = $chaveAcID;

                            $date = substr( $CommentCurr, $emissaoCount+8, 19);
                            $dateTmp = str_replace("/", "-", $date );

                            $date = $this->formatDateTimeZone($dateTmp);
                        }
                    }
                }
            }
        }

        $retArr = array("number" => $nfeID, "date" => $date, "accessKey" => $chaveAcID);
        return $retArr;
    }

    /**
     * get tracking order
     *
     * * @param $storeID
     * @param $Order
     * @return array
     */
    public function getTrackingOrder($storeID, $Order){
        $TrackNum = '';
        $TrackTitle = '';
        $TrackCreate = '';
        $dateTrack = '';

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                                                    ->setOrderFilter($Order)
                                                    ->load();
        foreach ($shipmentCollection as $shipment){
            foreach($shipment->getAllTracks() as $tracknum){
                $TrackNum = $tracknum->getNumber();
                $TrackTitle = $tracknum->getTitle();
                $TrackCreate = $tracknum->getCreatedAt();

                $dateTmp =  new DateTime(str_replace("/", "-", $TrackCreate ));
                $dateTrack = date_format($dateTmp, 'Y-m-d\TH:i:s\Z');
            }
        }

        $days = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_estimate_date_field', $storeID);

        $days = ($days == "" || $days == null) ? 10 : $days;
        $stimatedDate = date('Y-m-d\TH:i:s\Z', strtotime("+".$days." days", strtotime($dateTrack)));
        return array("number" => $TrackNum,
                     "carrier" => $TrackTitle,
                     "date" => $dateTrack,
                     "shippedDate" => $dateTrack,
                     "url" => "",
                     "estimateDate" => $stimatedDate);
    }

    /**
     * update order in AM
     *
     * @param $Order
     */
    public function updateOrderAnyMarket($storeID, $Order){
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
                    $statuAM = $this->getStatusMageToAnyMarketOrderConfig($storeID, $status);
                    if (strpos($statuAM, 'ERROR:') === false) {
                        $params = array(
                          "status" => $statuAM
                        );

                        $invoiceData = $this->getInvoiceOrder($Order);
                        $trackingData = $this->getTrackingOrder($storeID, $Order);

                        if ($invoiceData['number'] != '') {
                            $params["invoice"] = $invoiceData;
                        }

                        if ($trackingData['number'] != '') {
                            $params["tracking"] = $trackingData;
                        }

                        if( ($statuAM == "CONCLUDED" || $statuAM == "CANCELED" || $statuAM == "PAID_WAITING_SHIP" || $statuAM == "INVOICED" || $statuAM == "PAID_WAITING_DELIVERY" ) ||
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
                            $anymarketlog->setStatus("0");
                            $anymarketlog->save();
                        }else{
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('There was some error getting data Invoice or Tracking.') );
                            $anymarketlog->setLogId( $idOrder );
                            $anymarketlog->setLogJson('');
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->setStatus("0");
                            $anymarketlog->save();
                        }
                    }else{
                        if($ConfigOrder == 0){
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setStatus("0");
                            $anymarketlog->setLogDesc( $statuAM );
                            $anymarketlog->setLogId( $idOrder );
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->save();
                        }
                    }
                }else{
                    $this->sendOrderToAnyMarket($storeID, $idOrder, $HOST, $TOKEN);
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
    private function sendOrderToAnyMarket($storeID, $idOrder, $HOST, $TOKEN){
        $ConfigOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
        if($ConfigOrder == 0 && $idOrder){
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
                $statuAM = $this->getStatusMageToAnyMarketOrderConfig($storeID, 'new');
            }else{
                $statuAM = $this->getStatusMageToAnyMarketOrderConfig($storeID, $statusOrder);
            }


            if( (strpos($statuAM, 'ERROR:') === false) && ($statuAM != '') ) {
                $params = array(
                    'marketPlaceId' => $idOrder,
                    "createdAt" => $this->formatDateTimeZone( $Order->getData('created_at') ),
                    "status" =>  $statuAM,
                    "marketPlace" => "ECOMMERCE",
                    "marketPlaceStatus" => $statuAM,
                    "marketPlaceUrl" => null,
                    "shipping" => array(
                        "city" => $shipping->getCity(),
                        "state" => $shipping->getRegion(),
                        "country" => $shipping->getCountry(),
                        "address" => $shipping->getStreetFull(),
                        "street" =>  $shipping->getStreet(1),
                        "number" =>  $shipping->getStreet(2),
                        "comment" =>  $shipping->getStreet(3),
                        "neighborhood" =>  $shipping->getStreet(4),
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
                                        "status" => "",
                                        "value" => $Order->getBaseGrandTotal()
                                    ),
                    ),
                    "discount" => floatval( $Order->getDiscountAmount() ) < 0 ? floatval( $Order->getDiscountAmount() )*-1 : $Order->getDiscountAmount(),
                    "freight" => $Order->getShippingAmount(),
                    "gross" => $Order->getBaseSubtotal(),
                    "total" => $Order->getBaseGrandTotal()
                );

                $arrTracking = $this->getTrackingOrder($storeID, $Order);
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
                $anymarketlog->setStatus("0");
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
    public function listOrdersFromAnyMarketMagento($storeID){
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
                $anymarketlog->setStatus("0");
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
                $anymarketorders->setNmoIdOrder( $orderId );
                $anymarketorders->setStores(array($storeID));
                $anymarketorders->save();

                $contPed = $contPed+1;
            }
        }

        return $contPed;

    }

}
