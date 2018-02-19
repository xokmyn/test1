<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2/16/2018
 * Time: 4:14 PM
 */
/**
 * Add new column to transoft_callcenter/initiator_order table
 */
$this->startSetup();

$this->getConnection()->addColumn($this->getTable('transoft_callcenter/initiator_order'), 'position', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'default' => 1,
    'comment' => 'Position'
));

$this->endSetup();
