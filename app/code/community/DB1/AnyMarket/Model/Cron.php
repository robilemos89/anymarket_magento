<?php
class DB1_AnyMarket_Model_Cron{

    /**
     * sinc all stocks in feed and products with errors
     */
    public function sincStocks(){
        Mage::helper('db1_anymarket/queue')->processStocks();
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
        Mage::helper('db1_anymarket/queue')->processQueue("CRON");
    }

    public function sincOrdersWithErrors(){
        Mage::helper('db1_anymarket/queue')->processOrdersWithError01();
    }

    /**
     * execute the clean logs
     */
    public function executeCleanLogs(){
        $contLogs = Mage::helper('db1_anymarket/queue')->processCleanLogs();

        $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
        $anymarketlog->setLogDesc('Clean Logs by System, '.$contLogs.' cleaned');
        $anymarketlog->setStatus("0");
        $anymarketlog->save();
    }


}