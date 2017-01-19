<?php
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Add Integra Anymarket to prduct attribute set
$codigo = 'exp_sep_simp_prod';
$config = array(
    'group' => 'AnyMarket',
    'position' => 0,
    'required' => 0,
    'label'    => 'Integrar Somente Produtos Associados',
    'type' => 'int',
    'input'    => 'boolean',
    'visible'  => true,
    'used_in_product_listing' => true,
    'apply_to' => 'bundle,grouped,configurable',
    'note'     => 'Cadastrar somente os produtos associados como produto simples no Anymarket.',
    'user_defined' => 1,
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
);

$setup->addAttribute('catalog_product', $codigo, $config);

$installer->endSetup();