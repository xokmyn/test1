<?php
/**
 * Update callcenter for using new field in admin user
 * add new attribute to product
 * add new attribute set with new attribute
 */

$this->startSetup();
/**
 * Add new column to admin_user table
 */
$this->getConnection()->addColumn($this->getTable('admin/user'), 'callcenter_type', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'default' => 0,
    'comment' => 'Type callcenter user'
));

/**
 * Add new attribute set
 */
$entityTypeId = Mage::getModel('catalog/product')
    ->getResource()
    ->getEntityType()
    ->getId(); //product entity type

$attributeSet = Mage::getModel('eav/entity_attribute_set')
    ->setEntityTypeId($entityTypeId)
    ->setAttributeSetName('Format type');

$attributeSet->validate();
$attributeSet->save();

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$skeletonID = $installer->getAttributeSetId('catalog_product', 'Default');
$attributeSet->initFromSkeleton($skeletonID)->save();

/**
 * Add new attribute to this new attribute set
 */
$installer->addAttribute('catalog_product', 'callcenter_format_type', array(
    'group' => 'General',
    'label' => 'Callcenter format type',
    'input' => 'select',
    'type' => 'int',
    'required' => 0,
    'unique' => false,
    'visible_on_front' => 0,
    'filterable' => 0,
    'searchable' => 0,
    'comparable' => 0,
    'user_defined' => false,
    'is_configurable' => 0,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'note' => 'Callcenter format type',
    'option' =>
        array(
            'values' =>
                array(
                    0 => 'обычный',
                    1 => 'крупногабаритный',
                ),
        ),
));

// Add the newly created `callcenter_format_type` attribute to attribute set(s)
$installer->addAttributeToSet(
    Mage_Catalog_Model_Product::ENTITY,   // Entity type
    'Format type',                             // Attribute set name
    'General',                               // Attribute set group name
    'callcenter_format_type',              // Attribute code to add
    1                                       // Position on the attribute set group
);

$this->endSetup();
