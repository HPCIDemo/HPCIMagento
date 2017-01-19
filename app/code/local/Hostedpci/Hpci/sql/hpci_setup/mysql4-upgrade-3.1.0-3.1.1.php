<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE `hpci_fraud_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` datetime DEFAULT NULL,
  `order_increment_id` int(11) DEFAULT NULL,
  `fraud_system` enum('kount') DEFAULT NULL,
  `fraud_data` text,
  `fraud_method` enum('gateway','endpoint') DEFAULT NULL,
  `order_status_from` varchar(255) DEFAULT NULL,
  `order_status_to` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => 'hpci_order_review', 'label' => 'Order Review'),
    )
);

$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => 'hpci_pending_cancel', 'label' => 'Pending Cancel'),
    )
);

$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        array(
            'status'        => 'hpci_order_review',
            'state'         => 'processing',
            'is_default'    => 0
        )
    ),
    array(
        array(
            'status'        => 'hpci_order_review',
            'state'         => 'pending',
            'is_default'    => 0
        )
    )
);

$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        array(
            'status'        => 'hpci_pending_cancel',
            'state'         => 'processing',
            'is_default'    => 0
        )
    ),
    array(
        array(
            'status'        => 'hpci_pending_cancel',
            'state'         => 'pending',
            'is_default'    => 0
        )
    )
);

$installer->endSetup();
