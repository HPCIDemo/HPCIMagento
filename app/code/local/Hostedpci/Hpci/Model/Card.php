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

/**
 * Class Hostedpci_Hpci_Model_Card
 *
 * @method Hostedpci_Hpci_Model_Card setCustomerId()
 * @method Hostedpci_Hpci_Model_Card setCcNumber()
 * @method Hostedpci_Hpci_Model_Card setCcType()
 * @method Hostedpci_Hpci_Model_Card setCcCid()
 * @method Hostedpci_Hpci_Model_Card setCcExpYear()
 * @method Hostedpci_Hpci_Model_Card setCcExpMonth()
 * @method Hostedpci_Hpci_Model_Card setDateLastUsed()
 * @method Hostedpci_Hpci_Model_Card setIsDefault()
 */
class Hostedpci_Hpci_Model_Card extends Mage_Core_Model_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('hpci/card');
    }

    /**
     * @return array
     */
    public function getCcTypes()
    {
        $availableTypes = Mage::getStoreConfig('payment/hpci_acc/cctypes');
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        if ($availableTypes)
        {
            $availableTypes = explode(',', $availableTypes);
            foreach ($types as $code=>$name)
            {
                if (!in_array($code, $availableTypes))
                {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    /**
     * @return mixed
     */
    public function getCcTypeName()
    {
        $types = $this->getCcTypes();
        return isset($types[$this->getCcType()]) ? $types[$this->getCcType()] : $this->getCcType();
    }

    /**
     * @return mixed|string
     */
    public function getCcNumberLast4()
    {
        $ccNumber = $this->getData('cc_number');
        return 'xxxx-' . substr($ccNumber, strlen($ccNumber)-4);
    }

    /**
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getCustomer()
    {
        if(!$this->getData('customer') and $this->getCustomerId())
        {
            $this->setData('customer',
                Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getWebsite()->getId())
                    ->load($this->getCustomerId())
            );
        }

        return $this->getData('customer');
    }

    /**
     * @param int $default
     */
    public function setAsDefault($default = 1)
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->where('customer_id = ?', $this->getCustomerId());

        //
    }

    /**
     * @param $customerId
     * @return Hostedpci_Hpci_Model_Resource_Card_Collection
     */
    public function getCustomerCards($customerId)
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->where('customer_id = ?', $customerId);

        return $collection;
    }

    /**
     * @param $customerId
     * @return Varien_Object
     */
    public function getDefaultCard($customerId)
    {
        return $this->getCustomerCards($customerId)->getItemByColumnValue('is_default', 1);
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if(!$this->getDefaultCard($this->getCustomerId()))
        {
            $this->setIsDefault(1);
        }

        return parent::_beforeSave();
    }

    public function afterCommitCallback()
    {
        if($this->getIsDefault())
        {
            $collection = $this->getCollection();
            $collection->getSelect()
                ->where('customer_id = ?', $this->getCustomerId())
                ->where('id <> ?', $this->getId())
                ->where('is_default = 1')
            ;

            foreach($collection as $item)
            {
                $item
                    ->setIsDefault(0)
                    ->save()
                ;
            }
        }

        return parent::afterCommitCallback();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterDeleteCommit()
    {
        $collection = $this->getCustomerCards($this->getCustomerId());
        if(!$this->getDefaultCard($this->getCustomerId()) and count($collection) > 0)
        {
            $collection->getFirstItem()
                ->setIsDefault(1)
                ->save();
        }

        return parent::_afterDeleteCommit();
    }
}
