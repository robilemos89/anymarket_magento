<?php
$installer = $this;
$installer->startSetup();

$tp = (string)Mage::getConfig()->getTablePrefix();

$sql = "INSERT IGNORE INTO `".$tp."catalog_product_entity_varchar` (entity_type_id, attribute_id, store_id, entity_id, value) SELECT pei.entity_type_id, pei.attribute_id, pei.store_id, pei.entity_id, pei.value FROM `".$tp."catalog_product_entity_int` pei JOIN `".$tp."eav_attribute` ea ON pei.attribute_id = ea .attribute_id WHERE ea.attribute_code = 'categoria_anymarket';";
$sql .= "DELETE pei.*  FROM `".$tp."catalog_product_entity_int` pei JOIN `".$tp."eav_attribute` ea ON pei.attribute_id = ea .attribute_id WHERE ea.attribute_code = 'categoria_anymarket';";

$installer->run($sql);
$installer->endSetup();