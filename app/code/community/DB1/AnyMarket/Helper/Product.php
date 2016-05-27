<?php

class DB1_AnyMarket_Helper_Product extends DB1_AnyMarket_Helper_Data
{
    /**
     * @param $arrAttr
     * @param $key
     * @param $value
     * @return bool
     */
    private function checkArrayAttributes($arrAttr, $key, $value){
        foreach ($arrAttr as $arrVal) {
            if( $arrVal[$key] == $value ){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $UnitMeasurement
     * @param $value
     * @param $typeConvert
     * @return float
     */
    private function convertUnitMeasurement($UnitMeasurement, $value, $typeConvert){
        /*
        * $typeConvert = 0 receive from anymarket
        * $typeConvert = 1 send to anymarket

        * 'Centímetro', 'value' => '0'
        * 'Metro', 'value' => '1'
        * 'Decímetro', 'value' => '2'
        * 'Milímetro', 'value' => '3'
        */

        $valueRet = $value;
        if( $typeConvert == 0 ){
            switch ($UnitMeasurement) {
                case 0:
                    $valueRet = $value*1;
                    break;
                case 1:
                    $valueRet = $value/100;
                    break;
                case 2:
                    $valueRet = $value/10;
                    break;
                case 3:
                    $valueRet = $value*10;
                    break;
            }
        }else{
            switch ($UnitMeasurement) {
                case 0:
                    $valueRet = $value*1;
                    break;
                case 1:
                    $valueRet = $value*100;
                    break;
                case 2:
                    $valueRet = $value*10;
                    break;
                case 3:
                    $valueRet = $value/10;
                    break;
            }
        }

        return $valueRet;
    }

    /**
     * get all fields configured in configuration for descriptions
     *
     * @return string
     */
    public function getFieldsDescriptionConfig($storeID){
        $ConfigDescProd = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_desc_field', $storeID);
        $fieldDesc = array();
        if ($ConfigDescProd && $ConfigDescProd != 'a:0:{}') {
            $ConfigDescProd = unserialize($ConfigDescProd);
            if (is_array($ConfigDescProd)) {
                foreach($ConfigDescProd as $ConfigDescProdRow) {

                    $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','description');
                    $attributesData = Mage::getResourceModel('catalog/product_attribute_collection')
                        ->addVisibleFilter()
                        ->addFieldToSelect(array('frontend_input', 'backend_type'))
                        ->addFieldToFilter('main_table.attribute_id', array('eq' => $attributeId));

                    $arrValuesAttrs = array_values( $attributesData->getData() );
                    $StoreIDAmProd = array_shift( $arrValuesAttrs );
                    if( ($StoreIDAmProd['frontend_input'] == "textarea") && ($StoreIDAmProd['backend_type'] == "text") ){
                        array_push($fieldDesc, $ConfigDescProdRow['descProduct']);
                    }
                }
            }
        }

        if( empty($fieldDesc) ) {
            array_push($fieldDesc, "description");
        }

        return $fieldDesc;
    }

    /**
     * get decription by configuration
     *
     * @param $product
     * @return string
     */
    public function getFullDescription($storeID, $product){
        $ConfigDescProd = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_desc_field', $storeID);
        $descComplete = "";
        if ($ConfigDescProd && $ConfigDescProd != 'a:0:{}') {
            $ConfigDescProd = unserialize($ConfigDescProd);
            if (is_array($ConfigDescProd)) {
                foreach($ConfigDescProd as $ConfigDescProdRow) {
                    $descComplete .= $product->getData( $ConfigDescProdRow['descProduct'] ).' ';
                }
            }
        }else{
            $descComplete = $product->getDescription();
        }
        $baseURLMedia = Mage::getBaseUrl('media');

        $descComplete = str_replace('{{media url="', $baseURLMedia, $descComplete);
        $descComplete = str_replace('"}}', '', $descComplete);

        return trim($descComplete);
    }

    /**
     * create logs of product
     *
     * @param $returnProd array
     * @param $product Mage_Catalog_Model_Product
     */
    public function saveLogsProds($storeID, $priority, $returnProd, $product){
        $anymarketproductsUpdt = Mage::getModel('db1_anymarket/anymarketproducts')->setStoreId($storeID)->load($product->getId(), 'nmp_id');

        if(is_array($anymarketproductsUpdt->getData('store_id'))){
            $arrValuesProds = array_values($anymarketproductsUpdt->getData('store_id'));
            $StoreIDAmProd = array_shift($arrValuesProds);
        }else{
            $StoreIDAmProd = $anymarketproductsUpdt->getData('store_id');
        }

        if($returnProd['error'] == '1'){ //RETORNOU ERRO
            if( ($anymarketproductsUpdt->getData('nmp_sku') == null) || ($StoreIDAmProd != $storeID) ){
                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                $anymarketproducts->setNmpId( $product->getId() );
                $anymarketproducts->setNmpSku( $product->getSku() );
                $anymarketproducts->setNmpName( $product->getName() );
                $anymarketproducts->setNmpDescError( $returnProd['return'] );
                $anymarketproducts->setNmpStatusInt("Erro");
                $anymarketproducts->setStatus("1");
                $anymarketproducts->setStores(array($storeID));
                $anymarketproducts->save();
            }else{
                $anymarketproductsUpdt->setNmpId( $product->getId() );
                $anymarketproductsUpdt->setNmpSku( $product->getSku() );
                $anymarketproductsUpdt->setNmpName( $product->getName() );
                $anymarketproductsUpdt->setNmpDescError( $returnProd['return'] );
                $anymarketproductsUpdt->setNmpStatusInt("Erro");
                $anymarketproductsUpdt->setStatus("1");
                $anymarketproductsUpdt->setStores(array($storeID));
                $anymarketproductsUpdt->save();
            }

            $URL = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $product->getId() ));
            $this->addMessageInBox($storeID, Mage::helper('db1_anymarket')->__('Error synchronizing AnyMarket products.'), Mage::helper('db1_anymarket')->__('Error on Sync product SKU: ').$product->getSku(), $URL);
            $returnMet = $returnProd['return'];
        }else{ //FOI BEM SUCEDIDO
            if( ($anymarketproductsUpdt->getData('nmp_sku') == null) || ($StoreIDAmProd != $storeID) ) {
                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                $anymarketproducts->setNmpId($product->getId());
                $anymarketproducts->setNmpSku($product->getSku());
                $anymarketproducts->setNmpName($product->getName());
                $anymarketproducts->setNmpDescError("");
                $anymarketproducts->setNmpStatusInt("Integrado");
                $anymarketproducts->setStatus("1");
                $anymarketproducts->setStores(array($storeID));
                $anymarketproducts->save();
            }else{
                $anymarketproductsUpdt->setNmpId($product->getId());
                $anymarketproductsUpdt->setNmpSku($product->getSku());
                $anymarketproductsUpdt->setNmpName($product->getName());
                $anymarketproductsUpdt->setNmpDescError("");
                $anymarketproductsUpdt->setNmpStatusInt("Integrado");
                $anymarketproductsUpdt->setStatus("1");
                $anymarketproductsUpdt->setStores(array($storeID));
                $anymarketproductsUpdt->save();
            }

            $returnMet = $returnProd['return'];
        }

        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
        if(is_string($returnMet)){
            $anymarketlog->setLogDesc( $returnMet );
        }else{
            $anymarketlog->setLogDesc( json_encode($returnMet) );
        }

        $anymarketlog->setLogId( $product->getSku() );
        if(is_string($returnProd['json'])){
            $anymarketlog->setLogJson( $returnProd['json'] );
        }else{
            $anymarketlog->setLogJson( json_encode($returnProd['json']) );
        }

        $anymarketlog->setStatus($priority);
        $anymarketlog->setStores(array($storeID));
        $anymarketlog->save();
    }

    /**
     * @param $var_1
     * @param $var_2
     * @return array
     */
    private function compareArrayImage($var_1, $var_2){
        $arrayReturn = array();
        foreach ($var_1 as $value_1) {
            $hSamVal = false;
            foreach ($var_2 as $value_2) {
                $ArrtratValue1 = explode("_", $value_1['ctrl']);
                $ArrtratValue2 = explode("_", $value_2['ctrl']);

                $tratval1 = array_values($ArrtratValue1);
                $tratValue1 = array_shift($tratval1);
                $tratval2 = array_values($ArrtratValue2);
                $tratValue2 = array_shift($tratval2);
                if( $tratValue1 == $tratValue2 ){
                    $hSamVal = true;
                    break;
                }
            }
            if(!$hSamVal){
                $arrayReturn[] = $value_1;
            }
        }
        return $arrayReturn;
    }

    /**
     * create a Configurable Product
     *
     * @param $storeID
     * @param $dataProdConfig
     * @param $simpleProducts
     * @param $AttributeIds
     * @return Mage_Catalog_Model_Product
     */
    private function create_configurable_product($storeID, $dataProdConfig, $simpleProducts, $AttributeIds){
        $productGenerator = Mage::helper('db1_anymarket/productgenerator');
        $product = $productGenerator->createConfigurableProduct($storeID, $dataProdConfig, $simpleProducts, $AttributeIds);

        $returnProd['return'] = Mage::helper('db1_anymarket')->__('Configurable product Created').' ('.$product->getId().')';
        $returnProd['error'] = '0';
        $returnProd['json'] = '';

        $this->saveLogsProds($storeID, "0", $returnProd, $product);

        return $product;
    }

    /**
     * update a Configurable Product
     *
     * @param $storeID
     * @param $idProd
     * @param $dataProdConfig
     * @param $simpleProducts
     * @param $AttributeIds
     * @return Mage_Catalog_Model_Product
     */
    private function update_configurable_product($storeID, $idProd, $dataProdConfig, $simpleProducts, $AttributeIds){
        $productGenerator = Mage::helper('db1_anymarket/productgenerator');
        $product = $productGenerator->updateConfigurableProduct($storeID, $idProd, $dataProdConfig, $simpleProducts, $AttributeIds);
        
        $returnProd['return'] = Mage::helper('db1_anymarket')->__('Configurable product Updated').' ('.$product->getSku().')';
        $returnProd['error'] = '0';
        $returnProd['json'] = '';
        $this->saveLogsProds($storeID, "0", $returnProd, $product);

        return $product;
    }

    /**
     * create simple prod in magento
     *
     * @param $storeID
     * @param $data
     * @return Mage_Catalog_Model_Product
     */
    function create_simple_product($storeID, $data){
        $productGenerator = Mage::helper('db1_anymarket/productgenerator');
        $product = $productGenerator->createSimpleProduct($data);

        if(!$product){
            $returnProd['return'] = Mage::helper('db1_anymarket')->__('Simple product Created').' ('.$product->getId().')';
            $returnProd['error'] = '0';
            $returnProd['json'] = '';

            $this->saveLogsProds($storeID, "0", $returnProd, $product);
        }

        return $product;
    }

    /**
     * @param $attrCode
     * @param $attrVal
     * @param $typeProc
     * @return mixed
     */
    public function procAttrConfig($attrCode, $attrVal, $typeProc){
        $_product = Mage::getModel('catalog/product');
        $attr = $_product->getResource()->getAttribute($attrCode);

        if($attr){
            if ($attr->usesSource()) {
                if($typeProc == 0){
                    $returnAttr = $attr->getSource()->getOptionId((string)$attrVal);
                    if($returnAttr){
                        return $returnAttr;
                    }else{
                        return $attrVal;
                    }
                }else{
                    $returnAttr = $attr->getSource()->getOptionText($attrVal);
                    if($returnAttr){
                        return $returnAttr;
                    }else{
                        return $attrVal;
                    }
                }
            }else{
                return $attrVal;
            }
        }else{
            return $attrVal;
        }

    }

    /**
     * update image for specific product
     *
     * @param $Prod
     * @param $ProdsJSON
     * @param $idClient
     */
    public function update_image_product($Prod, $ProdsJSON, $idClient){
        $arrSku = $ProdsJSON->skus;
        $variation = array();
        foreach ($arrSku as $skuImg) {
            if($skuImg->idInClient == $Prod->getSku() ){
                foreach ($skuImg->variations as $variationSku) {
                    array_push($variation, $variationSku->description);
                }
                break;
            }
        }

        $mediaApi = Mage::getModel("catalog/product_attribute_media_api");
        $items = $mediaApi->items($Prod->getId());
        $imagesGalleryMG = array();
        foreach($items as $item) {
            $crltImg = basename($item['file']);
            $crltImg = str_replace(strrchr($crltImg,"."), "", $crltImg);
            $imagesGalleryMG[] = array('ctrl' => $crltImg, 'img' => $item['url'], 'file' => $item['file'] );
        }

        $imagesGalleryAM = array();
        foreach ($ProdsJSON->photos as $image) {
            $crltImgAM = basename($image->original);
            $crltImgAM = str_replace(strrchr($crltImgAM,"."), "", $crltImgAM);

            if( !empty($variation) ){
                if (in_array( $image->variationValue, $variation)) {
                    $imagesGalleryAM[] = array('ctrl' => md5($crltImgAM . $idClient), 'img' => $image->standard_resolution, 'main' => $image->main);
                }
            }else{
                $imagesGalleryAM[] = array('ctrl' => md5($crltImgAM . $idClient), 'img' => $image->standard_resolution, 'main' => $image->main);
            }
        }

        //COMPARA IMG AM COM MG SE TIVER DIVERGENCIA ADD NO PRODUTO
        $diffAM = $this->compareArrayImage($imagesGalleryAM, $imagesGalleryMG);
        if ($diffAM) {
            foreach ($diffAM as $diffAM_value) {
                $imagesGallery[] = array('img' => $diffAM_value['img'], 'main' => $diffAM_value['main']);
            }

            $dataImgs = array('images' => $imagesGallery, 'sku' => $idClient);
            $productGenerator = Mage::helper('db1_anymarket/productgenerator');
            $productGenerator->updateImages($Prod, $dataImgs);
        }

        //COMPARA IMG AM COM MG SE TIVER DIVERCIA REMOVE DO PRODUTO
        $diffMG = $this->compareArrayImage($imagesGalleryMG, $imagesGalleryAM);
        if ($diffMG) {
            foreach ($diffMG as $diffMG_value) {
                $mediaApi->remove($Prod->getId(), $diffMG_value['file']);
                //remover arquivo fisicamente
            }
        }
    }

    /**
     *
     * Send Image to Anymarket
     *
     * @param $storeID
     * @param $product
     * @param $variation
     *
     */
    public function sendImageToAnyMarket($storeID, $product, $variation){
        if($product){
            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);
            $exportImage = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_export_image_field', $storeID);

            $headers = array(
                "Content-type: application/json", 
                "gumgaToken: ".$TOKEN
            );

            if($product->getData('id_anymarket') != ''){
                $imgGetRet = $this->CallAPICurl("GET", $HOST."/v2/products/".$product->getData('id_anymarket')."/images", $headers, null);
                if($imgGetRet['error'] == '0'){
                    $imgsProdAnymarket = $imgGetRet['return'];
                    $imgsProdMagento = $product->getMediaGalleryImages();
                    
                    $arrAdd = array();
                    $ctrlAdd = false;
                    $arrRemove = array();
                    $ctrlRemove = false;
                    $arrImgs = array();

                    //verifica quais irao adicionar
                    foreach ($imgsProdMagento as $imgProdMagento) {
                        $ctrlAdd = false;

                        $urlImage = $imgProdMagento->getData('url');
                        $infoImg = getimagesize( $urlImage );
                        $imgSize = filesize( $imgProdMagento->getData('path') );

                        if( ($infoImg[0] != "") && ((float)$infoImg[0] < 350 || (float)$infoImg[1] < 350 || $imgSize > 4100000 )) {
                            if ($exportImage == 0) {
                                array_push($arrProd, 'Image_c (' . $urlImage . ' - Sku: ' . $product->getSku() . ' - Width: ' . $infoImg[0] . ' - Height: ' . $infoImg[1] . ' - Size: ' . $imgSize . ')');
                            } else {
                                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                $anymarketlog->setLogDesc('Error on export image - ' . $urlImage);
                                $anymarketlog->setLogId($product->getSku());
                                $anymarketlog->setStatus("1");
                                $anymarketlog->setStores(array($storeID));
                                $anymarketlog->save();
                            }
                        }else{
                            foreach ($imgsProdAnymarket as $imgProdAnymarket) {
                                if ($variation) {
                                    if( isset($imgProdAnymarket->variation) ) {
                                        if (($imgProdMagento->getData('url') == $imgProdAnymarket->url) && ($imgProdAnymarket->variation == $variation)) {
                                            $ctrlAdd = true;
                                            break;
                                        }
                                    }
                                } else {
                                    if ($imgProdMagento->getData('url') == $imgProdAnymarket->url) {
                                        $ctrlAdd = true;
                                        break;
                                    }
                                }

                            }

                            if (!$ctrlAdd) {
                                array_push($arrAdd, $imgProdMagento->getData('url'));
                            }
                        }

                    }

                    //verifica quais irao remover
                    foreach ($imgsProdAnymarket as $imgProdAnymarket) {
                        $ctrlRemove = false;
                        foreach ($imgsProdMagento as $imgProdMagento) {
                            if($variation){
                                if( isset($imgProdAnymarket->variation) ) {
                                    if (($imgProdAnymarket->url == $imgProdMagento->getData('url')) && ($imgProdAnymarket->variation == $variation)) {
                                        $ctrlRemove = true;
                                        break;
                                    }
                                }
                            }else{
                                if($imgProdAnymarket->url == $imgProdMagento->getData('url') ){
                                    $ctrlRemove = true;
                                    break;
                                }
                            }
                        }

                        if(!$ctrlRemove){
                            if($variation){
                                if( isset($imgProdAnymarket->variation) ) {
                                    if ($imgProdAnymarket->variation == $variation) {
                                        array_push($arrRemove, $imgProdAnymarket->id);
                                    }
                                }
                            }else{
                                array_push($arrRemove, $imgProdAnymarket->id);
                            }
                        }
                    }

                    // Add Image
                    if( !empty($arrImgs) ){
                        $returnProd['error'] = '1';
                        $returnProd['json'] = '';

                        $emptyFields = ' ';
                        foreach ($arrImgs as $field) {
                            $emptyFields .= $field.', ';
                        }

                        $returnProd['return'] = Mage::helper('db1_anymarket')->__('Product with inconsistency:').' '.$emptyFields;
                        $this->saveLogsProds($storeID, "0", $returnProd, $product);
                    }else {
                        foreach ($arrAdd as $imgAdd) {
                            if ($variation) {
                                $JSONAdd = array(
                                    "url" => $imgAdd,
                                    "variation" => $variation,
                                );
                            } else {
                                $JSONAdd = array(
                                    "url" => $imgAdd
                                );
                            }

                            $imgPostRet = $this->CallAPICurl("POST", $HOST . "/v2/products/" . $product->getData('id_anymarket') . "/images", $headers, $JSONAdd);
                            if ($imgPostRet['error'] == '1') {
                                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                $anymarketlog->setLogDesc('Error on export image (' . $product->getData('id_anymarket') . ') - ' . is_string($imgPostRet['return']) ? $imgPostRet['return'] : json_encode($imgPostRet['return']));
                                $anymarketlog->setLogJson($imgPostRet['json']);
                                $anymarketlog->setLogId($product->getSku());
                                $anymarketlog->setStatus("1");
                                $anymarketlog->setStores(array($storeID));
                                $anymarketlog->save();
                            } else {
                                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                $anymarketlog->setLogDesc('Exported image (' . $imgAdd . ')');
                                $anymarketlog->setLogJson($imgPostRet['json']);
                                $anymarketlog->setLogId($product->getSku());
                                $anymarketlog->setStatus("1");
                                $anymarketlog->setStores(array($storeID));
                                $anymarketlog->save();
                            }
                        }
                    }

                    // remove image
                    foreach ($arrRemove as $imgRemove) {
                        $imgDelRet = $this->CallAPICurl("DELETE", $HOST."/v2/products/".$product->getData('id_anymarket')."/images/".$imgRemove, $headers, null);
                        if($imgDelRet['error'] == '1'){
                            $anymarketlogDel = Mage::getModel('db1_anymarket/anymarketlog');

                            if( is_string($imgDelRet['return']) ){
                                $anymarketlogDel->setLogDesc( 'Error on delete image in Anymarket ('.$imgRemove.') - '.$imgDelRet['return']);
                            }else{
                                $anymarketlogDel->setLogDesc( 'Error on delete image in Anymarket ('.$imgRemove.') - '.json_encode($imgDelRet['return']));
                            }

                            $anymarketlogDel->setLogJson('');
                            $anymarketlogDel->setLogId($product->getSku());
                            $anymarketlogDel->setStatus("1");
                            $anymarketlogDel->setStores(array($storeID));
                            $anymarketlogDel->save();
                        }else{
                            $anymarketlogDel = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlogDel->setLogDesc( 'Deleted image from Anymarket ');
                            $anymarketlogDel->setLogJson('');
                            $anymarketlogDel->setLogId($product->getSku());
                            $anymarketlogDel->setStatus("1");
                            $anymarketlogDel->setStores(array($storeID));
                            $anymarketlogDel->save();
                        }
                    }

                }else{
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc( 'Error on get images from Anymarket ('.$product->getData('id_anymarket').') - '.$imgGetRet['return'] );
                    $anymarketlog->setStatus("1");
                    $anymarketlog->setStores(array($storeID));
                    $anymarketlog->save();
                }
            }
        }
    }

