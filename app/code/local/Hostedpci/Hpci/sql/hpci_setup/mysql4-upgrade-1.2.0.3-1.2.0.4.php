<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE hpci_transactions (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  date_created datetime DEFAULT NULL,
  request text,
  response text,
  order_id int(11) DEFAULT NULL,
  response_status varchar(255) DEFAULT NULL,
  url varchar(255) DEFAULT NULL,
  method varchar(255) DEFAULT NULL,
  message varchar(2048) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();
