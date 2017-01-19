<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$installer->getTable('sales/quote_item')} ADD COLUMN is_backorder INT(1) DEFAULT 0;
");

$installer->run("
ALTER TABLE {$installer->getTable('sales/order_item')} ADD COLUMN is_backorder INT(1) DEFAULT 0;
");

$installer->run("
    ALTER TABLE {$installer->getTable('sales/order')} ADD is_backorder int(1) DEFAULT 0;
");

$installer->addAttribute('catalog_product', 'is_backorder', array(
    'type'       => 'int',
    'input'      => 'select',
    'label'      => 'Is Backorder',
    'sort_order' => 1000,
    'required'   => false,
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'source'     => 'eav/entity_attribute_source_boolean',
    'default'    => '0'

));

$installer->endSetup();
