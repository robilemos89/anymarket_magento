<?php
class DB1_AnyMarket_Model_System_Config_Source_Categories_Values extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $retornArray = array();
    protected $descComp;

    private function getChildCat($IDRoot, $DescCateg){
        $categCh = Mage::getModel('db1_anymarket/anymarketcategories')->getCollection()
                   ->addFilter('nmc_cat_root_id', $IDRoot);

        $ctrlCat = '';
        if(sizeof($categCh) >1){
            $ctrlCat = $DescCateg;
        }

        foreach ($categCh as $catCh) {
            if($ctrlCat == ''){
                $DescCateg = $DescCateg.'\\'.$catCh->getData('nmc_cat_desc');
            }else{
                $DescCateg = $ctrlCat.'\\'.$catCh->getData('nmc_cat_desc');
            }
            $IDRoot = $catCh->getData('nmc_cat_id');

            $returnCateg = $this->getChildCat($IDRoot, $DescCateg);
            $hChild = $returnCateg["return"];
            $IDRoot = $returnCateg["root"];
        }

        if($IDRoot != ''){
            array_push($this->retornArray, array( 'value' => $IDRoot, 'label' => $DescCateg ) );
        }

        return array("return" => false, "root" => "", "desc" => "");
    }

    public function getAllOptions()
    {
        if (!Mage::app()->isSingleStoreMode()){
            $store = Mage::app()->getRequest()->getParam('store');
            $categories = Mage::getModel('db1_anymarket/anymarketcategories')->getCollection()
                          ->addFilter('nmc_cat_root_id','000')
                          ->addStoreFilter($store)
                          ->setOrder('nmc_cat_desc', 'ASC');
        }else{
            $categories = Mage::getModel('db1_anymarket/anymarketcategories')->getCollection()
                          ->addFilter('nmc_cat_root_id','000')
                          ->setOrder('nmc_cat_desc', 'ASC');
        }

        array_push($this->retornArray, array( 'value' => null, 'label' => ' ' ) );
        foreach($categories as $category) {
            $hChild = true;
            $IDRoot = $category->getData('nmc_cat_id');
            $DescCateg = $category->getData('nmc_cat_desc');
            $this->descComp = $DescCateg;
            while ( $hChild ) {
                $returnCateg = $this->getChildCat($IDRoot, $DescCateg);
                $hChild = $returnCateg["return"];
                $IDRoot = $returnCateg["root"];
                $DescCateg = $returnCateg["desc"];
            }

        }

        return $this->retornArray;
    }
}
