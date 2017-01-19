<?php
class Hostedpci_Hpci_Model_Source_Kount_Environment
{

    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('TEST')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('PRODUCTION')),
        );
    }

}
