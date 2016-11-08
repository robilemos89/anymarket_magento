<?php
$installer = $this;
$installer->startSetup();
$tp = (string)Mage::getConfig()->getTablePrefix();

$sql .= "";
$tableName = $tp."db1_anymarket_anymarketcategories_store";
if( $this->getConnection()->isTableExists($tableName) ) {
    $sql .= "INSERT IGNORE INTO `" . $tp . "db1_anymarket_anymarketcategories_store` (`anymarketcategories_id`, `store_id`) SELECT entity_id, 0 FROM `" . $tp . "db1_anymarket_anymarketcategories`;";
}

$tableName = $tp."db1_anymarket_anymarketattributes_store";
if( $this->getConnection()->isTableExists($tableName) ) {
    $sql .= "INSERT IGNORE INTO `".$tp."db1_anymarket_anymarketattributes_store` (`anymarketattributes_id`, `store_id`) SELECT entity_id, 0 FROM `".$tp."db1_anymarket_anymarketattributes`;";
}

$tableName = $tp."db1_anymarket_anymarketqueue_store";
if( $this->getConnection()->isTableExists($tableName) ) {
    $sql .= "INSERT IGNORE INTO `".$tp."db1_anymarket_anymarketqueue_store` (`anymarketqueue_id`, `store_id`) SELECT entity_id, 0 FROM `".$tp."db1_anymarket_anymarketqueue`;";
}

$tableName = $tp."db1_anymarket_anymarketproducts_store";
if( $this->getConnection()->isTableExists($tableName) ) {
    $sql .= "INSERT IGNORE INTO `".$tp."db1_anymarket_anymarketproducts_store` (`anymarketproducts_id`, `store_id`) SELECT entity_id, 0 FROM `".$tp."db1_anymarket_anymarketproducts`;";
}

$tableName = $tp."db1_anymarket_anymarketorders_store";
if( $this->getConnection()->isTableExists($tableName) ) {
    $sql .= "INSERT IGNORE INTO `".$tp."db1_anymarket_anymarketorders_store` (`anymarketorders_id`, `store_id`) SELECT entity_id, 0 FROM `".$tp."db1_anymarket_anymarketorders`;";
}

if($sql != "") {
    $installer->run($sql);
}
$installer->endSetup();