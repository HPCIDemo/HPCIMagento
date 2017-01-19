<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
delete from sales_order_status where status = 'hpci_fraud';
");

$installer->endSetup();
