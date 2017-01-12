<?php
require_once('app/Mage.php'); //Path to Magento
umask(0);
Mage::app();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Add new Attribute group
$groupName = 'AnyMarket';
$entityTypeId = $setup->getEntityTypeId('catalog_product');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);
$setup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 100);

// Add Integra Anymarket to prduct attribute set
$codigo = 'integra_anymarket';
$config = array(
    'group' => 'AnyMarket',
    'position' => 0,
    'required' => 1,
    'label'    => 'Integrar produto com o AnyMarket',
    'type' => 'int',
    'input'    => 'boolean',
    'visible'  => true,
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => '',
    'global'   => 'Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL'
);

$setup->addAttribute('catalog_product', $codigo, $config);

// Add id_anymarket to prduct attribute set
$codigo = 'id_anymarket';
$config = array(
    'group' => 'AnyMarket',
    'position' => 1,
    'required' => 0,
    'label'    => 'Código Anymarket',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Código referente ao produto no AnyMarket(Não Preencher)'
);

$setup->addAttribute('catalog_product', $codigo, $config);

// Add categoria_anymarket to prduct attribute set
$codigo = 'categoria_anymarket';
$config = array(
    'group' => 'AnyMarket',
    'position' => 2,
    'required' => 0,
    'label'    => 'Categoria Anymarket',
    'type'     => 'varchar',
    'input'    => 'select',
    'backend_model' => 'eav/entity_attribute_backend_array',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'source_model' => 'db1_anymarket/system_config_source_categories_values',
    'user_defined' => 1,
    'note'     => 'Selecione uma categoria para enviar ao Anymarket'
);

$setup->addAttribute('catalog_product', $codigo, $config);

$entityTypeId     = $setup->getEntityTypeId('catalog_category');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$attribute  = array(
    'input'         => 'select',
    'type'          => 'int',
    'source'        => 'eav/entity_attribute_source_boolean',
    'label'         =>  'Integrar com AnyMarket',
    'global'        =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'       =>  true,
    'position'      => 0,
    'required'      =>  false,
    'user_defined'  =>  true,
    'default'       =>  "",
    'group'         =>  "General Information"
);
$setup->addAttribute('catalog_category', 'categ_integra_anymarket', $attribute);

$attributeId = $setup->getAttributeId($entityTypeId, 'categ_integra_anymarket');

$setup->run("
INSERT IGNORE INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");

$installer->endSetup();

$installer->startSetup();
$entityTypeId = $installer->getEntityTypeId('catalog_product');
$idAttributeOldSelect = $installer->getAttribute($entityTypeId, 'categoria_anymarket', 'attribute_id');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'frontend_input','select');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'backend_type','varchar');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'backend_model','eav/entity_attribute_backend_array');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'source_model','db1_anymarket/system_config_source_categories_values');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'is_global', 'Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, 'is_user_defined', 1);
$installer->endSetup();
?>