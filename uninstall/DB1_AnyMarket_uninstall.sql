-- add table prefix if you have one
DROP TABLE IF EXISTS db1_anymarket_anymarketlog_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketlog;
DROP TABLE IF EXISTS db1_anymarket_anymarketproducts_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketproducts;
DROP TABLE IF EXISTS db1_anymarket_anymarketattributes_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketattributes;
DROP TABLE IF EXISTS db1_anymarket_anymarketorders_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketorders;
DROP TABLE IF EXISTS db1_anymarket_anymarketcategories_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketcategories;

DROP TABLE IF EXISTS db1_anymarket_anymarketlog01_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketlog01;
DROP TABLE IF EXISTS db1_anymarket_anymarketproducts01_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketproducts01;
DROP TABLE IF EXISTS db1_anymarket_anymarketattributes01_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketattributes01;
DROP TABLE IF EXISTS db1_anymarket_anymarketorders01_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketorders01;
DROP TABLE IF EXISTS db1_anymarket_anymarketcategories01_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketcategories01;

DROP TABLE IF EXISTS db1_anymarket_anymarketqueue_store;
DROP TABLE IF EXISTS db1_anymarket_anymarketqueue;
DELETE FROM core_resource WHERE code = 'db1_anymarket_setup';
DELETE FROM core_config_data WHERE path like 'db1_anymarket/%';