    /**
     * @param $product
     * @param $skusParam
     * @param $storeID
     */
    public function sendImageSkuToAnyMarket($storeID, $product, $skusParam) {
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

        $headers = array( 
            "Content-type: application/json", 
            "Cache-Control: no-cache",
            "gumgaToken: ".$TOKEN
        );

        // SINCRONIZA AS FOTOS E SKUS
        $skusProd = $this->CallAPICurl("GET", $HOST."/v2/products/".$product->getData('id_anymarket')."/skus", $headers, null);
        if($skusProd['error'] == '0') {
            foreach ($skusParam as $skuPut) {
                $prodSimple = Mage::getModel('catalog/product')->load($skuPut['internalIdProduct']);

                if ($prodSimple->getData('id_anymarket') != '') {
                    $paramSku = array(
                        "title" => $skuPut['title'],
                        "partnerId" => $skuPut['partnerId'],
                        "ean" => $skuPut['ean'],
                        "amount" => $skuPut['amount'],
                        "price" => $skuPut['price'],
                    );

                    if (isset($skuPut['variations'])) {
                        foreach ($skuPut['variations'] as $variationPut) {
                            $this->sendImageToAnyMarket($storeID, $prodSimple, $variationPut);
                        }
                        $paramSku['variations'] = $skuPut['variations'];
                    } else {
                        $this->sendImageToAnyMarket($storeID, $product, null);
                    }

                    $flagHSku = '';
                    if (isset($skusProd['return'])) {
                        foreach ($skusProd['return'] as $skuAM) {
                            if ($skuAM->partnerId == $prodSimple->getSku()) {
                                $flagHSku = $skuAM->id;
                                break;
                            }
                        }
                    }

                    if ($flagHSku != '') {
                        $skuProdReturn = $this->CallAPICurl("PUT", $HOST . "/v2/products/" . $product->getData('id_anymarket') . "/skus/" . $flagHSku, $headers, $paramSku);

                        if ($skuProdReturn['error'] == '0') {
                            $skuProdReturn['return'] = Mage::helper('db1_anymarket')->__('SKU Updated') . ' (' . $skuPut['partnerId'] . ')';
                        }
                    } else {
                        $skuProdReturn = $this->CallAPICurl("POST", $HOST . "/v2/products/" . $product->getData('id_anymarket') . "/skus", $headers, $paramSku);

                        if ($skuProdReturn['error'] == '0') {
                            $skuProdReturn['return'] = Mage::helper('db1_anymarket')->__('SKU Created') . ' (' . $skuPut['partnerId'] . ')';
                        }
                    }

                    $this->saveLogsProds($storeID, "1", $skuProdReturn, $prodSimple);
                    $this->updatePriceStockAnyMarket($storeID, $skuPut['internalIdProduct'], $skuPut['amount'], $skuPut['price']);
                }
            }
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc('Error on get Images Anymarket: '. $skusProd['return']);
            $anymarketlog->setLogJson($skusProd['json']);
            $anymarketlog->setLogId($product->getSku());
            $anymarketlog->setStatus("1");
            $anymarketlog->setStores(array($storeID));
            $anymarketlog->save();
        }
    }

