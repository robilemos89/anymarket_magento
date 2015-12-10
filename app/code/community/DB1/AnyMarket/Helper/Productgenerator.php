<?php

class DB1_AnyMarket_Helper_ProductGenerator extends DB1_AnyMarket_Helper_Data
{
    protected $_defaultData = array(
        'product' => array(
            'attribute_set_id' =>  '4',
            'type_id' =>  'simple',
            'sku' =>  '0124ASF3',
            'has_options' =>  '0', 
            'required_options' =>  '0',
            'created_at' =>  '',
            'updated_at' =>  '',
            'status' =>  '1',
            'visibility' =>  '4',
            'volume_comprimento' => null,
            'volume_altura' => null,
            'volume_largura' => null,
            'warranty_time' => null,
            'id_anymarket' => null,
            'tax_class_id' =>  '0', 
            'is_recurring' =>  '0', 
            'weight' =>  '1.0000',
            'price' =>  '10.0000',
            'cost' =>  '10.0000',
            'special_price' => null,
            'msrp' => null,
            'name' =>  'teste' ,
            'url_key' =>  '',
            'coun1_of_manufacture' => null,
            'video_url' => null,
            'nbm' =>  '1',
            'nbm_origin' =>  '1',
            'ean' =>  '1',
            'warranty_text' => null,
            'msrp_enabled' =>  '2',
            'msrp_display_actual_price_type' =>  '4',
            'meta_title' => null,
            'meta_description' => null,
            'image' =>  'no_selection',
            'small_image' =>  'no_selection',
            'thumbnail' =>  'no_selection',
            'custom_design' => null,
            'page_layout' => null,
            'options_container' =>  'container1',
            'gift_message_available' => null,
            'url_path' =>  'teste.html',
            'news_from_date' => null,
            'news_to_date' => null,
            'special_from_date' => null,
            'special_to_date' => null,
            'custom_design_from' => null,
            'custom_design_to' => null,
            'description' =>  'teste',
            'short_description' =>  'teste',
            'meta_keyword' => null,
            'custom_layout_update' => null,
            'is_salable' =>  '1',
            'integra_anymarket' => '1',
            'categoria_anymarket' => '',
        ),
        'images' => array(),
        'stock_item' => array(
            'use_config_manage_stock' => '0',
            'manage_stock' => '1',
            'min_sale_qty' => '1',
            'max_sale_qty' => '',
            'is_in_stock' => '1',
            'qty' => '9999',
        ),
        'category' => array(2,3,4),
    );

    /**
     * update images in MG
     *
     * @access public
     * @param $product, $data
     * @return void
     * 
     */
    public function updateImages($product, $data){ 
        $sku = $data['sku'];
        foreach ($data['images'] as $image) {
            $this->importImages($product, $image, $sku);
        }
    }

    /**
     * create simple prod in MG
     *
     * @access public
     * @param $data
     * @return product object
     * 
     */
    public function createSimpleProduct($data = array()){

        $data = array_replace_recursive($this->_defaultData, $data);

        $product = Mage::getModel('catalog/product');
        $product->setData($data['product']);
        $product->setStockData($data['stock_item']);
        $product->setForceConfirmed(true);
        $prodSaved = $product->save(); 

        $sku = $data['product']['sku'];

        if( array_key_exists("images", $data) ){
            foreach ($data['images'] as $image) {
                $this->importImages($prodSaved, $image, $sku);
            }
        }

        return $prodSaved;
    }

    /**
     * import images
     *
     * @access public
     * @param $product, $image, $sku
     * @return void
     * 
     */
    private function importImages($product, $image, $sku){

        $image_url = $image['img'];
        $image_url  = str_replace("https://", "http://", $image_url);
        $image_type = substr(strrchr($image_url,"."),1);
        $split =  explode("?", $image_type);
        $image_type = $split[0];
        $imgName = basename($image_url);
        $imgName = str_replace('.'.$image_type, "", $imgName);
        $filename  = md5($imgName . $sku).'.'.$image_type;

        $dirPath = Mage::getBaseDir('media') . DS . 'import'; 
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $filepath   = $dirPath . DS . $filename;

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL,$image_url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Cirkel');
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);

        file_put_contents($filepath, $query);

