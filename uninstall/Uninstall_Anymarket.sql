DROP TABLE db1_anymarket_anymarketattributes_store;
DROP TABLE db1_anymarket_anymarketattributes;
DROP TABLE db1_anymarket_anymarketbrands_store;
DROP TABLE db1_anymarket_anymarketbrands;
DROP TABLE db1_anymarket_anymarketcategories_store;
DROP TABLE db1_anymarket_anymarketcategories;
DROP TABLE db1_anymarket_anymarketlog_store;
DROP TABLE db1_anymarket_anymarketlog;
DROP TABLE db1_anymarket_anymarketorders_store;
DROP TABLE db1_anymarket_anymarketorders;
DROP TABLE db1_anymarket_anymarketproducts_store;
DROP TABLE db1_anymarket_anymarketproducts;
DROP TABLE db1_anymarket_anymarketqueue_store;
DROP TABLE db1_anymarket_anymarketqueue;

DELETE FROM `core_config_data` WHERE `path` LIKE 'anymarket_section%';
DELETE FROM `core_resource` WHERE `code`='db1_anymarket_setup';
DELETE FROM `eav_attribute` WHERE frontend_label = 'Categoria Anymarket'