    /**
     * @param $descBrand
     * @param $storeID
     * @return integer
     */
    public function getBrandForProduct($storeID, $descBrand){
        $brand = Mage::getModel('db1_anymarket/anymarketbrands')->load($descBrand, 'brd_name');
        if( $brand->getData('brd_id') == null ){
            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

            $headers = array( 
                "Content-type: application/json",
                "Accept: */*",
                "gumgaToken: ".$TOKEN
            );

            $param = array(
                "name" => $descBrand,
                "partnerId" => $descBrand
            );

            $returnBrands = $this->CallAPICurl("POST", $HOST."/v2/brands", $headers, $param);
            $brandsJSON = $returnBrands['return'];
            $return = null;
            if( $returnBrands['error'] == '0' ){
                $return = $brandsJSON->id;
            }else{
                if( isset($brandsJSON->data->brandId) ){
                    $return = $brandsJSON->data->brandId;
                }
            }

            if( $return ){
                $mBrands = Mage::getModel('db1_anymarket/anymarketbrands');
                $mBrands->setBrdId( $return );
                $mBrands->setBrdName( $descBrand );
                $mBrands->setStatus("1");
                $mBrands->setStores(array($storeID));
                $mBrands->save();

                return $return;
            }else{
                return '';
            }
            
        }else{
            return $brand->getData('brd_id');
        }
    }

