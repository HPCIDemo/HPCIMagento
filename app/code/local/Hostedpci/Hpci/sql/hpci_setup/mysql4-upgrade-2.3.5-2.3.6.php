<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_cards
ADD COLUMN is_default INT(1) DEFAULT 0;
");

$installer->run("
UPDATE hpci_cards SET is_default = 1;
");

$installer->endSetup();
