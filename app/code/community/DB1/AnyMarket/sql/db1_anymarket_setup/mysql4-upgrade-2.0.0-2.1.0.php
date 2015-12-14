<?php
$installer = $this;
$installer->startSetup();

$sql = "INSERT IGNORE INTO `db1_anymarket_anymarketcategories_store` (`anymarketcategories_id`, `store_id`) SELECT entity_id, 0 FROM `db1_anymarket_anymarketcategories`;";
$sql .= "INSERT IGNORE INTO `db1_anymarket_anymarketattributes_store` (`anymarketattributes_id`, `store_id`) SELECT entity_id, 0 FROM `db1_anymarket_anymarketattributes`;";
$sql .= "INSERT IGNORE INTO `db1_anymarket_anymarketqueue_store` (`anymarketqueue_id`, `store_id`) SELECT entity_id, 0 FROM `db1_anymarket_anymarketqueue`;";
$sql .= "INSERT IGNORE INTO `db1_anymarket_anymarketproducts_store` (`anymarketproducts_id`, `store_id`) SELECT entity_id, 0 FROM `db1_anymarket_anymarketproducts`;";
$sql .= "INSERT IGNORE INTO `db1_anymarket_anymarketorders_store` (`anymarketorders_id`, `store_id`) SELECT entity_id, 0 FROM `db1_anymarket_anymarketorders`;";

$installer->run($sql);
$installer->endSetup();