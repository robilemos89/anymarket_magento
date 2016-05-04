<?php
class DB1_AnyMarket_Model_Cron{

    /**
     * sinc all orders in feed and orders with errors
     */
    public function sincOrders(){
        Mage::helper('db1_anymarket/queue')->processOrders();
    }

    /**
     * sinc all products in feed and products with errors
     */
    public function sincProducts(){
        Mage::helper('db1_anymarket/queue')->processProducts();
    }

    /**
     * execute the queue
     */
    public function executeReindex(){
        Mage::helper('db1_anymarket/queue')->processReindex();
    }

    /**
     * execute the queue
     */
    public function executeQueue(){
        Mage::helper('db1_anymarket/queue')->processQueue();
    }

    /**
     * execute the clean logs
     */
    public function executeCleanLogs(){
        $contLogs = Mage::helper('db1_anymarket/queue')->processCleanLogs();

        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
        $anymarketlog->setLogDesc('Clean Logs by System, '.$contLogs.' cleaned');
        $anymarketlog->setStatus("1");
        $anymarketlog->save();
    }


}