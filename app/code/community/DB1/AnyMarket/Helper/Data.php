<?php
/**
 * AnyMarket default helper
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * convert array to options
     *
     * @access public
     * @param $options
     * @return array
     * 
     */
    public function convertOptions($options)
    {
        $converted = array();
        foreach ($options as $option) {
            if (isset($option['value']) && !is_array($option['value']) &&
                isset($option['label']) && !is_array($option['label'])) {
                $converted[$option['value']] = $option['label'];
            }
        }
        return $converted;
    }

    /**
     * get all store data
     *
     * @access public
     * @return array
     * 
     */
    public function getAllStores($websiteID = null)
    {
        $arrStores = array();
        if(!$websiteID){
            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        array_push($arrStores, $store->getData());
                    }
                }
            }
        }else{
            $website = Mage::getModel('core/website')->load($websiteID);

            foreach ($website->getStoreIds() as $storeid) {
                $storeDat = Mage::getModel('core/store')->load($storeid);
                array_push($arrStores, $storeDat->getData());
            }
        }
        return $arrStores;
    }

    /**
     * call curl
     *
     * @access public
     * @param $options
     * @return json
     * 
     */
    public function CallAPICurl($method, $url, $headers, $params){
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data_string = "";
        if ($method == "POST"){
            $data_string = json_encode($params);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        }else if($method == "PUT"){
            $data_string = json_encode($params);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        }

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 400);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $curl_response = curl_exec($curl);
        $err = curl_error($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ( $status == 200 ) {
            $retorno = array("error" => "0", "json" => $data_string, "return" => json_decode($curl_response) );
        }else{
            if($err){
                $retorno = array("error" => "1", "json" => $data_string,"return" => 'Error Curl: '.$err );
            }else{
                $retCurlResp = utf8_encode($curl_response);
                $retorno = array("error" => "1", "json" => $data_string, "return" => 'Error Execute: '.$retCurlResp );
            }

        }
        if($retorno == ""){
            $retorno = $data_string;
        }

        curl_close($curl);

        return $retorno;
    }

    /**
     * add message inbox of magento
     *
     * @access public
     * @param $title, $Desc, $URL
     * @return void
     * 
     */
    public function addMessageInBox($title, $Desc, $URL){
        $AdminNotice = Mage::getModel('adminnotification/inbox');
        $AdminNotice->setSeverity('2');
        $AdminNotice->setTitle( $title );
        $AdminNotice->setDescription( $Desc );
        $AdminNotice->setUrl( $URL );
        $AdminNotice->setDateAdded( date('Y-m-d H:i:s') );
        $AdminNotice->save();
    }

    /**
     * add message inbox of magento
     *
     * @access public
     * @return void
     * 
     */
    public function massInsertAttribute(){
        $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
        $storeID = Mage::app()->getStore()->getId();

        foreach ($productAttrs as $productAttr) {
            if($productAttr->getFrontendLabel() != null){
                $attrCheck =  Mage::getModel('db1_anymarket/anymarketattributes')->load($productAttr->getAttributeId(), 'nma_id_attr');

                if($attrCheck->getData('nma_id_attr') == null){
                    $anymarketattribute = Mage::getModel('db1_anymarket/anymarketattributes');
                    $anymarketattribute->setNmaIdAttr( $productAttr->getAttributeId() );
                    $anymarketattribute->setNmaDesc( $productAttr->getFrontendLabel() );
                    $anymarketattribute->setStatus( "0" );
                    $anymarketattribute->setStores(array($storeID));
                    $anymarketattribute->save();
                }
            }
        }
    }


}