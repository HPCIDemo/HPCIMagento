<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_cards
ADD COLUMN date_last_used DATETIME DEFAULT NULL,
ADD COLUMN customer_notifications INT(2) DEFAULT 0;
");

$installer->endSetup();
