<?php
$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

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
    'required'      =>  true,
    'user_defined'  =>  true,
    'default'       =>  "",
    'group'         =>  "General Information"
);
$setup->addAttribute('catalog_category', 'categ_integra_anymarket', $attribute);

$attributeId = $setup->getAttributeId($entityTypeId, 'categ_integra_anymarket');
 
$setup->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");
 
 
//this will set data of attribute for root category
Mage::getModel('catalog/category')
    ->load(1)
    ->setImportedCatId(0)
    ->setInitialSetupFlag(true)
    ->save();
 
//this will set data of attribute for default category
Mage::getModel('catalog/category')
    ->load(2)
    ->setImportedCatId(0)
    ->setInitialSetupFlag(true)
    ->save();

$setup->getConnection()
    ->addColumn($this->getTable('db1_anymarket/anymarketcategories'),'nmc_id_magento', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => false,
        'length'    => 255,
        'comment'   => 'ID Category in Magento'
        ));

$setup->run("DELETE FROM `core_config_data` WHERE `path` LIKE 'anymarket_section%'");

$installer->endSetup();
?>