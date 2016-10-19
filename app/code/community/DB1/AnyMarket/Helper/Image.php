<?php

class DB1_AnyMarket_Helper_Image extends DB1_AnyMarket_Helper_Data
{

    /**
     * //obtem as imagens do produto(Config ou Simples)
     * @param $storeID
     * @param $product
     *
     * @return array
     */
    public function getImagesOfProduct($storeID, $product, $ArrVariationValues){
        if($product) {
            $transformToHttp = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_transform_http_image_field', $storeID);
            $itemsIMG = array();
            $galleryData = $product->getMediaGalleryImages();
            $exportImage = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_export_image_field', $storeID);
            foreach ($galleryData as $g_image) {
                $infoImg = getimagesize($g_image['url']);
                $imgSize = filesize($g_image['path']);
                $processImage = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_transform_process_image_field', $storeID);
                if ($processImage == 0) {
                    if (($infoImg[0] != "") && ((float)$infoImg[0] < 350 || (float)$infoImg[1] < 350 || $imgSize > 4100000)) {
                        if ($exportImage == 0) {
                            array_push($arrProd, 'Image_a (' . $g_image['url'] . ' - Sku: ' . $product->getSku() . ' - Width: ' . $infoImg[0] . ' - Height: ' . $infoImg[1] . ' - Size: ' . $imgSize . ')');
                        } else {
                            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                            $anymarketlog->setLogDesc('Error on export image - ' . $g_image['url']);
                            $anymarketlog->setLogId($product->getSku());
                            $anymarketlog->setStatus("1");
                            $anymarketlog->setStores(array($storeID));
                            $anymarketlog->save();
                        }
                    } else {
                        $urlImageImport = $g_image['url'];
                        $defaultImage = $product->getImage();
                        $isMain =  strpos($urlImageImport, $defaultImage) === false ? false : true;
                        if ($ArrVariationValues) {
                            foreach ($ArrVariationValues as $value) {
                                if ($transformToHttp != 0) {
                                    $urlImageImport = str_replace("https", "http", $urlImageImport);
                                }
                                $itemsIMG[] = array(
                                    "main" => $isMain,
                                    "url" => $urlImageImport,
                                    "variation" => $value,
                                );
                            }
                        } else {
                            if ($transformToHttp != 0) {
                                $urlImageImport = str_replace("https", "http", $urlImageImport);
                            }

                            $itemsIMG[] = array(
                                "main" => $isMain,
                                "url" => $urlImageImport
                            );
                        }

                    }
                } else {
                    if (count($product->getMediaGalleryImages()) > 0) {
                        $with = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_width_image_field', $storeID);
                        $height = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_height_image_field', $storeID);
                        foreach ($product->getMediaGalleryImages() as $_image) {
                            if (((int)$with > 0) && ((int)$height > 0)) {
                                $thumbnail12 = Mage::helper('catalog/image')->init($product, 'image', $_image->getFile())->resize($with, $height);
                            } else {
                                $thumbnail12 = Mage::helper('catalog/image')->init($product, 'image', $_image->getFile());
                            }
                            $thumbnail13 = str_replace('/webApps/migration/productapi/new/', '/', $thumbnail12);
                            $urlImageImport = $thumbnail13;
                            if ($ArrVariationValues) {
                                foreach ($ArrVariationValues as $value) {
                                    if ($transformToHttp != 0) {
                                        $urlImageImport = str_replace("https", "http", $urlImageImport);
                                    }
                                    $itemsIMG[] = array(
                                        "main" => false,
                                        "url" => $urlImageImport,
                                        "variation" => $value,
                                    );
                                }
                            } else {
                                if ($transformToHttp != 0) {
                                    $urlImageImport = str_replace("https", "http", $urlImageImport);
                                }

                                $itemsIMG[] = array(
                                    "main" => true,
                                    "url" => $urlImageImport
                                );
                            }

                        }
                    }
                }
            }

            return $itemsIMG;
        }else{
            return null;
        }
    }

    public function processImageToAdd($storeID, $imgProdMagentoURL){

        return $imgProdMagentoURL;
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
            $product = Mage::getModel('catalog/product')->setStoreId($storeID)->load( $product->getId() );
            $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
            $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);
            $exportImage = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_export_image_field', $storeID);
            $transformToHttp = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_transform_http_image_field', $storeID);

            $headers = array(
                "Content-type: application/json",
                "gumgaToken: ".$TOKEN
            );

