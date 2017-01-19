<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_cards ADD COLUMN nickname VARCHAR(255) DEFAULT NULL;
");

$installer->endSetup();
