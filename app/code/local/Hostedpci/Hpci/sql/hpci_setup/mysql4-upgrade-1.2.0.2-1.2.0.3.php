<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE hpci_cards (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  customer_id int(11) DEFAULT NULL,
  cc_type varchar(10) DEFAULT NULL,
  cc_number varchar(20) DEFAULT NULL,
  cc_cid int(11) DEFAULT NULL,
  cc_exp_month int(2) DEFAULT NULL,
  cc_exp_year int(4) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->endSetup();
