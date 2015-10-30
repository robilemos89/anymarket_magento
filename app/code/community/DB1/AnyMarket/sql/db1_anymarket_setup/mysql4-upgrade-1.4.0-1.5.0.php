<?php
$this->startSetup();

$table = $this->getConnection()
    ->newTable($this->getTable('db1_anymarket/anymarketqueue'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Anymarket Queue ID'
    )
    ->addColumn(
        'nmq_id',
        Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,
        ),
        'Código do Item que esta aguardando na fila'
    )
    ->addColumn(
        'nmq_type',
        Varien_Db_Ddl_Table::TYPE_TEXT, 3,
        array(
            'nullable'  => false,
        ),
        'Tipo de Operação (IMP/EXP)'
    )
    ->addColumn(
        'nmq_table',
        Varien_Db_Ddl_Table::TYPE_TEXT, 25,
        array(
            'nullable'  => false,
        ),
        'Tabela originaria do item'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Anymarket Queue Modification Time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Anymarket Queue Creation Time'
    ) 
    ->setComment('Anymarket Queue Table');
$this->getConnection()->createTable($table);

$table = $this->getConnection()
    ->newTable($this->getTable('db1_anymarket/anymarketqueue_store'))
    ->addColumn(
        'anymarketqueue_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable'  => false,
            'primary'   => true,
        ),
        'Anymarket Queue ID'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Store ID'
    )
    ->addIndex(
        $this->getIdxName(
            'db1_anymarket/anymarketqueue_store',
            array('store_id')
        ),
        array('store_id')
    )
    ->addForeignKey(
        $this->getFkName(
            'db1_anymarket/anymarketqueue_store',
            'anymarketqueue_id',
            'db1_anymarket/anymarketqueue',
            'entity_id'
        ),
        'anymarketqueue_id',
        $this->getTable('db1_anymarket/anymarketqueue'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $this->getFkName(
            'db1_anymarket/anymarketqueue_store',
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
    ->setComment('Anymarket Queues To Store Linkage Table');
$this->getConnection()->createTable($table);

$this->endSetup();