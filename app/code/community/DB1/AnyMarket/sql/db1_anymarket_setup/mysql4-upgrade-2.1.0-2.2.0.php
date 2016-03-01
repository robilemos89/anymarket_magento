<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->updateAttribute('catalog_product', 'categoria_anymarket', 'is_user_defined', 1);

$installer->endSetup();