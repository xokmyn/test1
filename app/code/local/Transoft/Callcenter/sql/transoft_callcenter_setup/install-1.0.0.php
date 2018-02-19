<?php
/**
 * Callcenter module install script
 */

$this->startSetup();
/**
 * Create relations table initiator-order
 */
$order_table = Mage::getModel('sales/order')->getResource()->getTable('order');
$admin_user = Mage::getModel('admin/user')->getResource()->getTable('user');
$table = $this->getConnection()
    ->newTable($this->getTable('transoft_callcenter/initiator_order'))
    ->addColumn(
        'rel_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Relation ID'
    )
    ->addColumn(
        'initiator_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ),
        'Initiator ID'
    )
    ->addColumn(
        'order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ),
        'Order ID'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable' => false,
            'default' => '1',
        ),
        'Status'
    )
    ->addForeignKey(
        $this->getFkName(
            'transoft_callcenter/initiator_order',
            'initiator_id',
            $admin_user,
            'user_id'
        ),
        'initiator_id',
        $admin_user,
        'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex(
        $this->getIdxName(
            'transoft_callcenter/initiator_order',
            array('initiator_id', 'order_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('initiator_id', 'order_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Initiator to Order Linkage Table');
$this->getConnection()->createTable($table);

/**
 * 2 Roles Creation
 */
$roles = array(0 => 'Специалист колл-центра', 1 => 'Координатор колл-центра');
$resources = $this->getDefaultRoles();
foreach ($roles as $k => $role) {
    try {
        $col = Mage::getModel('admin/role')->setRoleName($role)->setRoleType('G')->setTreeLevel(1)->save();
        if ($col->getRoleId()) {
            if ($k === 0) {
                $resources = $this->getOrderResource();
            } elseif ($k === 1) {
                $resources = $this->getRemoveInitiatorResource();
            }
            Mage::getModel('admin/rules')
                ->setRoleId($col->getRoleId())
                ->setResources($resources)
                ->saveRel();
        }
    } catch (Exception $e) {
        Mage::logException($e);
    }
}

$this->endSetup();
