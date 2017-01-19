<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_cards
ADD COLUMN installments int(2) DEFAULT 0;
");

$installer->endSetup();
