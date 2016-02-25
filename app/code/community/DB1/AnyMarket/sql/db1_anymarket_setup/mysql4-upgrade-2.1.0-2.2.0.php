<?php

$installer = $this;
$installer->startSetup();
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'categoria_anymarket', 'is_user_defined', 1);
$installer->endSetup();