    /**
     * send product to AnyMarket
     *
     * @param $idProduct
     * @return bool
     */
    public function sendProductToAnyMarket($storeID, $idProduct){
        //obter configuracoes
        $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load($idProduct);

        //Obtem os parametros dos attr para subir para o AM
        $model =              Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_model_field', $storeID);
        $brand =              Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_brand_field', $storeID);

        $volume_comprimento = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_comp_field', $storeID);
        $volume_altura =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_alt_field', $storeID);
        $volume_largura =     Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_larg_field', $storeID);
        $video_url =          Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_video_url_field', $storeID);
        $nbm        =         Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_field', $storeID);
        $nbm_origin =         Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_origin_field', $storeID);
        $ean =                Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_ean_field', $storeID);
        $warranty_text =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_text_field', $storeID);
        $warranty_time =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_time_field', $storeID);

        $price_factor =       Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_price_factor_field', $storeID);
        $calculated_price =   Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_calculated_price_field', $storeID);

        $arrProd = array();
        // verifica categoria null ou em branco
        $categProd = $product->getData('categoria_anymarket');
        if($categProd == null || $categProd == ''){
            array_push($arrProd, Mage::helper('db1_anymarket')->__('AnyMarket Category') );
        }

        // verifica o Price Factor (Markup)
        $varPriceFactor = $this->procAttrConfig($price_factor, $product->getData( $price_factor ), 1);
        if((string)(float)$varPriceFactor == $varPriceFactor) {
            $varPriceFactor = (float)$varPriceFactor;
            if($varPriceFactor > 99){
               array_push($arrProd, Mage::helper('db1_anymarket')->__('AnyMarket Price Factor(Limit 99)'));
            }
        }else{
            array_push($arrProd, Mage::helper('db1_anymarket')->__('AnyMarket Price Factor(Only Number)') );
        }

        // verifica Origin null ou em branco
        $originData = $this->procAttrConfig($nbm_origin, $product->getData( $nbm_origin ), 1);
        if($originData == null || $originData == ''){
           array_push($arrProd, Mage::helper('db1_anymarket')->__('AnyMarket Origin') );
        }

        //trata para nao enviar novamente solicitacao quando o erro for o mesmo
        if( ($product->getData('id_anymarket') == '') || ($product->getData('id_anymarket') == '0') ){
            $prodErrorCtrl = Mage::getModel('db1_anymarket/anymarketproducts')->setStoreId($storeID)
                                                                              ->load($product->getId(), 'nmp_id');
            if( $prodErrorCtrl->getData('nmp_id') != null ){
                $descError = $prodErrorCtrl->getData('nmp_desc_error');

                // Trata para nao ficar disparando em cima da Duplicadade de SKU
                $mesgDuplSku = strrpos($descError, "Duplicidade de SKU:");
                if ($mesgDuplSku !== false) {
                    $oldSkuErr = $this->getBetweenCaract($descError, '"', '"');

                    if($oldSkuErr == $product->getSku()){
                        array_push($arrProd, 'Duplicidade de SKU: '.Mage::helper('db1_anymarket')->__('Already existing SKU in anymarket').' "'.$oldSkuErr.'".');
                    }
                }
            }
        }

        if( !empty($arrProd) ){
            $returnProd['error'] = '1';
            $returnProd['json'] = '';

            $emptyFields = ' ';
            foreach ($arrProd as $field) {
                $emptyFields .= $field.', ';
            }

            $returnProd['return'] = Mage::helper('db1_anymarket')->__('Product with inconsistency:').$emptyFields;
            $this->saveLogsProds($storeID, "0", $returnProd, $product);

            return false;
        }else{
            $arrProd = array();

            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

            $MassUnit = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_weight_field', $storeID);
            $UnitMeasurement = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_size_field', $storeID);

            //verifica se o produto e configurable
            $confID = "";
            $Weight = "";
            if($product->getTypeID() == "configurable"){
                $confID = $product->getId();
            }else{
                // verifica se é um simples pertecente a um Configurable
                $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                $Weight = $product->getWeight();
                if (isset($parentIds[0])) {
                    $confID = $parentIds[0];
                    $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load($confID);
                }
            }

            //obtem as imagens do produto(Config ou Simples)
            $itemsIMG = array();
            $galleryData = $product->getMediaGalleryImages();
            $exportImage = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_export_image_field', $storeID);
            foreach($galleryData as $g_image) {
                $infoImg = getimagesize($g_image['url']);
                $imgSize = filesize($g_image['path']);

                if( ($infoImg[0] != "") && ((float)$infoImg[0] < 350 || (float)$infoImg[1] < 350 || $imgSize > 4100000) ){
                    if($exportImage == 0) {
                        array_push($arrProd, 'Image_a (' . $g_image['url'] . ' - Sku: ' . $product->getSku() . ' - Width: ' . $infoImg[0] . ' - Height: ' . $infoImg[1] . ' - Size: ' . $imgSize . ')');
                    }else{
                        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                        $anymarketlog->setLogDesc('Error on export image - ' . $g_image['url']);
                        $anymarketlog->setLogId($product->getSku());
                        $anymarketlog->setStatus("1");
                        $anymarketlog->setStores(array($storeID));
                        $anymarketlog->save();
                    }
                }else{
                    $itemsIMG[] = array(
                        "main" => true,
                        "url" => $g_image['url']
                    );
                }
            }

            //obtem os produtos configs - verifica se e configurable
            $ArrSimpleConfigProd = array();
            if($confID != ""){
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
                $attributesConf = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product); 

                foreach($childProducts as $child) {
                    $SimpleConfigProd = Mage::getModel('catalog/product')->load($child->getId());

                    if ($Weight == "") {
                        $Weight = $SimpleConfigProd->getWeight();
                    }

                    //obtem os atributos do configuravel
                    $qtyStore = $this->getAllStores();
                    if (count($qtyStore) > 1) {
                        $storeIDAttrVar = $storeID;
                    } else {
                        $fArr = array_shift($qtyStore);
                        $storeIDAttrVar = $fArr['store_id'];
                    }

                    $ArrVariationValues = array();
                    foreach ($attributesConf as $attribute) {
                        $options = Mage::getResourceModel('eav/entity_attribute_option_collection');
                        $valuesAttr = $options->setAttributeFilter($attribute['attribute_id'])
                            ->setStoreFilter($storeIDAttrVar)
                            ->toOptionArray();

                        foreach ($valuesAttr as $value) {
                            $childValue = $child->getData($attribute['attribute_code']);
                            if ($value['value'] == $childValue) {
                                $ArrVariationValues[$attribute['store_label']] = $value['label'];
                            }
                        }
                    }

                    //obtem as imagens do produto (Obtem os simples e relaciona as variacoes)
                    $galleryDataSimp = $SimpleConfigProd->getMediaGalleryImages();
                    foreach ($galleryDataSimp as $g_imageSimp) {
                        $infoImg = getimagesize($g_imageSimp['url']);
                        $imgSize = filesize($g_imageSimp['path']);

                        if (($infoImg[0] != "") && ((float)$infoImg[0] < 350 || (float)$infoImg[1] < 350 || $imgSize > 4100000)) {
                            if ($exportImage == 0) {
                                array_push($arrProd, 'Image_b (' . $g_imageSimp['url'] . ' - Sku: ' . $SimpleConfigProd->getSku() . ' - Width: ' . $infoImg[0] . ' - Height: ' . $infoImg[1] . ' - Size: ' . $imgSize . ')');
                            } else {
                                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                $anymarketlog->setLogDesc('Error on export image - ' . $g_imageSimp['url']);
                                $anymarketlog->setLogId($SimpleConfigProd->getSku());
                                $anymarketlog->setStatus("1");
                                $anymarketlog->setStores(array($storeID));
                                $anymarketlog->save();
                            }
                        } else {
                            foreach ($ArrVariationValues as $value) {
                                $itemsIMG[] = array(
                                    "main" => false,
                                    "url" => $g_imageSimp['url'],
                                    "variation" => $value,
                                );
                            }
                        }
                    }

                    $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));

                    if ($filter == 'final_price') {
                        $stkPrice = $SimpleConfigProd->getFinalPrice();
                    } else {
                        $stkPrice = $SimpleConfigProd->getData($filter);
                    }

                    $simpConfProdSku = $SimpleConfigProd->getSku();
                    // verificacao dos dados de price
                    if (($stkPrice == null) || ($stkPrice == '') || ((float)$stkPrice <= 0)) {
                        array_push($arrProd, 'Price (' . $simpConfProdSku . ')');
                    }

                    // verificacao dos dados de SKU
                    $cValid = array('.', '-', '_');

                    if (!ctype_alnum(str_replace($cValid, '', $simpConfProdSku))) {
                        array_push($arrProd, 'SKU (' . $simpConfProdSku . ')');
                    }

                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($SimpleConfigProd);
                    if ($SimpleConfigProd->getData('integra_anymarket') == 1 && $SimpleConfigProd->getStatus() == 1){
                        $ArrSimpleConfigProd[] = array(
                            "variations" => $ArrVariationValues,
                            "price" => $stkPrice,
                            "amount" => $stock->getQty(),
                            "ean" => $SimpleConfigProd->getData($ean),
                            "partnerId" => $simpConfProdSku,
                            "title" => $SimpleConfigProd->getName(),
                            "idProduct" => $SimpleConfigProd->getData('id_anymarket'),
                            "internalIdProduct" => $SimpleConfigProd->getId(),
                        );
                }

                }

            }

            //ajusta o array de skus
            if( count($ArrSimpleConfigProd) <= 0 ){
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));

                if($filter == 'final_price'){
                    $stkPrice = $product->getFinalPrice();
                }else{
                    $stkPrice = $product->getData($filter);
                }

                $prodSkuJ = $product->getSku();

                // verificacao dos dados de price
                if(($stkPrice == null) || ($stkPrice == '')  || ((float)$stkPrice <= 0)){
                    array_push($arrProd, 'Price ('.$prodSkuJ.')');
                }

                // verificacao dos dados de SKU
                $cValid = array('.', '-', '_'); 
                if(!ctype_alnum(str_replace($cValid, '', $prodSkuJ))) { 
                    array_push($arrProd, 'SKU ('.$prodSkuJ.')');
                }

                if ($product->getData('integra_anymarket') == 1 && $product->getStatus() == 1) {
                    $ArrSimpleConfigProd[] = array(
                        "price" => $stkPrice,
                        "amount" => $stock->getQty(),
                        "ean" => $product->getData($ean),
                        "partnerId" => $prodSkuJ,
                        "title" => $product->getName(),
                        "idProduct" => $product->getData('id_anymarket'),
                        "internalIdProduct" => $product->getId(),
                    );
                }
            }

            
            //cria os headers
            $headers = array( 
                "Content-type: application/json", 
                "Cache-Control: no-cache",
                "gumgaToken: ".$TOKEN
            );

            $idProductAnyMarket = null;
            if($product->getData('id_anymarket') != ""){
                $idProductAnyMarket = $product->getData('id_anymarket');
            }


            //cria os custom attributes
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($product->getAttributeSetId());
            $attributeSetName  = $attributeSetModel->getAttributeSetId();

            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setAttributeSetFilter($attributeSetName)
            ->getItems();

            $ArrAttributes = array();
            $contIndexAttr = 0;
            foreach ($attributes as $attribute){
                $attrCheck =  Mage::getModel('db1_anymarket/anymarketattributes')->load($attribute->getAttributeId(), 'nma_id_attr');
                if($attrCheck->getData('nma_id_attr') != null){
                    if($attrCheck->getData('status') == 1){
                        if( $attribute->getAttributeCode() != $model ){
                            if(!$this->checkArrayAttributes($ArrAttributes, "description", $attribute->getFrontendLabel())){
                                if($confID == ""){
                                    $valAttr = $this->procAttrConfig($attribute->getAttributeCode(), $product->getData( $attribute->getAttributeCode() ), 1);
                                    if( $valAttr != null || $valAttr != '' ){
                                        $ArrAttributes[] = array("index" => $contIndexAttr, "name" => $attribute->getFrontendLabel(), "value" => $valAttr);
                                        $contIndexAttr = $contIndexAttr+1;
                                    }
                                }else{
                                    foreach ($attributesConf as $attributeConf){
                                        if(!in_array($attribute->getAttributeCode(), $attributeConf)){
                                            if(!$this->checkArrayAttributes($ArrAttributes, "description", $attribute->getFrontendLabel())){
                                                $valAttr = $this->procAttrConfig($attribute->getAttributeCode(), $product->getData( $attribute->getAttributeCode() ), 1);
                                                if( $valAttr != null || $valAttr != '' ){
                                                    $ArrAttributes[] = array("index" => $contIndexAttr, "name" => $attribute->getFrontendLabel(), "value" => $valAttr);
                                                    $contIndexAttr = $contIndexAttr+1;
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

            //trata as dimensoes
            $vHeight = $this->procAttrConfig($volume_altura, $product->getData( $volume_altura ), 1);
            $vWidth  = $this->procAttrConfig($volume_largura, $product->getData( $volume_largura ), 1);
            $vLength = $this->procAttrConfig($volume_comprimento, $product->getData( $volume_comprimento ), 1);

            //Cria os params
            $param = array(
                "id" => $idProductAnyMarket,
                "title" => $product->getName(),
                "description" => $this->getFullDescription($storeID, $product),
                "nbm" => array(
                    "id" => $this->procAttrConfig($nbm, $product->getData( $nbm ), 1)
                ),
                "brand" => array(
                    "id" => $this->getBrandForProduct($storeID, $this->procAttrConfig($brand, $product->getData( $brand ), 1)),
                    "name" => $this->procAttrConfig($brand, $product->getData( $brand ), 1)
                ),
                "origin" => array(
                    "id" => $this->procAttrConfig($nbm_origin, $product->getData( $nbm_origin ), 1)
                ),
                "category" => array(
                    "id" => $product->getData('categoria_anymarket')
                ),
                "model" =>  $this->procAttrConfig($model, $product->getData( $model ), 1),
                "warrantyText" => $this->procAttrConfig($warranty_text, $product->getData( $warranty_text ), 1),
                "warrantyTime" => $this->procAttrConfig($warranty_time, $product->getData( $warranty_time ), 1),
                "weight" => $MassUnit == 0 ? $Weight/1 : $Weight/1000,
                "height" => $this->convertUnitMeasurement($UnitMeasurement, $vHeight, 1),
                "width"  => $this->convertUnitMeasurement($UnitMeasurement, $vWidth,  1),
                "length" => $this->convertUnitMeasurement($UnitMeasurement, $vLength, 1),
                "images" => $itemsIMG,
                "priceFactor" => $varPriceFactor,
                "calculatedPrice" => $product->getData( $calculated_price ) == 0 ? false : true,
                // OBTER ATRIBUTOS CUSTOM
                "characteristics" => $ArrAttributes,
                "skus" => $ArrSimpleConfigProd,
            );

            $varVideoURL = $this->procAttrConfig($video_url, $product->getData( $video_url ), 1);
            if($varVideoURL && $varVideoURL != ""){
                $param["videoUrl"] = $varVideoURL;
            }

            if( !empty($arrProd) ){
                $returnProd['error'] = '1';
                $returnProd['json'] = '';

                $emptyFields = ' ';
                foreach ($arrProd as $field) {
                    $emptyFields .= $field.', ';
                }

                $returnProd['return'] = Mage::helper('db1_anymarket')->__('Product with inconsistency:').' '.$emptyFields;
                $this->saveLogsProds($storeID, "0", $returnProd, $product);

                return false;
            }else{
                if( ($product->getData('id_anymarket') == '') || ($product->getData('id_anymarket') == '0') ){
                    $returnProd = $this->CallAPICurl("POST", $HOST."/v2/products/", $headers, $param);

                    $IDinAnymarket = '0';
                    if($returnProd['error'] != '1'){
                        $SaveLog = $returnProd['return'];
                        $IDinAnymarket = json_encode($SaveLog->id);

                        if($IDinAnymarket != '0'){
                            $productForSave = Mage::getModel('catalog/product')->setStoreId($storeID)->load($product->getId());
                            $productForSave->setIdAnymarket($IDinAnymarket);
                            $productForSave->save();
                        }

                        if($product->getTypeID() == "configurable"){
                            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

                            if(is_array($childProducts)) {
                                foreach ($childProducts as $child) {
                                    Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
                                    $productC = Mage::getModel('catalog/product')->setStoreId($storeID)->load($child->getId());

                                    if ($productC->getIntegraAnymarket() != '1') {
                                        $productC->setIntegraAnymarket('1');
                                    }
                                    if ($IDinAnymarket != '0') {
                                        $productC->setIdAnymarket($IDinAnymarket);
                                    }
                                    $productC->save();
                                    Mage::app()->setCurrentStore( $this->getCurrentStoreView() );
                                }
                            }
                        }

                        if($IDinAnymarket != '0'){
                            $returnProd['error'] = '0';
                            $returnProd['return'] = Mage::helper('db1_anymarket')->__('Successfully synchronized product.');
                            $this->saveLogsProds($storeID, "1", $returnProd, $product);
                        }else{
                            $returnProd['error'] = '1';
                            $returnProd['return'] = Mage::helper('db1_anymarket')->__('Error synchronizing, code anymarket invalid.');
                            $this->saveLogsProds($storeID, "0", $returnProd, $product);
                        }

                    }else{
                        $this->saveLogsProds($storeID, "1", $returnProd, $product);
                    }

                }else{
                    $returnProd = $this->CallAPICurl("PUT", $HOST."/v2/products/".$product->getData('id_anymarket'), $headers, $param);
                    if($returnProd['error'] == '0'){
                        $returnProd['return'] = Mage::helper('db1_anymarket')->__('Product Updated');
                    }

                    //ADICIONA UM NOVO SKU
                    foreach ($ArrSimpleConfigProd as $skuPut) {
                        $skuProdReturn = $this->CallAPICurl("POST", $HOST."/v2/products/".$product->getData('id_anymarket')."/skus", $headers, $skuPut);
                        if($skuProdReturn['error'] == '0'){
                            $skuProdReturn['return'] = Mage::helper('db1_anymarket')->__('SKU Created').' ('.$skuPut['partnerId'].')';
                            $this->saveLogsProds($storeID, "1", $skuProdReturn, $product);
                        }else{
                            $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                            $productSku = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $skuPut['partnerId'] );
                            if( $productSku != null ) {
                                if ($productSku->getData() != null) {
                                    if ($productSku->getId() != null) {
                                        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productSku);
                                        $this->updatePriceStockAnyMarket($storeID, $productSku->getId(), $stock->getQty(), $productSku->getData($filter));
                                    }
                                }
                            }
                        }
                    }

                    $this->sendImageSkuToAnyMarket($storeID, $product, $param['skus']);

                    $this->saveLogsProds($storeID, "1", $returnProd, $product);
                }
                return true;
            }
        }

    }

    /**
     * @param $HOST
     * @param $headers
     * @param $IDTransmission
     * @param $statusTransmission
     * @param $tokenTransmissions
     */
    private function changeStatusTransmission($HOST, $headers, $IDTransmission, $statusTransmission, $tokenTransmissions){
        $params = array(
            "marketPlaceStatus" => $statusTransmission." Sincronizado"
        );

        $returnChangeTrans = $this->CallAPICurl("PUT", $HOST."/v2/transmissions/".$IDTransmission, $headers, $params);
        if($returnChangeTrans['error'] == '1'){
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error on change transmissions status: '). $returnChangeTrans['return'] );
            $anymarketlog->setStatus("0");
            $anymarketlog->save();
        }

        if($tokenTransmissions != 'notoken'){
            $paramsFeeds = array(
                "token" => $tokenTransmissions
            );

            $returnChangeTrans = $this->CallAPICurl("PUT", $HOST."/v2/transmissions/feeds/".$IDTransmission, $headers, $paramsFeeds);
            if($returnChangeTrans['error'] == '1'){
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error update feeds transmissions'));
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }
        }

    }

    /**
     * get only product in feed of AnyMarket
     */
    public function getFeedProdsFromAnyMarket($storeID){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $returnFeedTrans = $this->CallAPICurl("GET", $HOST."/v2/transmissions/feeds?limit=100", $headers, null);

        if($returnFeedTrans['error'] == '1'){
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( Mage::helper('db1_anymarket')->__('Error on get transmissions feed '). $returnFeedTrans['return'] );
            $anymarketlog->setStatus("0");
            $anymarketlog->save();
        }else{
            $prodCreated = $this->getSpecificFeedProduct($storeID, $returnFeedTrans['return'], $headers, $HOST);

            // TRATA STOCK
            if ( $prodCreated ) {
                $typeSincOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
                if ($typeSincOrder == 1) {
                    $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                    $ProdStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($prodCreated);
                    $this->updatePriceStockAnyMarket($storeID, $prodCreated->getId(), $ProdStock->getQty(), $prodCreated->getData($filter));
                }
            }
        }
    }

    /**
     * @param $IDProd
     * @param $storeID
     */
    public function getStockProductAnyMarket($storeID, $IDProd){
        $product = Mage::getModel('catalog/product')->load( $IDProd );
        if($product->getIdAnymarket() != ''){
            $HOST = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

            $headers = array(
                "Content-type: application/json",
                "Accept: */*",
                "gumgaToken: " . $TOKEN
            );

            $returnProdSpecific = $this->CallAPICurl("GET", $HOST . "/v2/products/" . $product->getIdAnymarket(), $headers, null);
            if ($returnProdSpecific['error'] == '0') {
                $ProdsJSON = $returnProdSpecific['return'];

                foreach ($ProdsJSON->skus as $sku) {
                    $IDSKUProd = $sku->partnerId != null ? $sku->partnerId : $ProdsJSON->idProduct;
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $IDSKUProd);

                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                    $stockItem->setData('is_in_stock', $sku->amount > 0 ? '1' : '0');
                    $stockItem->setData('qty', $sku->amount);
                    $stockItem->save();

                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc(Mage::helper('db1_anymarket')->__('Imported stock SKU: ') . $product->getData('sku'));
                    $anymarketlog->setLogId($product->getId());
                    $anymarketlog->setStatus("0");
                    $anymarketlog->setStores(array($storeID));
                    $anymarketlog->save();

                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getIdAnymarket(), 'nmp_id');
                    if ($anymarketproducts->getNmpId() == null) {
                        $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');
                    }

                    $anymarketproducts->setNmpId($product->getId());
                    $anymarketproducts->setNmpSku($product->getData('sku'));
                    $anymarketproducts->setNmpName($product->getData('name'));
                    $anymarketproducts->setNmpDescError("");
                    $anymarketproducts->setNmpStatusInt("Integrado");
                    $anymarketproducts->setStatus("1");
                    $anymarketproducts->setStores(array($storeID));
                    $anymarketproducts->save();
                }
            } else {
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc("Erro on get stock " . $returnProdSpecific['return']);
                $anymarketlog->setLogId($IDProd);
                $anymarketlog->setStatus("0");
                $anymarketlog->setStores(array($storeID));
                $anymarketlog->save();
            }
        }
    }


    /**
     * @param $listTransmissions
     * @param $headers
     * @param $HOST
     * @param $storeID
     *
     * @return string
     */
    public function getSpecificFeedProduct($storeID, $listTransmissions, $headers, $HOST){
        $arrJSONProds = array();
        $arrControlProds = array();

        Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        foreach ($listTransmissions as $transmissionIDs) {
            $transmissionID = $transmissionIDs->id;
            $transmissionToken = $transmissionIDs->token;

            $transmissionReturn = $this->CallAPICurl("GET", $HOST."/v2/transmissions/".$transmissionID, $headers, null);

            $prodRet = "";
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
            if($typeSincProd == 1) {
                if ($transmissionReturn['error'] == '1') {
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc(Mage::helper('db1_anymarket')->__('Error on get transmissions ') . $transmissionReturn['return']);
                    $anymarketlog->setStatus("0");
                    $anymarketlog->save();
                } else {
                    $transmission = $transmissionReturn['return'];
                    $statusTransmission = $transmission->publicationStatus;
                    $IDProdTrans = $transmission->product->id;
                    $NameProdTrans = $transmission->product->title;
                    if ($statusTransmission == 'ACTIVE') {
                        $arrVarSku = array();
                        if (isset($transmission->sku->variations)) {
                            foreach ($transmission->sku->variations as $variation) {
                                array_push($arrVarSku, array(
                                    "id" => $variation->id,
                                    "description" => $variation->description,
                                    "variationTypeId" => $variation->type->id,
                                    "variationTypeName" => $variation->type->name
                                ));

                            }
                        }

                        $imagesGallery = array();
                        if( isset($transmission->images) ) {
                            foreach ($transmission->images as $image) {
                                $imagesGallery[] = array(
                                    "standard_resolution" => $image->standardUrl,
                                    "original" => $image->standardUrl,
                                    "main" => $image->main,
                                    "variationValue" => isset($image->variation) ? $image->variation : null
                                );
                            }
                        }

                        $arrVarGeral = array();
                        if (isset($transmission->sku->variations)) {
                            foreach ($transmission->sku->variations as $varSKU) {
                                array_push($arrVarGeral, array(
                                    "name" => $varSKU->type->name,
                                    "id" => $varSKU->type->id
                                ));
                            }
                        }

                        if (!in_array($IDProdTrans, $arrControlProds)) {
                            array_push($arrControlProds, $IDProdTrans);

                            $arrAttr = array();
                            if (isset($transmission->characteristics)) {
                                foreach ($transmission->characteristics as $carac) {
                                    array_push($arrAttr, array(
                                        "name" => $carac->name,
                                        "value" => $carac->value
                                    ));
                                }
                            }

                            $arrJSONProds[$IDProdTrans] = array(
                                "id" => $IDProdTrans,
                                "title" => $transmission->product->title,
                                "idTransmission" => $transmissionIDs->id,
                                "description" => isset($transmission->description) ? $transmission->description : null,
                                "brand" => isset($transmission->brand->id) ? $transmission->brand->id : null,
                                "model" => isset($transmission->model) ? $transmission->model : null,
                                "videoURL" => isset($transmission->videoUrl) ? $transmission->videoUrl : null,
                                "warrantyTime" => isset($transmission->warrantyTime) ? $transmission->warrantyTime : null,
                                "warranty" => isset($transmission->warrantyText) ? $transmission->warrantyText : null,
                                "height" => isset($transmission->height) ? $transmission->height : null,
                                "width" => isset($transmission->width) ? $transmission->width : null,
                                "weight" => isset($transmission->weight) ? $transmission->weight : null,
                                "length" => isset($transmission->length) ? $transmission->length : null,
                                "originCode" => isset($transmission->origin->id) ? $transmission->origin->id : null,
                                "nbm" => isset($transmission->nbm) ? $transmission->nbm->id : null,
                                "category" => isset($transmission->category->id) ? $transmission->category->id : null,
                                "photos" => $imagesGallery,
                                "variations" => $arrVarGeral,
                                "attributes" => $arrAttr,
                                "skus" => array(
                                    array(
                                        "id" => $transmission->sku->id,
                                        "title" => empty($arrVarGeral) ? $transmission->product->title : $transmission->sku->title,
                                        "idProduct" => $IDProdTrans,
                                        "idInClient" => isset($transmission->sku->idInClient) ? $transmission->sku->idInClient : $transmission->sku->partnerId,
                                        "price" => $transmission->sku->price,
                                        "stockAmount" => $transmission->sku->amount,
                                        "ean" => isset($transmission->sku->ean) ? $transmission->sku->ean : null,
                                        "variations" => $arrVarSku
                                    )
                                ),
                            );
                        } else {
                            foreach ($arrVarGeral as $variationGeral) {
                                if (!in_array($variationGeral, $arrJSONProds[$IDProdTrans]['variations'])) {
                                    array_push($arrJSONProds[$IDProdTrans]['variations'], $variationGeral);
                                }
                            }

                            foreach ($imagesGallery as $imageG) {
                                if (!in_array($imageG, $arrJSONProds[$IDProdTrans]['photos'])) {
                                    array_push($arrJSONProds[$IDProdTrans]['photos'], $imageG);
                                }
                            }
                            array_push($arrJSONProds[$IDProdTrans]['skus'], array(
                                "id" => $transmission->sku->id,
                                "title" => $transmission->sku->title,
                                "idProduct" => $IDProdTrans,
                                "idInClient" => isset($transmission->sku->idInClient) ? $transmission->sku->idInClient : $transmission->sku->partnerId,
                                "price" => $transmission->sku->price,
                                "stockAmount" => $transmission->sku->amount,
                                "ean" => isset($transmission->sku->ean) ? $transmission->sku->ean : null,
                                "variations" => $arrVarSku
                            ));
                        }
                    } else if ($statusTransmission == 'PAUSED') {
                        $prodLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', isset($transmission->sku->partnerId) ? $transmission->sku->partnerId : $IDProdTrans);
                        if ($prodLoaded != null) {
                            if ($prodLoaded->getData('integra_anymarket') == 1) {
                                $prodLoaded->setStatus(2);
                                $prodLoaded->save();

                                $this->changeStatusTransmission($HOST, $headers, $transmissionID, 'Pausado', $transmissionToken);
                                $prodRet = 'Transmission Paused - '.$prodLoaded->getSku();
                            }
                        }
                    } else if ($statusTransmission == 'CLOSED') {
                        $prodLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', isset($transmission->sku->partnerId) ? $transmission->sku->partnerId : $IDProdTrans);
                        if ($prodLoaded != null) {
                            if ($prodLoaded->getData('integra_anymarket') == 1) {
                                $prodLoaded->setStatus(2);
                                $prodLoaded->save();

                                $this->changeStatusTransmission($HOST, $headers, $transmissionID, 'Finalizado', $transmissionToken);
                                $prodRet = 'Transmission Closed - '.$prodLoaded->getSku();
                            }
                        }
                    } elseif ($statusTransmission == 'WITHOUT_STOCK') {
                        $prodLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', isset($transmission->sku->partnerId) ? $transmission->sku->partnerId : $IDProdTrans);
                        if ($prodLoaded != null) {
                            if ($prodLoaded->getData('integra_anymarket') == 1) {

                                // DECREMENTE O STOCK
                                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($prodLoaded->getId());
                                if ($stockItem->getManageStock()) {
                                    //$prodLoaded->setStatus(2);
                                    $stockItem->setData('qty', 0);
                                    $stockItem->setData('is_in_stock', 0);
                                    $stockItem->save();
                                }
                                $prodLoaded->save();

                                $prodRet = 'Product Without Stock - '.$prodLoaded->getSku();
                                $this->changeStatusTransmission($HOST, $headers, $transmissionID, 'Sem Estoque', $transmissionToken);
                            }
                        }
                    }
                }
            }

            $typeSincOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
            if( $typeSincOrder == 0 ){
                if( $transmissionReturn['error'] == '0' ) {
                    $transmissionStock = $transmissionReturn['return'];

                    $skuToLoad = isset($transmissionStock->sku->partnerId) ? $transmissionStock->sku->partnerId : $transmissionStock->product->id;
                    $prodLoaded = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $skuToLoad);
                    if ($prodLoaded != null) {
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($prodLoaded->getId());
                        if ($stockItem->getManageStock()) {
                            $stockItem->setData('qty', $transmissionStock->sku->amount);
                            if ($transmissionStock->sku->amount > 0) {
                                $stockItem->setData('is_in_stock', 1);
                            } else {
                                $stockItem->setData('is_in_stock', 0);
                            }
                            $stockItem->save();
                        }
                        $prodLoaded->save();

                        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                        $anymarketlog->setLogDesc( "Stock Updated" );
                        $anymarketlog->setLogId( $prodLoaded->getSku() );
                        $anymarketlog->setStatus("0");
                        $anymarketlog->setStores(array($storeID));
                        $anymarketlog->save();

                        $prodRet = $prodLoaded->getSku()." - Stock Updated";
                    }else{
                        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                        $anymarketlog->setLogDesc( "Product not found (".$skuToLoad.") - Stock Updated" );
                        $anymarketlog->setStatus("0");
                        $anymarketlog->setStores(array($storeID));
                        $anymarketlog->save();

                        $prodRet = "Product not found (".$skuToLoad.") - Stock Updated";
                    }
                }else{
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc( $transmissionReturn['return'] . " - Update Stock" );
                    $anymarketlog->setStatus("0");
                    $anymarketlog->setStores(array($storeID));
                    $anymarketlog->save();

                    $prodRet = $transmissionReturn['return'] . " - Update Stock";
                }
            }
        }

        //CRIA OS PRODUTOS CASO ELES NAO EXISTAM
        foreach ($arrJSONProds as $ProdsJSON) {
            $feedReturn = $this->createProducts($storeID, json_encode($ProdsJSON));
            if($feedReturn){
                $this->changeStatusTransmission($HOST, $headers, $ProdsJSON["idTransmission"], 'Ativo', $transmissionToken);

                $returnProd = array();
                $returnProd['return'] = 'Product Created or updated.';
                $returnProd['json'] = '';
                $returnProd['error'] = '0';
                $this->saveLogsProds($storeID, "1", $returnProd, $feedReturn);

                $prodRet = 'Product Created or updated.';
            }
        }

        return $prodRet;
    }


    /**
     * @param $ProdsJSON
     * @param $storeID
     * @return Mage_Catalog_Model_Product
     */
    public function createProducts($storeID, $ProdsJSON){
        Mage::getSingleton('core/session')->setImportProdsVariable('false');
        $ProdsJSON = json_decode($ProdsJSON);

        $ProdCrt = null;
        $websiteID = Mage::getModel('core/store')->load($storeID)->getWebsiteId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $typeSincOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);

        $priceField = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
        if($priceField == 'final_price'){
            $priceField = 'price';

            $config = new Mage_Core_Model_Config();
            $config->saveConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', 'price', 'default', $storeID);

            Mage::app()->getCacheInstance()->cleanType('config');
        }

        $brand =    Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_brand_field', $storeID);
        $nbm   =    Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_field', $storeID);
        $model =    Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_model_field', $storeID);
        $MassUnit = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_weight_field', $storeID);
        $UnitMeasurement = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_size_field', $storeID);
        $AttrSet  = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_atribute_set_field', $storeID);

        $volume_comprimento = Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_comp_field', $storeID);
        $volume_altura =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_alt_field', $storeID);
        $volume_largura =     Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_vol_larg_field', $storeID);
        $video_url =          Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_video_url_field', $storeID);
        $nbm_origin =         Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_nbm_origin_field', $storeID);
        $ean =                Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_ean_field', $storeID);
        $warranty_text =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_text_field', $storeID);
        $warranty_time =      Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_warranty_time_field', $storeID);

        $configureFieldsConfig = $this->getFieldsDescriptionConfig($storeID);

        //PROD CONFIGURABLE
        if ( !empty( $ProdsJSON->variations ) ) {
            $prodSimpleFromConfig = array();
            $AttributeIds = array();
            $AttributeOptions = array();

            $variationArray = array();
            $sinc = '';
            foreach ($ProdsJSON->variations as $variation) {
                $variationArray[$variation->id] = $variation->name;
                $AttrCtlr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $variation->name);
                if(!$AttrCtlr->getData()){
                    $sinc = $variation->name;
                    break;
                }
            }

            if($sinc == ''){
                foreach ($ProdsJSON->skus as  $sku) {
                    $IDSKUProd = $sku->idInClient != null ? $sku->idInClient : $sku->id;
                    $ProdCrt = '';
                    $product = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $IDSKUProd);

                    foreach ($sku->variations as  $varValues) {
                        $descVar = $varValues->description;
                        $idVar = $varValues->variationTypeId;
                    }

                    //trata as dimensoes
                    $vHeight = $this->procAttrConfig($volume_altura,      $ProdsJSON->height, 0);
                    $vWidth  = $this->procAttrConfig($volume_largura,     $ProdsJSON->width,  0);
                    $vLength = $this->procAttrConfig($volume_comprimento, $ProdsJSON->length, 0);

                    if(!$product){
                        $AttributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $variationArray[ $idVar ]);
                        if (!in_array($AttributeId, $AttributeIds)) {
                            $AttributeIds[] = $AttributeId;
                            $collectionAttr = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                             ->setPositionOrder('asc')
                                             ->setAttributeFilter($AttributeId)
                                             ->setStoreFilter(0)
                                             ->load();

                            $AttributeOptions[$idVar] = $collectionAttr->toOptionArray();

                        }

                        $varAttr = '';
                        $descVarAttr = '';
                        foreach ( $AttributeOptions[$idVar] as $attrOpt) {
                            if($attrOpt['label'] == $descVar ){
                                $varAttr = $attrOpt['value'];
                                $descVarAttr = $attrOpt['label'];
                                break;
                            }
                        }

                        $imagesGallery = array();
                        foreach ($ProdsJSON->photos as $image) {
                            if( $image->variationValue != null ){
                                if( $image->variationValue == $descVarAttr ){
                                    $imagesGallery[] = array('img' => $image->standard_resolution, 'main' => $image->main);
                                }
                            }
                        }

                        if($varAttr != ''){
                            $dataPrd = array(
                                    'attribute_set_id' => $AttrSet == null ? Mage::getModel('catalog/product')->getDefaultAttributeSetId() : $AttrSet,
                                    'type_id' =>  'simple',
                                    'sku' => $IDSKUProd,
                                    'name' => $sku->title,
                                    'description' => $sku->title,
                                    'short_description' => $sku->title,
                                    $priceField => $sku->price,
                                    'created_at' =>  strtotime('now'),
                                    'updated_at' =>  strtotime('now'),
                                    'id_anymarket' => $sku->idProduct,
                                    'weight' => $MassUnit == 1 ? $ProdsJSON->weight*1000 : $ProdsJSON->weight,
                                    'store_id' => $storeID,
                                    'website_ids' => array($websiteID),
                                    $brand => $this->procAttrConfig($brand, $ProdsJSON->brand, 0),
                                    $model => $this->procAttrConfig($model, $ProdsJSON->model, 0),
                                    $video_url => $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0),

                                    $volume_comprimento => $this->convertUnitMeasurement($UnitMeasurement, $vLength, 0),
                                    $volume_altura  => $this->convertUnitMeasurement($UnitMeasurement, $vHeight, 0),
                                    $volume_largura => $this->convertUnitMeasurement($UnitMeasurement, $vWidth, 0),

                                    $warranty_time => $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0),
                                    $nbm => $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0),
                                    $nbm_origin => $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0),
                                    $ean => $this->procAttrConfig($ean, $sku->ean, 0),
                                    $warranty_text => $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0),
                                    'msrp_enabled' =>  '2',
                                    'categoria_anymarket' => $ProdsJSON->category,
                                    $variationArray[ $idVar ] => $varAttr,
                            );

                            foreach ($ProdsJSON->attributes as  $attrProd) {
                                $dataPrd[ strtolower($attrProd->name) ] = $this->procAttrConfig(strtolower($attrProd->name), $attrProd->value, 0);  
                            }

                            $dataPrdSimple = array(
                                'product' => $dataPrd,
                                'stock_item' => array(
                                    'is_in_stock' =>  $sku->stockAmount > 0 ? '1' : '0',
                                    'qty' => $sku->stockAmount,
                                ),
                                'images' => $imagesGallery,
                            );

                            $ProdReturn = $this->create_simple_product($storeID, $dataPrdSimple);
                            $ProdCrt = $ProdReturn->getEntityId();

                            $product = Mage::getModel('catalog/product')->load($ProdCrt);
                        }else{
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc( 'Opção de variação sem correspondente no magento ('.$variationArray[ $idVar ].') - '.$descVar );
                            $anymarketlog->setStatus("0");
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->save();
                        }
                    }else{
                        //Atualiza Imagens
                        $this->update_image_product($product, $ProdsJSON, $IDSKUProd);

                        $webSiteIds = $product->getWebsiteIds();
                        if(!in_array($websiteID, $webSiteIds)){
                            array_push($webSiteIds, $websiteID);
                            $product->setWebsiteIds( $webSiteIds );
                        }

                        $product->setStoreId($storeID);
                        $product->setName( $sku->title );
                        $product->setDescription( $sku->title );
                        $product->setShortDescription( $sku->title );
                        $product->setData('weight', $MassUnit == 1 ? $ProdsJSON->weight*1000 : $ProdsJSON->weight);

                        $product->setData($priceField, $sku->price);
                        $product->setData('brand_anymarket', $ProdsJSON->brand);
                        $product->setData($model, $this->procAttrConfig($model, $ProdsJSON->model, 0));
                        $product->setData($video_url, $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0));

                        $product->setData($volume_comprimento, $this->convertUnitMeasurement($UnitMeasurement, $vLength, 0));
                        $product->setData($volume_altura, $this->convertUnitMeasurement($UnitMeasurement, $vHeight, 0));
                        $product->setData($volume_largura,$this->convertUnitMeasurement($UnitMeasurement, $vWidth, 0));

                        $product->setData($warranty_time, $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0));
                        $product->setData($nbm, $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0));
                        $product->setData($nbm_origin, $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0));
                        $product->setData($ean, $this->procAttrConfig($ean, $sku->ean, 0));
                        $product->setData($warranty_text, $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0));
                        $product->setData('id_anymarket', $sku->idProduct);
                        $product->setData('categoria_anymarket', $ProdsJSON->category);
                        $product->setData('name', $ProdsJSON->title);
                        $product->setStatus(1);
                        $product->save();

                        foreach ($ProdsJSON->attributes as  $attrProd) {
                            $product->setData( strtolower($attrProd->name), $this->procAttrConfig(strtolower($attrProd->name), $attrProd->value, 0));
                        }

                        if( $typeSincOrder == 0 ) {
                            $qtyStock = $sku->stockAmount;
                            $inStock = $qtyStock > 0 ? '1' : '0';

                            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                            $stockItem->setData('is_in_stock', $inStock);
                            $stockItem->setData('qty', $qtyStock);
                            $stockItem->save();
                        }

                        $ProdCrt = $product->getId();
                    }

                    $returnProd['return'] = Mage::helper('db1_anymarket')->__('Simple product Created').' ('.$ProdCrt.')';
                    $returnProd['error'] = '0';
                    $returnProd['json'] = '';

                    $this->saveLogsProds($storeID, "1", $returnProd, $product);

                    if($ProdCrt != ''){
                        $prodSimpleFromConfig[] = array('AttributeText' => $variationArray[ $idVar ], 'Id' => $ProdCrt);
                        $ProdCrt = '';
                    }
                }

                $collectionConfigurable = Mage::getResourceModel('catalog/product_collection')
                                                ->addAttributeToFilter('type_id', array('eq' => 'configurable'));

                $prod = null;
                foreach ($collectionConfigurable as $prodConfig) {
                    $prod = Mage::getModel('catalog/product')->setStoreId(1)->load( $prodConfig->getId() );
                    if( $prod->getData('id_anymarket') == $ProdsJSON->id ){
                        break;
                    }

                }

                $imagesGallery = array();
                foreach ($ProdsJSON->photos as $image) {
                    $imagesGallery[] = array('img' => $image->standard_resolution, 'main' => $image->main);
                }

                if( $prod == null ){
                    if($prodSimpleFromConfig){
                        $dataProdConfig = array(
                            'stock' => '0',
                            'price' => '0',
                            'name' => $ProdsJSON->title,
                            'brand' => '',
                            'sku' => $ProdsJSON->id,
                            'id_anymarket' => $ProdsJSON->id,
                            'categoria_anymarket' => $ProdsJSON->category,
                            'images' => $imagesGallery
                        );

                        foreach ($configureFieldsConfig as $fieldConfig) {
                            $dataProdConfig[$fieldConfig] = $ProdsJSON->description;
                        }

                        $ProdCrt = $this->create_configurable_product($storeID, $dataProdConfig, $prodSimpleFromConfig, $AttributeIds);
                    }
                }else{
                    $dataProdConfig = array(
                        'stock' => '0',
                        'price' => '0',
                        'name' => $ProdsJSON->title,
                        'brand' => '',
                        'sku' => $ProdsJSON->id,
                        'id_anymarket' => $ProdsJSON->id,
                        'categoria_anymarket' => $ProdsJSON->category
                    );

                    foreach ($configureFieldsConfig as $fieldConfig) {
                        $dataProdConfig[$fieldConfig] = $ProdsJSON->description;
                    }

                    $this->update_configurable_product($storeID, $prod->getId(), $dataProdConfig, $prodSimpleFromConfig, $AttributeIds);
                }
            }else{
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( 'Variação sem correspondente no magento ('.utf8_decode($sinc).') - Produto: '.$ProdsJSON->id);
                $anymarketlog->setStatus("0");
                $anymarketlog->setStores(array($storeID));
                $anymarketlog->save();
            }
        // PROD SIMPLES
        }else{
            foreach ($ProdsJSON->skus as $ProdJSON) {
                $skuProd = $ProdJSON;
            }

            $imagesGallery = array();
            foreach ($ProdsJSON->photos as $image) {
                $imagesGallery[] = array('img' => $image->standard_resolution, 'main' => $image->main);
            }

            $IDSkuJsonProd = $skuProd->idInClient != null ? $skuProd->idInClient : $skuProd->idProduct;
            $product = Mage::getModel('catalog/product')->setStoreId($storeID)->loadByAttribute('sku', $IDSkuJsonProd);
            foreach ($ProdsJSON->skus as  $sku) {
                $skuEan = $sku->ean;
            }

            //trata as dimensoes
            $vHeight = $this->procAttrConfig($volume_altura, $ProdsJSON->height, 0);
            $vWidth  = $this->procAttrConfig($volume_largura, $ProdsJSON->width, 0);
            $vLength = $this->procAttrConfig($volume_comprimento, $ProdsJSON->length, 0);

            if(!$product){
                $dataPrd = array(
                    'attribute_set_id' => $AttrSet == null ? Mage::getModel('catalog/product')->getDefaultAttributeSetId() : $AttrSet,
                    'type_id' =>  'simple',
                    'sku' => $IDSkuJsonProd,
                    'name' => $skuProd->title,
                     $priceField => $skuProd->price,
                    'created_at' => strtotime('now'),
                    'updated_at' => strtotime('now'),
                    'id_anymarket' => $ProdsJSON->id,
                    'weight' => $MassUnit == 1 ? $ProdsJSON->weight*1000 : $ProdsJSON->weight,
                    'store_id' => $storeID,
                    'website_ids' => array($websiteID),
                    'brand_anymarket' => $ProdsJSON->brand,
                    $nbm => $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0),
                    $model => $this->procAttrConfig($model, $ProdsJSON->model, 0),
                    $video_url => $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0),

                    $volume_comprimento => $this->convertUnitMeasurement($UnitMeasurement, $vLength, 0),
                    $volume_altura  => $this->convertUnitMeasurement($UnitMeasurement, $vHeight, 0),
                    $volume_largura => $this->convertUnitMeasurement($UnitMeasurement, $vWidth, 0),

                    $warranty_time => $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0),
                    $nbm_origin => $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0),
                    $ean => $this->procAttrConfig($ean, $skuEan, 0),
                    $warranty_text => $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0),
                    'msrp_enabled' =>  '2',
                    'categoria_anymarket' => $ProdsJSON->category,
                );

                foreach ($configureFieldsConfig as $fieldConfig) {
                    $dataPrd[$fieldConfig] = $ProdsJSON->description;
                }

                foreach ($ProdsJSON->attributes as  $attrProd) {
                    $dataPrd[ strtolower($attrProd->name) ] = $this->procAttrConfig(strtolower($attrProd->name), $attrProd->value, 0);
                }

                $data = array(
                    'product' => $dataPrd,
                    'stock_item' => array(
                        'is_in_stock' =>  $skuProd->stockAmount > 0 ? '1' : '0',
                        'qty' => $skuProd->stockAmount,
                    ),
                    'images' => $imagesGallery,

                );

                $ProdCrt = $this->create_simple_product($storeID, $data);
            }else{
                //Atualiza Imagens
                $this->update_image_product($product, $ProdsJSON, $IDSkuJsonProd);

                $webSiteIds = $product->getWebsiteIds();
                if(!in_array($websiteID, $webSiteIds)){
                    array_push($webSiteIds, $websiteID);
                    $product->setWebsiteIds( $webSiteIds );
                }

                $product->setStoreId($storeID);
                $product->setName( $skuProd->title );

//                $product->setDescription( $ProdsJSON->description );
//                $product->setShortDescription( $ProdsJSON->description );

                foreach ($configureFieldsConfig as $fieldConfig) {
                    $product->setData($fieldConfig, $ProdsJSON->description);
                }

                $product->setData('weight', $MassUnit == 1 ? $ProdsJSON->weight*1000 : $ProdsJSON->weight);

                $product->setData('brand_anymarket', $ProdsJSON->brand);
                $product->setData($model, $this->procAttrConfig($model, $ProdsJSON->model, 0));
                $product->setData($video_url, $this->procAttrConfig($video_url, $ProdsJSON->videoURL, 0));

                $product->setData($volume_comprimento, $this->convertUnitMeasurement($UnitMeasurement, $vLength, 0));
                $product->setData($volume_altura,  $this->convertUnitMeasurement($UnitMeasurement, $vHeight, 0));
                $product->setData($volume_largura, $this->convertUnitMeasurement($UnitMeasurement, $vWidth, 0));

                $product->setData($warranty_time, $this->procAttrConfig($warranty_time, $ProdsJSON->warrantyTime, 0));
                $product->setData($nbm, $this->procAttrConfig($nbm, $ProdsJSON->nbm, 0));
                $product->setData($nbm_origin, $this->procAttrConfig($nbm_origin, $ProdsJSON->originCode, 0));
                $product->setData($ean, $this->procAttrConfig($ean, $skuEan, 0));
                $product->setData($warranty_text, $this->procAttrConfig($warranty_text, $ProdsJSON->warranty, 0));
                $product->setData('id_anymarket', $ProdsJSON->id);
                $product->setData('categoria_anymarket', $ProdsJSON->category);
                $product->setData('name', $ProdsJSON->title);
                $product->setStatus(1);

                foreach ($ProdsJSON->attributes as  $attrProd) {
                    $product->setData( strtolower($attrProd->name), $this->procAttrConfig(strtolower($attrProd->name), $attrProd->value, 0));
                }

                $product->setData($priceField, $skuProd->price);

                if( $typeSincOrder == 0 ) {
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                    $stockItem->setData('is_in_stock', $ProdsJSON->skus[0]->stockAmount > 0 ? '1' : '0');
                    $stockItem->setData('qty', $ProdsJSON->skus[0]->stockAmount);
                    $stockItem->save();
                }

                $product->save();

                $ProdCrt = $product;
            }

        }
        Mage::getSingleton('core/session')->setImportProdsVariable('true');
        return $ProdCrt;
    }

    public function massUpdtProds($storeID){
        try {
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
            if($typeSincProd == 0){
                $products = $products = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('name');
                $cont = 0;
                foreach($products as $product) {
                    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );

                    if (!$parentIds) {
                        $anymarketproductsUpdt =  Mage::getModel('db1_anymarket/anymarketproducts')->setStoreId($storeID)->load($product->getId(), 'nmp_id');
                        if(is_array($anymarketproductsUpdt->getData('store_id'))){
                            $arrvar = array_values($anymarketproductsUpdt->getData('store_id'));
                            $StoreIDAmProd = array_shift($arrvar);
                        }else{
                            $StoreIDAmProd = $anymarketproductsUpdt->getData('store_id');
                        }
                        if( ($anymarketproductsUpdt->getData('nmp_id') == null) || ($StoreIDAmProd != $storeID) ){

                            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                            if (!isset($parentIds[0])) {
                                $name = $product->getName();
                                $sku  = $product->getSku();
                                $IDProd  = $product->getId();

                                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->load($sku ,'nmp_sku');
                                $anymarketproducts->setNmpId( $IDProd );
                                $anymarketproducts->setNmpSku( $sku );
                                $anymarketproducts->setNmpName( $name );
                                $anymarketproducts->setNmpDescError("");
                                $anymarketproducts->setNmpStatusInt("Não integrado (Magento)");
                                $anymarketproducts->setStatus($product->getData('integra_anymarket'));
                                $anymarketproducts->setStores(array($storeID));
                                $anymarketproducts->save();

                                $cont = $cont+1;
                            }
                        }else{
                            if( ($anymarketproductsUpdt->getData('nmp_sku') != $product->getSku() ) || ($anymarketproductsUpdt->getData('nmp_name') != $product->getName() ) ){
                                $anymarketproductsUpdt->setNmpSku( $product->getSku() );
                                $anymarketproductsUpdt->setNmpName( $product->getName() );
                                $anymarketproductsUpdt->save();

                                $cont = $cont+1;
                            }
                        }
                    }

                }

                if($cont > 0){
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('db1_anymarket')->__('Total %d products successfully listed.', $cont)
                    );
                }
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('There was an error updating the products.')
            );
            Mage::logException($e);
        }
    }

    /**
     * @param $IDProd
     * @param $QtdStock
     * @param $Price
     */
    public function updatePriceStockAnyMarket($storeID, $IDProd, $QtdStock, $Price){
        $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load( $IDProd );
        if($product->getTypeID() != "configurable"){
            if( ($product->getStatus() == 1) && ($product->getData('integra_anymarket') == 1) ){
                $anymarketproductsUpdt =  Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');
                if( ($anymarketproductsUpdt->getData('nmp_status_int') != 'Não integrado (Magento)') ){
                    $sincronize = true;
                    if($product->getVisibility() == 1){ //nao exibido individualmente
                        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild( $product->getId() );
                        if ($parentIds) {
                            $sincronize = true;
                        }else{
                            $sincronize = false;
                        }
                    }

                    if ($sincronize == true) {
                        if($product->getData('id_anymarket') != ""){
                            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
                            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

                            $headers = array(
                                "Content-type: application/json",
                                "Accept: */*",
                                "gumgaToken: ".$TOKEN
                            );

                            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
                            if( $typeSincProd == 0 ){
                                $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
                                if($filter == 'final_price'){
                                    $Price = $product->getFinalPrice();
                                }else{
                                    $Price = $product->getData($filter);
                                }
                            }else{
                                $Price = null;
                            }

                            $typeSincOrder = Mage::getStoreConfig('anymarket_section/anymarket_integration_order_group/anymarket_type_order_sync_field', $storeID);
                            if( $typeSincOrder == 0 ){
                                $QtdStock = null;
                            }elseif( !is_numeric ( $QtdStock ) ){
                                $QtdStock = null;
                            }

                            if( ($QtdStock != null) || ($Price != null) ){
                                $params = array(
                                    "partnerId" => $product->getSku(),
                                    "quantity" => $QtdStock,
                                    "cost" => $Price
                                );

                                $returnProd = $this->CallAPICurl("PUT", $HOST."/v2/stocks", $headers, array($params));
                                if($returnProd['return'] == ''){
                                    $returnProd['return'] = Mage::helper('db1_anymarket')->__('Update Stock and Price');
                                    $returnProd['error'] = '0';
                                    $returnProd['json'] = json_encode($params);
                                }

                                if( $returnProd['error'] == '1' ){
                                    $this->saveLogsProds($storeID, "0", $returnProd, $product);
                                }else{
                                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                                    $anymarketlog->setLogDesc(  Mage::helper('db1_anymarket')->__('Update stock and price.') );
                                    $anymarketlog->setStatus("0");
                                    $anymarketlog->setLogId( $product->getId() );
                                    $anymarketlog->setLogJson( json_encode($params) );
                                    $anymarketlog->setStores(array($storeID));
                                    $anymarketlog->save();
                                }
                            }

                        }
                    }
                }
            }
        }else{
            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

            $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', $storeID));
            foreach($childProducts as $child) {
                $this->updatePriceStockAnyMarket($storeID, $child->getId(), $child->getStockItem()->getQty(), $child->getData($filter));
            }
        }


    }

}