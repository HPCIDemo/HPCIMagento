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

class Hostedpci_Hpci_Block_Customer_Address extends Mage_Directory_Block_Data
{
    /**
     * @var
     */
    protected $_address;
    /**
     * @var
     */
    protected $_countryCollection;
    /**
     * @var
     */
    protected $_regionCollection;

    /**
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_address = Mage::getModel('customer/address');

        // Init address object
        $customerBillingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();

        if ($customerBillingAddressId)
        {
            $this->_address->load($customerBillingAddressId);
            if ($this->_address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId())
            {
                $this->_address->setData(array());
            }
        }

        if (!$this->_address->getId())
        {
            $this->_address->setPrefix($this->getCustomer()->getPrefix())
                ->setFirstname($this->getCustomer()->getFirstname())
                ->setMiddlename($this->getCustomer()->getMiddlename())
                ->setLastname($this->getCustomer()->getLastname())
                ->setSuffix($this->getCustomer()->getSuffix());
        }

        if ($postedData = Mage::getSingleton('customer/session')->getAddressFormData(true))
        {
            $this->_address->addData($postedData);
        }
    }

    /**
     * Generate name block html
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock('customer/widget_name')
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * @return mixed|string
     */
    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = Mage::helper('customer')->__('Edit Address');
        }
        else {
            $title = Mage::helper('customer')->__('Add New Address');
        }
        return $title;
    }

    /**
     * @return mixed|string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('hpci/cards/index');
        } else {
            return $this->getUrl('hpci/cards/index');
        }
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return Mage::getUrl('hpci/cards/index');
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * @return mixed|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * @return mixed
     */
    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }

    /**
     * @return int
     */
    public function getCustomerAddressCount()
    {
        return count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
    }

    /**
     * @return bool|int
     */
    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    /**
     * @return bool|int
     */
    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();;
    }

    /**
     * @return bool
     */
    public function isDefaultBilling()
    {
        $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultBilling;
    }

    /**
     * @return bool
     */
    public function isDefaultShipping()
    {
        $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultShipping;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * @return string
     */
    public function getBackButtonUrl()
    {
        if ($this->getCustomerAddressCount()) {
            return $this->getUrl('hpci/cards/index');
        } else {
            return $this->getUrl('hpci/cards/index');
        }
    }
}
