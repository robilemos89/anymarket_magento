<?php
$installer = $this;
$installer->startSetup();

$resource = Mage::getSingleton('core/resource');

function getMagentoVersion()
{
    return substr(str_replace(".", "", Mage::getVersion()), 0, 2);
}
function _tableExists($table, $resource)
{
    if (getMagentoVersion() < 16) {
        return array_search($table, $resource->getConnection('core_write')->listTables());
    } 
    return $resource->getConnection('core_write')->isTableExists($table);
}

$sql = '';
$table = 'db1_anymarket_anymarketattributes01';
if (_tableExists($table, $resource)) {
	$sql = "ALTER TABLE db1_anymarket_anymarketattributes01 RENAME db1_anymarket_anymarketattributes;";
	$sql .= "ALTER TABLE db1_anymarket_anymarketattributes01_store RENAME db1_anymarket_anymarketattributes_store;";
	$sql .= "SET foreign_key_checks = 0;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketattributes_store` CHANGE  `anymarketattributes01_id` `anymarketattributes_id` int(11) NOT NULL;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketattributes_store` ADD CONSTRAINT `fk13_anymarketattributes_id` FOREIGN KEY ( `anymarketattributes_id` ) REFERENCES `db1_anymarket_anymarketattributes` ( `entity_id` );";
}

$table = 'db1_anymarket_anymarketcategories01';
if (_tableExists($table, $resource)) {
	$sql .= "ALTER TABLE db1_anymarket_anymarketcategories01 RENAME db1_anymarket_anymarketcategories;";
	$sql .= "ALTER TABLE db1_anymarket_anymarketcategories01_store RENAME db1_anymarket_anymarketcategories_store;";
	$sql .= "SET foreign_key_checks = 0;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketcategories_store` CHANGE  `anymarketcategories01_id` `anymarketcategories_id` int(11) NOT NULL;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketcategories_store` ADD CONSTRAINT `fk13_anymarketcategories_id` FOREIGN KEY ( `anymarketcategories_id` ) REFERENCES `db1_anymarket_anymarketcategories` ( `entity_id` );";
}

$table = 'db1_anymarket_anymarketlog01';
if (_tableExists($table, $resource)) {
	$sql .= "ALTER TABLE db1_anymarket_anymarketlog01 RENAME db1_anymarket_anymarketlog;";
	$sql .= "ALTER TABLE db1_anymarket_anymarketlog01_store RENAME db1_anymarket_anymarketlog_store;";
	$sql .= "SET foreign_key_checks = 0;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketlog_store` CHANGE  `anymarketlog01_id` `anymarketlog_id` int(11) NOT NULL;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketlog_store` ADD CONSTRAINT `fk13_anymarketlog_id` FOREIGN KEY ( `anymarketlog_id` ) REFERENCES `db1_anymarket_anymarketlog` ( `entity_id` );";
}

$table = 'db1_anymarket_anymarketorders01';
if (_tableExists($table, $resource)) {
	$sql .= "ALTER TABLE db1_anymarket_anymarketorders01 RENAME db1_anymarket_anymarketorders;";
	$sql .= "ALTER TABLE db1_anymarket_anymarketorders01_store RENAME db1_anymarket_anymarketorders_store;";
	$sql .= "SET foreign_key_checks = 0;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketorders_store` CHANGE  `anymarketorders01_id` `anymarketorders_id` int(11) NOT NULL;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketorders_store` ADD CONSTRAINT `fk13_anymarketcategories_id` FOREIGN KEY ( `anymarketcategories_id` ) REFERENCES `db1_anymarket_anymarketcategories` ( `entity_id` );";
}

$table = 'db1_anymarket_anymarketproducts01';
if (_tableExists($table, $resource)) {
	$sql .= "ALTER TABLE db1_anymarket_anymarketproducts01 RENAME db1_anymarket_anymarketproducts;";
	$sql .= "ALTER TABLE db1_anymarket_anymarketproducts01_store RENAME db1_anymarket_anymarketproducts_store;";
	$sql .= "SET foreign_key_checks = 0;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketproducts_store` CHANGE  `anymarketproducts01_id` `anymarketproducts_id` int(11) NOT NULL;";
	$sql .= "ALTER TABLE `db1_anymarket_anymarketproducts_store` ADD CONSTRAINT `fk13_anymarketcategories_id` FOREIGN KEY ( `anymarketcategories_id` ) REFERENCES `db1_anymarket_anymarketcategories` ( `entity_id` );";
}

if ($sql != '') {
	$installer->run($sql);
}
$installer->endSetup();