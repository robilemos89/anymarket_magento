<?php
$this->startSetup();
$tp = (string)Mage::getConfig()->getTablePrefix();

$tableName = $tp.$this->getTable('db1_anymarket/anymarketbrands');
if( $this->getConnection()->isTableExists($tableName) != true ) {
    $table = $this->getConnection()
        ->newTable($this->getTable('db1_anymarket/anymarketbrands'))
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ),
            'Anymarketbrands ID'
        )
        ->addColumn(
            'brd_id',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(
                'nullable' => false,
            ),
            'Anymarketbrands Codigo Anymarket'
        )
        ->addColumn(
            'brd_name',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(
                'nullable' => false,
            ),
            'Anymarketbrands Descricao'
        )
        ->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
            array(),
            'Enabled'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Anymarketbrands Modification Time'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Anymarketbrands Creation Time'
        )
        ->setComment('Anymarketbrands Table');
    $this->getConnection()->createTable($table);
}

$tableName = $tp.$this->getTable('db1_anymarket/anymarketbrands_store');
if( $this->getConnection()->isTableExists($tableName) != true ) {
    $table = $this->getConnection()
        ->newTable($this->getTable('db1_anymarket/anymarketbrands_store'))
        ->addColumn(
            'anymarketbrands_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'nullable' => false,
                'primary' => true,
            ),
            'Anymarketbrands ID'
        )
        ->addColumn(
            'store_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ),
            'Store ID'
        )
        ->addIndex(
            $this->getIdxName(
                'db1_anymarket/anymarketbrands_store',
                array('store_id')
            ),
            array('store_id')
        )
        ->addForeignKey(
            $this->getFkName(
                'db1_anymarket/anymarketbrands_store',
                'anymarketbrands_id',
                'db1_anymarket/anymarketbrands',
                'entity_id'
            ),
            'anymarketbrands_id',
            $this->getTable('db1_anymarket/anymarketbrands'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                'db1_anymarket/anymarketbrands_store',
                'store_id',
                'core/store',
                'store_id'
            ),
            'store_id',
            $this->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Anymarketbrand To Store Linkage Table');
    $this->getConnection()->createTable($table);
}

$this->endSetup();