<?php

class DB1_AnyMarket_Helper_Category extends DB1_AnyMarket_Helper_Data
{

    /**
     * get all root category of AM
     *
     * @access public
     * @return void
     * 
     */
    public function getCategories(){
        $HOST  = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_host_field', Mage::app()->getStore()->getId());
        $TOKEN = Mage::getStoreConfig('anymarket_section/anymarket_acesso_group/anymarket_token_field', Mage::app()->getStore()->getId());

        $headers = array( 
            "Content-type: application/json",
            "Accept: */*",
            "gumgaToken: ".$TOKEN
        );

        $startRec = 0;
        $countRec = 1;
        $arrOrderCod = null;

        while ($startRec <= $countRec) {
            $returnCat = $this->CallAPICurl("GET", $HOST."/rest/api/v2/categories/?offset=".$startRec."&limit=30", $headers, null);

            if($returnCat['error'] == '1'){
                $startRec = 1;
                $countRec = 0;

                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setLogDesc( 'Error on Sincronize Category '. $returnCat['return'] );
                $anymarketlog->setStatus("1");
                $anymarketlog->save();
            }else{
                $CatJSON = $returnCat['return'];

                $startRec = $startRec + $CatJSON->page->size;
                $countRec = $CatJSON->page->totalElements;

                foreach ($CatJSON->content as $category) {
                    $IDCat = $category->id;

                    $anymarketcategoriesUpdt = Mage::getModel('db1_anymarket/anymarketcategories')->load($IDCat, 'nmc_cat_id');
                    if($anymarketcategoriesUpdt->getData('nmc_cat_id') == null){
                        $anymarketcategories = Mage::getModel('db1_anymarket/anymarketcategories');
                        $anymarketcategories->setNmcCatId( $IDCat );
                        $anymarketcategories->setNmcCatRootId( '000' );
                        $anymarketcategories->setStatus('1');
                        $anymarketcategories->setNmcCatDesc( $category->name );
                        $anymarketcategories->save();
                    }else{
                        $anymarketcategoriesUpdt->setNmcCatDesc( $category->name );
                        $anymarketcategoriesUpdt->save();
                    }

                    $this->getChildCat($HOST, $headers, $category->id, $IDCat);
                }

            }

        }

    }


    /**
     * get all child category of AM
     *
     * @access private
     * @param $HOST, $headers, $catID, $IDCatRoot
     * @return void
     * 
     */
    private function getChildCat($HOST, $headers, $catID, $IDCatRoot){
        $returnCatSpecific = $this->CallAPICurl("GET", $HOST."/rest/api/v2/categories/".$catID, $headers, null);
        $CatSpecifivJSON = $returnCatSpecific['return'];
        if($returnCatSpecific['error'] == '0'){
            if( isset($CatSpecifivJSON->children) ){
                foreach ($CatSpecifivJSON->children as $catChild) {

                    $anymarketcategoriesUpdt = Mage::getModel('db1_anymarket/anymarketcategories')->load($catChild->id, 'nmc_cat_id');
                    if($anymarketcategoriesUpdt->getData('nmc_cat_id') == null){
                        $anymarketcategories = Mage::getModel('db1_anymarket/anymarketcategories');
                        $anymarketcategories->setNmcCatId( $catChild->id );
                        $anymarketcategories->setNmcCatRootId( $IDCatRoot );
                        $anymarketcategories->setStatus('1');
                        $anymarketcategories->setNmcCatDesc( $catChild->name );
                        $anymarketcategories->save();
                    }else{
                        $anymarketcategoriesUpdt->setNmcCatDesc( $catChild->name );
                        $anymarketcategoriesUpdt->save();
                    }

                    $this->getChildCat($HOST, $headers, $catChild->id, $catChild->id);
                }
            }
        }else{
            $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
            $anymarketlog->setLogDesc( $returnCatSpecific['return'] );
            $anymarketlog->setLogId( $IDCatRoot ); 
            $anymarketlog->setStatus("1");
            $anymarketlog->save();
        }
    }


}