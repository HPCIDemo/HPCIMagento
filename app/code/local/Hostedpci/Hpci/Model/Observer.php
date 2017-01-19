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

class Hostedpci_Hpci_Model_Observer
{
    /**
     * @param $observer
     */
    public function convertOrderToQuote($observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        if($data = unserialize($order->getHpciPaymentData()))
        {
            $quote->getPayment()->importData(array(
                'method' => 'hpci_acc',
                'cc_exp_month' => $data['cc_exp_month'],
                'cc_exp_year' => $data['cc_exp_year'],
                'cc_number' => $data['cc_number'],
                'cc_cid' => $data['cc_cid'],
                'cc_type' => $data['cc_type'],
            ));
        }
    }

    /**
     * @param $observer
     */
    public function quoteToOrder($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();

        if((count($quote->getAllItems()) == 1) and current($quote->getAllItems())->getIsBackorder())
        {
            $order->setIsBackorder(1);
        }
    }

    /**
     *
     */
    public function salesOrderEditSavePreDispatch()
    {
        $payment = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getPayment();
        if($payment->getMethod() == 'hpci_acc')
        {
            Mage::app()->getFrontController()->getRequest()->setPost('payment', null);
        }
    }

    /**
     * @param $event
     */
    public function saveCustomerCard($event)
    {
        $customer = $event->getCustomer();
        $paymentData = $event->getRequest()->getParam('payment');

        // if we adding card
        if($paymentData and $paymentData['cc_type'])
        {
            $customerBillingAddressId = $customer->getDefaultBilling();

            // customer should have billing address
            if (!$customerBillingAddressId)
            {
                Mage::throwException('Please, add billing address before adding card');
            }

            $billingAddress = Mage::getModel('customer/address')->load($customerBillingAddressId);

            // prepare authorization data
            $payment = new Varien_Object(
                array_merge(array(
                    'order' => new Varien_Object(array(
                            'customer_email' => $customer->getEmail(),
                            'order_currency_code' => Mage::app()->getStore()->getBaseCurrencyCode(),
                            'billing_address' => $billingAddress,
                            'store' => new Varien_Object(array(
                                    'website' => Mage::app()->getWebsite()
                                )),
                            'real_order_id' => uniqid(),
                        ))
                ), $paymentData)
            );

            // authorize
            try
            {
                Mage::getModel('hpci/acc')->authorize($payment, 1);

                // add card if authorization is approved
                if($payment->getStatus() == 'APPROVED')
                {
                    $card = Mage::getModel('hpci/card')->load($customer->getId(), 'customer_id');
                    $card
                        ->addData($event->getRequest()->getParam('payment'))
                        ->setCustomerId($customer->getId())
                        ->save()
                    ;
                }
                else
                {
                    Mage::throwException('There was an error in validating this credit card.  Payment is not approved.  Please try again.');
                }

            }catch (Exception $e){
                Mage::throwException('There was an error in validating this credit card.  Exception in authorization.  Please try again.');
            }
        }
    }

    /**
     * Remove old transactions
     */
    public function transactionsCleanup()
    {
        $days = Mage::getStoreConfig('hpci/settings/transactions_lifetime');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->query("delete from hpci_transactions where TIMESTAMPDIFF(DAY, date_created, NOW()) >= ". ($days ? (int)$days : 60));
    }

