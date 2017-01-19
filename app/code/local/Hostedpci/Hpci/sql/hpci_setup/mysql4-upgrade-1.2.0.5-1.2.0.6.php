<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_transactions ENGINE = MYISAM;
");

$installer->endSetup();
