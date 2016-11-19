
<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn(
        $installer->getTable('salesrule/rule'),
        'customer_id',
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'    => '10',
            'nullable'  => false,
            'default'   => 0,
            'comment'  => 'Customer Id',
        )
    );

$installer->endSetup();