        if (file_exists($filepath)) {
            $attrIMG = array();

            if( array_key_exists('main', $image) ){
                if($image['main'] == true){
                    $attrIMG = array('image', 'thumbnail', 'small_image');
                }
            }

            $productMG = Mage::getModel('catalog/product')->loadByAttribute('sku', $product->getSku());
            $productMG->addImageToMediaGallery( $filepath, $attrIMG, false, false);
            $productMG->save();
        }

    }

    /**
     * create configurable product in MG
     *
     * @access public
     * @param $dataProdConfig, $simpleProducts, $AttributeIds
     * @return product object
     * 
     */
    public function createConfigurableProduct($dataProdConfig = array() ,$simpleProducts = array(), $AttributeIds = array()){
        $storeID = Mage::app()->getStore()->getId();
        $confProduct = Mage::getModel('catalog/product')->setSku($dataProdConfig['sku']);
        $confProduct->setTypeId('configurable');

        $confProduct->getTypeInstance()->setUsedProductAttributeIds($AttributeIds);

        $configurableProductsData = array();
        $configurableAttributesData = $confProduct->getTypeInstance()->getConfigurableAttributesAsArray();

        foreach ($simpleProducts as $simpleProduct) {
            $sProd = Mage::getModel('catalog/product')->load( $simpleProduct['Id'] );

            $AttributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $simpleProduct['AttributeText'] );

            $simpleProductsData = array(
                'label'         => $sProd->getAttributeText( $simpleProduct['AttributeText'] ),
                'attribute_id'  => $AttributeId,
                'value_index'   => (int) $sProd->getData( $simpleProduct['AttributeText'] ),
                'is_percent'    => 0,
                'pricing_value' => $sProd->getPrice(),
            );

            $configurableProductsData[ $sProd->getId() ] = $simpleProductsData;
            $configurableAttributesData[0]['values'][] = $simpleProductsData;
        }

        $confProduct->setConfigurableProductsData($configurableProductsData);
        $confProduct->setConfigurableAttributesData($configurableAttributesData);

        $confProduct->setCanSaveConfigurableAttributes(true);
        $confProduct->setStoreId($storeID)
                     ->setAttributeSetId(4)
                     ->setStockData($dataProdConfig['stock'])
                     ->setPrice($dataProdConfig['price'])
                     ->setName($dataProdConfig['name'])
                     ->setShortDescription($dataProdConfig['short'])
                     ->setDescription($dataProdConfig['description'])
                     ->setCategoryIds(array(3))
                     ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                     ->setBrand($dataProdConfig['brand'])
                     ->setStatus(1)
                     ->setTaxClassId(0)
                     ->setIdAnymarket( $dataProdConfig['id_anymarket'] )
                     ->setCategoriaAnymarket( $dataProdConfig['categoria_anymarket'] )                     
                     ->save();

        $sku = $dataProdConfig['sku'];
        foreach ($dataProdConfig['images'] as $image) {
            $this->importImages($confProduct, $image, $sku);
        }

        return $confProduct;
    }

    /**
     * update configurable product in MG
     *
     * @access public
     * @param $idProd, $dataProdConfig, $simpleProducts
     * @return product object
     * 
     */
    public function updateConfigurableProduct($idProd, $dataProdConfig = array() ,$simpleProducts = array(), $AttributeIds = array()){
        $storeID = Mage::app()->getStore()->getId();
        $confProduct = Mage::getModel('catalog/product')->setStoreId($storeID)->load( $idProd );
        $confProduct->setTypeId('configurable');

        $confProduct->getTypeInstance()->setUsedProductAttributeIds($AttributeIds);

        $configurableProductsData = array();
        $configurableAttributesData = $confProduct->getTypeInstance()->getConfigurableAttributesAsArray();

        foreach ($simpleProducts as $simpleProduct) {
            $sProd = Mage::getModel('catalog/product')->load( $simpleProduct['Id'] );

            $AttributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $simpleProduct['AttributeText'] );

            $simpleProductsData = array(
                'label'         => $sProd->getAttributeText( $simpleProduct['AttributeText'] ),
                'attribute_id'  => $AttributeId,
                'value_index'   => (int) $sProd->getData( $simpleProduct['AttributeText'] ),
                'is_percent'    => 0,
                'pricing_value' => $sProd->getPrice(),
            );

            $configurableProductsData[ $sProd->getId() ] = $simpleProductsData;
            $configurableAttributesData[0]['values'][] = $simpleProductsData;
        }

        $confProduct->setConfigurableProductsData($configurableProductsData);
//        $confProduct->setConfigurableAttributesData($configurableAttributesData);
        $confProduct->setCanSaveConfigurableAttributes(true);
        $confProduct->setStoreId($storeID)
                     ->setAttributeSetId(4)
                     ->setStockData($dataProdConfig['stock'])
                     ->setPrice($dataProdConfig['price'])
                     ->setName($dataProdConfig['name'])
                     ->setShortDescription($dataProdConfig['short'])
                     ->setDescription($dataProdConfig['description'])
                     ->setCategoryIds(array(3))
                     ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                     ->setBrand($dataProdConfig['brand'])
                     ->setStatus(1)
                     ->setTaxClassId(0)
                     ->setIdAnymarket( $dataProdConfig['id_anymarket'] )
                     ->save();

        return $confProduct;
    }

}