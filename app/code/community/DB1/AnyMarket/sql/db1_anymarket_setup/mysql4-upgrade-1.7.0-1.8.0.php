<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->updateAttribute('catalog_product', 'categoria_anymarket', 'note', 'Selecione uma categoria para subir ao Anymarket');
$setup->updateAttribute('catalog_product', 'categoria_anymarket', 'is_global', 'Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL');
$setup->updateAttribute('catalog_product', 'id_anymarket', 'is_global', 'Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL');

$installer->endSetup();