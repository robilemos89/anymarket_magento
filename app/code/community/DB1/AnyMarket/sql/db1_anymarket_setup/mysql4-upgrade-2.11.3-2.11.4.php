<?php
$this->startSetup();

$tableName = $this->getTable('db1_anymarket/anymarketimage');
if( $this->getConnection()->isTableExists( $tableName ) != true ) {
    $table = $this->getConnection()
        ->newTable($tableName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ),
            'AnyMarket Imagem'
        )
        ->addColumn(
            'value_id',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(
                'nullable' => true,
            ),
            'id image magento'
        )
        ->addColumn(
            'id_image',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(
                'nullable' => true,
            ),
            'id image anymarket'
        )
        ->setComment('AnyMarket Image Table');
    $this->getConnection()->createTable($table);
}

$this->endSetup();
