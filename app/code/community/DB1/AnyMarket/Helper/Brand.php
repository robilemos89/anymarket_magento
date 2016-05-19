<?php

class DB1_AnyMarket_Helper_Brand extends DB1_AnyMarket_Helper_Data
{
    /**
     * get all brands from magento
     *
     * @access public
     */
    public function getBrands($storeID){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', $storeID);
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', $storeID);

        $headers = array( 
            "Content-type: application/json",
            "gumgaToken: ".$TOKEN
        );

        $retCountBrand = 0;
        $startRec = 0;
        $countRec = 1;
        while ($startRec <= $countRec) {
            $brandGetRet = $this->CallAPICurl("GET", $HOST . "/v2/brands/?offset=" . $startRec . "&limit=30", $headers, null);

            if ($brandGetRet['error'] == '0') {
                $brandJSON = $brandGetRet['return'];

                $startRec = $startRec + $brandJSON->page->size;
                $countRec = $brandJSON->page->totalElements;

                foreach ($brandJSON->content as $brand) {
                    $mBrands = Mage::getModel('db1_anymarket/anymarketbrands')->load($brand->id, 'brd_id');
                    $mBrands->setBrdId($brand->id);
                    $mBrands->setBrdName($brand->name);
                    $mBrands->setStatus("1");
                    $mBrands->setStores(array($storeID));
                    $mBrands->save();

                    $retCountBrand++;
                }
            } else {
                $startRec = 1;
                $countRec = 0;

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc('Error on get brands (' . $brandGetRet['return'] . ')');
                $anymarketlog->setStatus("1");
                $anymarketlog->setStores(array($storeID));
                $anymarketlog->save();
            }
        }
        return $retCountBrand;

    }


}