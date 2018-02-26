<?php
/**
 * Callcenter module install script
 */

$this->startSetup();
/**
 * Create relations table initiator-order
 */
$orderTable = Mage::getModel('sales/order')->getResource()->getTable('order');
$adminUser = Mage::getModel('admin/user')->getResource()->getTable('user');

$table = $this->getConnection()
    ->newTable($this->getTable('transoft_callcenter/initiator'))
    ->addColumn(
        'entity_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Initiator Order ID'
    )
    ->addColumn(
        'initiator_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => false,
            'unsigned' => true,
        ),
        'Initiator ID'
    )
    ->addColumn(
        'order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'nullable' => false,
            'default' => 0
        ),
        'Order Id'
    )
    ->addColumn(
        'position',
        Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
            'default' => 1
        ),
        'Position'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'default' => 1
        ),
        'Enabled'
    )
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Initiator Order Modification Time'
    )
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(),
        'Initiator Order Creation Time'
    )
    ->addForeignKey(
        $this->getFkName(
            'transoft_callcenter/initiator',
            'initiator_id',
            $adminUser,
            'user_id'
        ),
        'initiator_id',
        $adminUser,
        'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex(
        $this->getIdxName(
            'transoft_callcenter/initiator',
            array('initiator_id', 'order_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('initiator_id', 'order_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Initiator to Order Linkage Table');
$this->getConnection()->createTable($table);
$this->endSetup();
