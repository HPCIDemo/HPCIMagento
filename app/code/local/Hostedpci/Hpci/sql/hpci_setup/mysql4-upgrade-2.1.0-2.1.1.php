<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE sales_flat_order_grid ADD COLUMN is_backorder INT(1) DEFAULT 0;
");

$installer->run("
update sales_flat_order_grid sfog set sfog.is_backorder = (select sfo.is_backorder from sales_flat_order sfo where sfo.entity_id = sfog.entity_id);
");


$installer->endSetup();
