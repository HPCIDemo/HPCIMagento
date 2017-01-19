<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_transactions
ADD COLUMN client_ip VARCHAR(15) DEFAULT NULL;
");

$installer->endSetup();
