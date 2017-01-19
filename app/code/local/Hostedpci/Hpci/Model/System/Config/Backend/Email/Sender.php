<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Hostedpci
 * @package     Hostedpci_Hpci
 * @author      Adam Chetverkin (adam.chetverkin@snowcommerce.com)
 * @copyright   Copyright (c) 2016 Snow Commerce (http://snowcommerce.com)
 * @license     @ Snow Commerce
 */
class Hostedpci_Hpci_Model_System_Config_Backend_Email_Sender extends Mage_Core_Model_Config_Data
{
    /**
     * Check sender name validity
     *
     * @return Hostedpci_Hpci_Model_System_Config_Backend_Email_Sender
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if(empty($value))
        {
            return $this;
        }

        if (!preg_match("/^[\S ]+$/", $value)) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid sender name "%s". Please use only visible characters and spaces.', $value));
        }

        if (strlen($value) > 255) {
            Mage::throwException(Mage::helper('adminhtml')->__('Maximum sender name length is 255. Please correct your settings.'));
        }
        return $this;
    }
}
