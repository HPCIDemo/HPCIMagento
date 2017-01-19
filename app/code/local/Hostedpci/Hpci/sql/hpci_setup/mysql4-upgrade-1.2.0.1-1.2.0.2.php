<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE sales_flat_quote ADD COLUMN hpci_payment_data TEXT DEFAULT NULL;
");

$installer->run("
ALTER TABLE sales_flat_order ADD COLUMN hpci_payment_data TEXT DEFAULT NULL;
");

$installer->endSetup();
