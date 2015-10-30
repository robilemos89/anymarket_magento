<?php
$installer = $this;
$connection = $installer->getConnection();
 
$installer->startSetup();
$installer->getConnection()
->addColumn($installer->getTable('db1_anymarket/anymarketlog'),'log_json', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'length'    => 700,
    'comment'   => 'Json'
    ));
 
$installer->endSetup();