    /**
     * @param $card
     */
    protected function _notifyCustomer($card)
    {
        /** @var Hostedpci_Hpci_Model_Card $card */
        $card = Mage::getModel('hpci/card')->load($card['id']);
        $customer = $card->getCustomer();

        // notify customer
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($customer->getEmail(), $customer->getName());
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(array(
            'name'  => Mage::getStoreConfig('hpci/settings/card_exp_sender_name', $store->getId()),
            'email' => Mage::getStoreConfig('hpci/settings/card_exp_sender_email', $store->getId()),
        ));
        $mailer->setTemplateId(Mage::getStoreConfig('hpci/settings/card_exp_template', $store->getId()));
        $mailer->setTemplateParams(array(
            'customer'  => $customer,
            'card'      => $card,
        ));

        $copyTo = Mage::getStoreConfig('hpci/settings/card_exp_bcc', $store->getId());
        $copyMethod = Mage::getStoreConfig('hpci/settings/card_exp_bcc_method', $store->getId());

        // BCC
        if ($copyTo && $copyMethod == 'bcc')
        {
            foreach(explode(',', $copyTo) as $bcc)
            {
                if(!trim($bcc)) continue;
                $emailInfo->addBcc(trim($bcc));
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach(explode(',', $copyTo) as $email)
            {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        $mailer->send();
    }

    /**
     * Notify customer about card expiring
     */
    public function checkCardExp()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $now        = Mage::getModel('core/date')->gmtDate();

        foreach(Mage::app()->getStores() as $store)
        {
            // return false if template is not set
            if(!Mage::getStoreConfig('hpci/settings/card_exp_enabled', $store->getId()))
            {
                continue;
            }
            // customer_notifications
            // 0 = not notified
            // 1 = month before
            // 2 = two weeks before
            // 3 = day after

            // get expiring credit cards - MONTH
            $cards = $connection->fetchAll("
                select * from hpci_cards where
                    CAST(CONCAT(cc_exp_year, '-', LPAD(cc_exp_month+1, 2, '0'), '-01') AS DATETIME) <= DATE_ADD('{$now}', interval 1 MONTH)
                    and customer_notifications = 0 and is_default = 1
                ;
            ");

            foreach($cards as $card)
            {
                $this->_notifyCustomer($card);

                // update card "customer_notifications"
                $connection->query("update hpci_cards set customer_notifications = 1 where id = {$card['id']}");
            }

            // get expiring credit cards - TWO WEEKS
            $cards = $connection->fetchAll("
                select * from hpci_cards where
                    CAST(CONCAT(cc_exp_year, '-', LPAD(cc_exp_month+1, 2, '0'), '-01') AS DATETIME) <= DATE_ADD('{$now}', interval 14 DAY)
                    and customer_notifications = 1 and is_default = 1
                ;
            ");

            foreach($cards as $card)
            {
                $this->_notifyCustomer($card);

                // update card "customer_notifications"
                $connection->query("update hpci_cards set customer_notifications = 2 where id = {$card['id']}");
            }

            // get expiring credit cards - MONTH
            $cards = $connection->fetchAll("
                select * from hpci_cards where
                    CAST(CONCAT(cc_exp_year, '-', LPAD(cc_exp_month+1, 2, '0'), '-01') AS DATETIME) < CAST('{$now}' AS DATETIME)
                    and customer_notifications = 2 and is_default = 1
                ;
            ");

            foreach($cards as $card)
            {
                $this->_notifyCustomer($card);

                // update card "customer_notifications"
                $connection->query("update hpci_cards set customer_notifications = 3 where id = {$card['id']}");
            }
        }
    }

    /**
     * Prevent deleting customer with stored CC
     *
     * @param $event
     */
    public function customerDeleteBefore($event)
    {
        $customer   = $event->getCustomer();
        $card       = Mage::getModel('hpci/card')->load($customer->getId(), 'customer_id');

        if($card->getId())
        {
            Mage::throwException(Mage::helper('hpci')->__('Unable to delete Customer with stored CC'));
        }
    }

    /**
     * Prevent deleting customer with stored CC
     *
     * @param $event
     */
    public function customerLoadAfter($event)
    {
        $customer   = $event->getCustomer();
        $card       = Mage::getModel('hpci/card')->load($customer->getId(), 'customer_id');

        if($card->getId())
        {
            $customer->setIsDeleteable(false);
        }
    }

    /**
     * @param $observer
     */
    public function copyIsBackorderAttribute($observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setIsBackorder($product->getIsBackorder());
    }

    /**
     * @param $observer
     * @throws Exception
     */
    public function salesOrderPaymentPlaceEnd($observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        if($payment->getMethod() == 'hpci_acc' and
            Mage::registry('hpci_fraud_order') and
            Mage::registry('hpci_fraud_order') == $order->getRealOrderId())
        {
            $order
                ->setState(Mage::registry('hpci_fraud_state'), 'hpci_fraud')
                ->save();

            Mage::unregister('hpci_fraud_order');
            Mage::unregister('hpci_fraud_state');
        }
    }

    /**
     * Set flag to save CC
     *
     * @param $observer
     * @return boolean
     */
    public function checkoutTypeOnepageSaveOrder($observer)
    {
        $payment = Mage::app()->getRequest()->getParam('payment');
        if(!isset($payment['method']) or $payment['method'] != 'hpci_acc')
        {
            return true;
        }

        if(isset($payment['save_cc']) and $payment['save_cc'] == 1)
        {
            Mage::register('hpci_save_cc', 1);
        }
        if(isset($payment['save_cc']) and $payment['save_cc'] == 1 and isset($payment['nickname']))
        {
            Mage::register('hpci_save_nickname', $payment['nickname']);
        }
        if(isset($payment['default_cc']) and $payment['default_cc'] == 1)
        {
            Mage::register('hpci_default_cc', 1);
        }
        return true;
    }

    /**
     * @param $observer
     * @return bool
     */
    public function checkoutTypeMultishippingSaveOrder($observer)
    {
        if(Mage::registry('hpci_card_saved'))
        {
            return true;
        }

        Mage::register('hpci_card_saved', 1);

        $payment = Mage::getSingleton('customer/session')->getData('hpci_multi_payment');
        Mage::app()->getRequest()->setParam('payment', $payment);

        $this->checkoutTypeOnepageSaveOrder($observer);
        return true;
    }

    /**
     * @param $observer
     * @return bool
     */
    public function savePaymentToSession($observer)
    {
        $payment = Mage::app()->getRequest()->getParam('payment');
        if(!isset($payment['method']) or $payment['method'] != 'hpci_acc')
        {
            return true;
        }

        Mage::getSingleton('customer/session')->setData('hpci_multi_payment', $payment);
        return true;
    }

    /**
     * @return bool
     */
    public function kountAdminOrderCreate()
    {
        $helper = Mage::helper('hpci/kount');
        if(!$helper->isEnabled())
        {
            return false;
        }

        $helper->generateSessionID();
    }
}
