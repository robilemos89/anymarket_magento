--
-- Estrutura da tabela `db1_anymarket_anymarketattributes`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketattributes` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Anymarket Attributes ID',
`nma_id_attr` varchar(255) NOT NULL COMMENT 'Código do Atributo',
`nma_desc` varchar(255) NOT NULL COMMENT 'Descrição do Atributo',
`status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Attributes Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Attributes Creation Time',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Attributes Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketattributes_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketattributes_store` (
`anymarketattributes_id` int(11) NOT NULL COMMENT 'Anymarket Attributes ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketattributes_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETATTRIBUTES_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Attributes To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketbrands`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketbrands` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Anymarketbrands ID',
`brd_id` varchar(255) NOT NULL COMMENT 'Anymarketbrands Codigo Anymarket',
`brd_name` varchar(255) NOT NULL COMMENT 'Anymarketbrands Descricao',
`status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarketbrands Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarketbrands Creation Time',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarketbrands Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketbrands_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketbrands_store` (
`anymarketbrands_id` int(11) NOT NULL COMMENT 'Anymarketbrands ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketbrands_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETBRANDS_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarketbrand To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketcategories`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketcategories` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Anymarket Categories ID',
`nmc_cat_id` varchar(255) NOT NULL COMMENT 'Código completo da Categoria',
`nmc_cat_root_id` varchar(255) NOT NULL COMMENT 'Código da categoria antecessora',
`nmc_cat_desc` varchar(255) NOT NULL COMMENT 'Decrição da Categoria',
`nmc_id_magento` varchar(255) DEFAULT NULL COMMENT 'ID Category in Magento',
`status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Categories Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Categories Creation Time',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Categories Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketcategories_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketcategories_store` (
`anymarketcategories_id` int(11) NOT NULL COMMENT 'Anymarket Categories ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketcategories_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETCATEGORIES_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Categories To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketimage`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketimage` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'AnyMarket Imagem',
`value_id` varchar(255) DEFAULT NULL COMMENT 'id image magento',
`id_image` varchar(255) DEFAULT NULL COMMENT 'id image anymarket',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='AnyMarket Image Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketlog`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketlog` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'AnyMarket Log ID',
`log_id` varchar(255) NOT NULL COMMENT 'Identificação do produto ou pedido',
`log_desc` text NOT NULL COMMENT 'Descrição Log',
`status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'AnyMarket Log Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'AnyMarket Log Creation Time',
`log_json` text NOT NULL COMMENT 'Json',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='AnyMarket Log Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketlog_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketlog_store` (
`anymarketlog_id` int(11) NOT NULL COMMENT 'AnyMarket Log ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketlog_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETLOG_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='AnyMarket Log To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketorders`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketorders` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Anymarket Orders ID',
`nmo_id_anymarket` varchar(255) NOT NULL COMMENT 'Código no AnyMarket',
`nmo_id_seq_anymarket` varchar(255) NOT NULL COMMENT 'Código sequencial no AnyMarket',
`nmo_id_order` varchar(255) NOT NULL COMMENT 'Código Venda Magento',
`nmo_status_int` varchar(255) NOT NULL COMMENT 'Status Integração',
`nmo_desc_error` varchar(255) NOT NULL COMMENT 'Descrição do Erro',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Orders Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Orders Creation Time',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Orders Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketorders_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketorders_store` (
`anymarketorders_id` int(11) NOT NULL COMMENT 'Anymarket Orders ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketorders_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETORDERS_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Orders To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketproducts`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketproducts` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Anymarket Products ID',
`nmp_id` varchar(255) NOT NULL COMMENT 'ID do produto',
`nmp_sku` varchar(255) NOT NULL COMMENT 'SKU do produto',
`nmp_name` varchar(255) NOT NULL COMMENT 'Nome do Produto',
`nmp_desc_error` text NOT NULL COMMENT 'Descrição do Erro',
`nmp_status_int` varchar(255) NOT NULL COMMENT 'Status Integração',
`status` smallint(6) DEFAULT NULL COMMENT 'Enabled',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Products Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Products Creation Time',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Products Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketproducts_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketproducts_store` (
`anymarketproducts_id` int(11) NOT NULL COMMENT 'Anymarket Products ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketproducts_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETPRODUCTS_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Products To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketqueue`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketqueue` (
`entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Anymarket Queue ID',
`nmq_id` varchar(255) NOT NULL COMMENT 'Código do Item que esta aguardando na fila',
`nmq_type` varchar(3) NOT NULL COMMENT 'Tipo de Operação (IMP/EXP)',
`nmq_table` varchar(25) NOT NULL COMMENT 'Tabela originaria do item',
`updated_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Queue Modification Time',
`created_at` timestamp NULL DEFAULT NULL COMMENT 'Anymarket Queue Creation Time',
PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Queue Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `db1_anymarket_anymarketqueue_store`
--

CREATE TABLE IF NOT EXISTS `db1_anymarket_anymarketqueue_store` (
`anymarketqueue_id` int(11) NOT NULL COMMENT 'Anymarket Queue ID',
`store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
PRIMARY KEY (`anymarketqueue_id`,`store_id`),
KEY `IDX_DB1_ANYMARKET_ANYMARKETQUEUE_STORE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anymarket Queues To Store Linkage Table';

-- --------------------------------------------------------

--
-- Estrutura da tabela `design_change`
--

CREATE TABLE IF NOT EXISTS `design_change` (
`design_change_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Design Change Id',
`store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Id',
`design` varchar(255) DEFAULT NULL COMMENT 'Design',
`date_from` date DEFAULT NULL COMMENT 'First Date of Design Activity',
`date_to` date DEFAULT NULL COMMENT 'Last Date of Design Activity',
PRIMARY KEY (`design_change_id`),
KEY `IDX_DESIGN_CHANGE_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Design Changes' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Limitadores para a tabela `db1_anymarket_anymarketattributes_store`
--
ALTER TABLE `db1_anymarket_anymarketattributes_store`
ADD CONSTRAINT `FK_EAC2F29D6B697E407AAF122F7717954F` FOREIGN KEY (`anymarketattributes_id`) REFERENCES `db1_anymarket_anymarketattributes` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_4B9D519EB22C82AB80E437DF71E5CCE1` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `db1_anymarket_anymarketbrands_store`
--
ALTER TABLE `db1_anymarket_anymarketbrands_store`
ADD CONSTRAINT `FK_A60AFAA940C0A62204B5C1E9D8924D9A` FOREIGN KEY (`anymarketbrands_id`) REFERENCES `db1_anymarket_anymarketbrands` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_97B56FAD6A52820ED7B95A7F70BB137C` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `db1_anymarket_anymarketcategories_store`
--
ALTER TABLE `db1_anymarket_anymarketcategories_store`
ADD CONSTRAINT `FK_A606608F4DA74C8B41053BC3A1D2E823` FOREIGN KEY (`anymarketcategories_id`) REFERENCES `db1_anymarket_anymarketcategories` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_E0078750827E816D02B2DDC2D12CAC9E` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `db1_anymarket_anymarketlog_store`
--
ALTER TABLE `db1_anymarket_anymarketlog_store`
ADD CONSTRAINT `FK_5EB7F98D444F8AB931FF16FE2020CFB0` FOREIGN KEY (`anymarketlog_id`) REFERENCES `db1_anymarket_anymarketlog` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_DB1_ANYMARKET_ANYMARKETLOG_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `db1_anymarket_anymarketorders_store`
--
ALTER TABLE `db1_anymarket_anymarketorders_store`
ADD CONSTRAINT `FK_2EE2F91638D7F31BFD39BCDEFBFD1C11` FOREIGN KEY (`anymarketorders_id`) REFERENCES `db1_anymarket_anymarketorders` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_B0C5FAE64B605AC7F24E606FB469D4DB` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `db1_anymarket_anymarketproducts_store`
--
ALTER TABLE `db1_anymarket_anymarketproducts_store`
ADD CONSTRAINT `FK_8E7D5CA445597D32E0E3EDC13514897D` FOREIGN KEY (`anymarketproducts_id`) REFERENCES `db1_anymarket_anymarketproducts` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_F9E576431BB7F5859B6207532CF1AC9A` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `db1_anymarket_anymarketqueue_store`
--
ALTER TABLE `db1_anymarket_anymarketqueue_store`
ADD CONSTRAINT `FK_9373F65EE3C1C4123EBCC93B58029F8B` FOREIGN KEY (`anymarketqueue_id`) REFERENCES `db1_anymarket_anymarketqueue` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_E9C88E466588659C0F26ACF56F890CC7` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
--
--
INSERT INTO `core_resource` (`code`, `version`, `data_version`) VALUES
('db1_anymarket_setup', '2.12.1', '2.12.1');
