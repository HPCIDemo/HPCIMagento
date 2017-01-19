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
class Hostedpci_Hpci_CardsController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     *
     */
    public function indexAction()
    {
        // Manage Cards should be allowed
        if(!Mage::getStoreConfig('hpci/settings/manage_cards'))
        {
            return $this->_redirect('');
        }

        $this->loadLayout();
        $session = $this->_getSession();
        $this->_initLayoutMessages('customer/session');

        // user should be logged in
        if(!$session->isLoggedIn())
        {
            return $this->_redirect('');
        }

        // save customer address
        if($this->getRequest()->isPost())
        {
            $this->saveAddress();

            if(!$this->getRequest()->getParam('payment'))
            {
                return $this->_redirect('*/*/*');
            }
        }

        // if we adding card
        if($this->getRequest()->getParam('payment'))
        {
            $eventObject = new Varien_Object(array('redirect_url' => '*/*/*'));
            $customerBillingAddressId = $session->getCustomer()->getDefaultBilling();

            $billingAddress = Mage::getModel('customer/address')->load($customerBillingAddressId);

            // prepare authorization data
            $payment = new Varien_Object(
                array_merge(array(
                    'order' => new Varien_Object(array(
                            'customer_email' => $session->getCustomer()->getEmail(),
                            'order_currency_code' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                            'billing_address' => $billingAddress,
                            'store' => new Varien_Object(array(
                                    'website' => Mage::app()->getWebsite()
                                )),
                            'real_order_id' => uniqid(),
                        ))
                ), $this->getRequest()->getParam('payment'))
            );

            // authorize
            try
            {
                Mage::getModel('hpci/acc')->authorize($payment, 1);

                // add card if authorization is approved
                if($payment->getStatus() == 'APPROVED')
                {
                    $card = Mage::getModel('hpci/card');
                    $card
                        ->addData($this->getRequest()->getParam('payment'))
                        ->setCustomerId($session->getCustomerId())
                        ->setCustomerNotifications(0) // reset customer notifications
                        ->save()
                    ;

                    // allow to change url in event
                    Mage::dispatchEvent('hpci_card_added', array('object' => $eventObject));
                    $session->addSuccess('Account information updated');
                }
                else{
                    $session->addError('There was an error in validating this credit card. Payment is not approved.  Please try again.');
                }

            }catch (Exception $e){
                $session->addError('There was an error in validating this credit card. Exception in authorization. Please try again.');
                Mage::log($e,null,'hpci_exceptions.log');
            }

            return $this->_redirect($eventObject->getRedirectUrl());
        }

        $this->renderLayout();
    }

    /**
     * @return bool
     */
    public function saveAddress()
    {
        if(!$this->getRequest()->getParam('firstname') or !$this->getRequest()->getParam('street'))
        {
            return false;
        }
        $customer = $this->_getSession()->getCustomer();
        /* @var $address Mage_Customer_Model_Address */
        $address  = Mage::getModel('customer/address');
        $addressId = $this->getRequest()->getParam('id');
        if ($addressId) {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
            }
        }

        $errors = array();

        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntity($address);
        $addressData    = $addressForm->extractData($this->getRequest());
        $addressErrors  = $addressForm->validateData($addressData);
        if ($addressErrors !== true) {
            $errors = $addressErrors;
        }

        try {
            $addressForm->compactData($addressData);
            $address->setCustomerId($customer->getId())
                ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

            $addressErrors = $address->validate();
            if ($addressErrors !== true) {
                $errors = array_merge($errors, $addressErrors);
            }

            if (count($errors) === 0) {
                $address->save();
                $this->_getSession()->addSuccess($this->__('The address has been saved.'));
                return;
            } else {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                foreach ($errors as $errorMessage) {
                    $this->_getSession()->addError($errorMessage);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                ->addException($e, $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                ->addException($e, $this->__('Cannot save address.'));
        }
    }

    /**
     *
     */
    public function deleteAction()
    {
        // Manage Cards should be allowed
        if (!Mage::getStoreConfig('hpci/settings/manage_cards'))
        {
            return $this->_redirect('');
        }

        $session = $this->_getSession();

        // user should be logged in
        if (!$session->isLoggedIn()) {
            return $this->_redirect('');
        }

        $card = Mage::getModel('hpci/card')->load($this->getRequest()->getParam('id'));
        if(!$card->getId())
        {
            return $this->_redirect('');
        }
        if($card->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId())
        {
            return $this->_redirect('');
        }

        if ($card->getIsDefault() == 1 and !Mage::getStoreConfig('hpci/settings/delete_default_card'))
        {
            return $this->_redirect('');
        }
        $card->delete();

        $session->addSuccess($this->__('Card Deleted'));

        return $this->_redirect('hpci/cards');
    }

    /**
     *
     */
    public function defaultAction()
    {
        // Manage Cards should be allowed
        if (!Mage::getStoreConfig('hpci/settings/manage_cards'))
        {
            return $this->_redirect('');
        }

        $session = $this->_getSession();

        // user should be logged in
        if (!$session->isLoggedIn()) {
            return $this->_redirect('');
        }

        $card = Mage::getModel('hpci/card')->load($this->getRequest()->getParam('id'));
        if(!$card->getId())
        {
            return $this->_redirect('');
        }
        if($card->getCustomerId() != $session->getCustomerId())
        {
            return $this->_redirect('');
        }

        $cards = $card->getCustomerCards($card->getCustomerId());
        foreach($cards as $_card)
        {
            $_card
                ->setIsDefault(0)
                ->save()
            ;
        }

        $card
            ->setIsDefault(1)
            ->save()
        ;

        $session->addSuccess($this->__('Default card saved'));

        return $this->_redirect('hpci/cards');
    }
}
