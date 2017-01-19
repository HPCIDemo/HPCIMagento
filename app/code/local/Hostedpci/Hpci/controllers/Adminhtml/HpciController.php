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
class Hostedpci_Hpci_Adminhtml_HpciController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('snowcommerce/hpci');
    }

    /**
     * Retrieve Hpci helper
     *
     * @return Hostedpci_Hpci_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('hpci');
    }

    /**
     * Transactions grid
     */
    public function transactionsAction()
    {
        if($this->getRequest()->isAjax())
        {
            $html = $this->getLayout()->createBlock('hpci/adminhtml_transactions_grid')->toHtml();
            /* @var $translate Mage_Core_Model_Translate_Inline */
            $translate = Mage::getModel('core/translate_inline');
            if ($translate->isAllowed())
            {
                $translate->processResponseBody($html);
            }
            $this->getResponse()->setBody($html);
        }
        else
        {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    /**
     * View transaction
     */
    public function transactionAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Send Support Request
     */
    public function supportAction()
    {
        if(!$this->getRequest()->getParam('id'))
        {
            return $this->_redirect('');
        }
        $transaction = Mage::getModel('hpci/transaction')->load($this->getRequest()->getParam('id'));
        if(!$transaction->getId())
        {
            return $this->_redirect('');
        }

        $mail = Mage::getModel('core/email')
            ->setToName('Support')
            ->setToEmail('mr.awers@gmail.com')
            ->setBody(sprintf("Request from site: %s\n\n Request text:\n\n%s\n\n Response text:\n\n %s", Mage::getBaseUrl(), $transaction->getRequest(), $transaction->getResponse()))
            ->setSubject('Support Request from '.Mage::getBaseUrl())
            ->setFromEmail(Mage::getStoreConfig('trans_email/ident_support/name'))
            ->setFromName(Mage::getStoreConfig('trans_email/ident_support/email'))
            ->setType('text');

        try{
            $mail->send();

            $session = $this->_getSession();
            $session->addSuccess($this->__('Support Request has been sent'));
        }
        catch(Exception $error)
        {
        }

        return $this->_redirect('*/*/transaction', array('transaction_id' => $transaction->getId()));
    }

    /**
     *
     */
    public function deleteCardAction()
    {
        $session = $this->_getSession();

        $card = Mage::getModel('hpci/card')->load($this->getRequest()->get('id'));
        $customerId = $card->getCustomerId();
        if(!$card->getId())
        {
            return $this->_redirect('');
        }
        $card->delete();

        $session->addSuccess($this->__('Card Deleted'));

        return $this->_redirect('adminhtml/customer/edit', array('id' => $customerId));
    }

    /**
     *
     */
    public function customercardsAction()
    {
        $this->loadLayout();
        $customer = Mage::getModel('customer/customer')
            ->load($this->getRequest()->getParam('customer_id'));
        Mage::register('current_customer', $customer);
        $this->renderLayout();
    }
}