            if($product->getData('id_anymarket') != ''){
                $imgGetRet = $this->CallAPICurl("GET", $HOST."/v2/products/".$product->getData('id_anymarket')."/images", $headers, null);
                if($imgGetRet['error'] == '0'){
                    $imgsProdAnymarket = $imgGetRet['return'];

                    $arrAdd = array();
                    $arrImgs = array();

                    $processImage = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_transform_process_image_field', $storeID);
                    $imgsProdMagento = $product->getMediaGalleryImages();

                    if (count($imgsProdMagento) <= 0) {
                        $productIMG = Mage::getModel('catalog/product')->load( $product->getId() );
                        $imgsProdMagento = $productIMG->getMediaGalleryImages();
                    }

                    if (count($imgsProdMagento) > 0) {
                        if( $processImage == 0 ) {
                            foreach ($imgsProdMagento as $imgProdMagento) {
                                $urlImage = $imgProdMagento->getData('url');
                                $infoImg = getimagesize($urlImage);
                                $imgSize = filesize($imgProdMagento->getData('path'));
                                if (($infoImg[0] != "") && ((float)$infoImg[0] < 350 || (float)$infoImg[1] < 350 || $imgSize > 4100000)) {
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
                                } else {
                                    $imgProdMagentoURL = $imgProdMagento->getData('url');
                                    if ($transformToHttp != 0) {
                                        $imgProdMagentoURL = str_replace("https", "http", $imgProdMagentoURL);
                                    }
                                    array_push($arrAdd, $imgProdMagentoURL);
                                }
                            }
                        }else {
                            $with = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_width_image_field', $storeID);
                            $height = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_height_image_field', $storeID);
                            foreach ($imgsProdMagento as $imgProdMagento) {
                                if( ((int)$with > 0) && ((int)$height > 0) ){
                                    $thumbnail12 = Mage::helper('catalog/image')->init($product, 'image', $imgProdMagento->getFile())->resize($with, $height);
                                }else{
                                    $thumbnail12 = Mage::helper('catalog/image')->init($product, 'image', $imgProdMagento->getFile());
                                }

                                $imgProdMagentoURL = str_replace('/webApps/migration/productapi/new/', '/', $thumbnail12);
                                if ($transformToHttp != 0) {
                                    $imgProdMagentoURL = str_replace("https", "http", $imgProdMagentoURL);
                                }
                                array_push($arrAdd, $imgProdMagentoURL);
                            }
                        }
                    }

                    foreach ($imgsProdAnymarket as $imgProdAnymarket) {
                        $exportImages = true;
                        if ($variation) {
                            if( !isset($imgProdAnymarket->variation) || $imgProdAnymarket->variation != $variation ){
                                $exportImages = false;
                            }
                        }

                        if( $exportImages ) {
                            $imgRemove = $imgProdAnymarket->id;
                            $imgDelRet = $this->CallAPICurl("DELETE", $HOST . "/v2/products/" . $product->getData('id_anymarket') . "/images/" . $imgRemove, $headers, null);
                            if ($imgDelRet['error'] == '1') {
                                $anymarketlogDel = Mage::getModel('db1_anymarket/anymarketlog');

                                if (is_string($imgDelRet['return'])) {
                                    $anymarketlogDel->setLogDesc('Error on delete image in Anymarket (' . $imgRemove . ') - ' . $imgDelRet['return']);
                                } else {
                                    $anymarketlogDel->setLogDesc('Error on delete image in Anymarket (' . $imgRemove . ') - ' . json_encode($imgDelRet['return']));
                                }

                                $anymarketlogDel->setLogJson('');
                                $anymarketlogDel->setLogId($product->getSku());
                                $anymarketlogDel->setStatus("1");
                                $anymarketlogDel->setStores(array($storeID));
                                $anymarketlogDel->save();
                            } else {
                                $anymarketlogDel = Mage::getModel('db1_anymarket/anymarketlog');
                                $anymarketlogDel->setLogDesc('Deleted image from Anymarket ');
                                $anymarketlogDel->setLogJson('');
                                $anymarketlogDel->setLogId($product->getSku());
                                $anymarketlogDel->setStatus("1");
                                $anymarketlogDel->setStores(array($storeID));
                                $anymarketlogDel->save();
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
                        $defaultImage = $product->getImage();
                        foreach ($arrAdd as $imgAdd) {
                            $isMain =  strpos($imgAdd, $defaultImage) === false ? false : true;
                            if ($variation) {
                                $JSONAdd = array(
                                    "url" => $imgAdd,
                                    "variation" => $variation,
                                    "main" => $isMain
                                );
                            } else {
                                $JSONAdd = array(
                                    "url" => $imgAdd,
                                    "main" => $isMain
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

                }else{
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setLogDesc( 'Error on get images from Anymarket ('.$product->getData('id_anymarket').') ');
                    $anymarketlog->setStatus("1");
                    $anymarketlog->setStores(array($storeID));
                    $anymarketlog->save();
                }
            }
        }
    }

}