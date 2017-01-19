<?php
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE hpci_transactions
ADD COLUMN last4 VARCHAR(4) DEFAULT NULL,
ADD COLUMN billing_name VARCHAR(255) DEFAULT NULL;
");

$collection = Mage::getModel('hpci/transaction')->getCollection();
foreach($collection as $item)
{
    $last4 = null;
    $first_name = null;
    $last_name = null;

    preg_match('!creditCardNumber\] \=\> (\d+)!', $item->getRequest(), $last4);
    if(isset($last4[1]))
    {
        $last4 = substr($last4[1], -4);
    }
    else
    {
        $last4 = null;
    }

    preg_match('!billingLocation\.firstName\] \=\> ([\w ]+)!', $item->getRequest(), $first_name);
    if(isset($first_name[1]) and $first_name[1])
    {
        $first_name = $first_name[1];
    }
    else
    {
        $first_name = null;
    }

    preg_match('!billingLocation\.lastName\] \=\> ([\w ]+)!', $item->getRequest(), $last_name);
    if(isset($last_name[1]) and $last_name[1])
    {
        $last_name = $last_name[1];
    }
    else
    {
        $last_name = null;
    }

    $item
        ->setData('last4', $last4)
        ->setData('billing_name', ($first_name ? ($first_name . ' ' ) : '') . $last_name)
        ->save();
}

$installer->endSetup();
