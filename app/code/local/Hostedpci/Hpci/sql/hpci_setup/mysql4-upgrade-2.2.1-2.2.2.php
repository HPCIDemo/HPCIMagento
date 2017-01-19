<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => 'hpci_fraud', 'label' => 'HPCI Suspected Fraud'),
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
            'status'        => 'hpci_fraud',
            'state'         => 'processing',
            'is_default'    => 0
        )
    ),
    array(
        array(
            'status'        => 'hpci_fraud',
            'state'         => 'pending',
            'is_default'    => 0
        )
    )
);

$installer->endSetup();
