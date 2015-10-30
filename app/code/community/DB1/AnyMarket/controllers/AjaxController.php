<?php

class DB1_AnyMarket_AjaxController extends Mage_Core_Controller_Front_Action {
    public function categoryAction() {
        $categories = Mage::getModel('db1_anymarket/anymarketcategories')->getCollection()->setOrder('nmc_cat_desc', 'ASC');

        $categItem = array();
        foreach($categories as $category) {
            $categItem[] = array(
                           "rootid" => $category->getData('nmc_cat_root_id'),
                            "id" => $category->getData('nmc_cat_id'),
                            "desc" => $category->getData('nmc_cat_desc'),
                );
        }

        $categ = array( "categorias" => $categItem );

        echo json_encode( $categ );
    }
}