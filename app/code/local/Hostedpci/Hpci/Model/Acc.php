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
class Hostedpci_Hpci_Model_Acc extends Mage_Payment_Model_Method_Abstract
{
    /**
     * unique internal payment method identifier
     */
    protected $_code = 'hpci_acc';

    /**
     * @var string
     */
    protected $_formBlockType = 'hpci/form';
    /**
     * @var string
     */
    protected $_infoBlockType = 'hpci/info';

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    /**
     * @var bool
     */
    protected $_canAuthorize            = true;
    /**
     * @var bool
     */
    protected $_canCapture              = true;
    /**
     * @var bool
     */
    protected $_canCapturePartial       = false;
    /**
     * @var bool
     */
    protected $_canRefund               = true;
    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    /**
     * @var bool
     */
    protected $_canVoid                = true;
    /**
     * @var bool
     */
    protected $_canUseInternal         = true;
    /**
     * @var bool
     */
    protected $_canUseCheckout         = true;
    /**
     * @var bool
     */
    protected $_canUseForMultishipping = true;
    /**
     * @var bool
     */
    protected $_canSaveCc = true;

    /**
     * @var string
     */
    protected $_paymentMethod    = 'ACC';
    /**
     * @var string
     */
    protected $_defaultLocale    = 'en';
    /**
     * @var array
     */
    protected $_supportedLocales = array('cn', 'cz', 'da', 'en', 'es', 'fi', 'de', 'fr', 'gr', 'it', 'nl', 'ro', 'ru', 'pl', 'sv', 'tr');
    /**
     * @var string
     */
    protected $_hidelogin = '1';
    /**
     * @var
     */
    protected $_order;


    const PXY_AUTH		=	"/iSynSApp/paymentAuth.action";
    const PXY_SALE		=	"/iSynSApp/paymentSale.action";
    const PXY_CAPTURE	=	"/iSynSApp/paymentCapture.action";
    const PXY_CREDIT	=	"/iSynSApp/paymentCredit.action";
    const PXY_VOID		=	"/iSynSApp/paymentVoid.action";

    const PXYPARAM_APIVERSION		=	"apiVersion";
    const PXYPARAM_APITYPE			=	"apiType";
    const PXYPARAM_APITYPE_PXYHPCI	=	"pxyhpci";
    const PXYPARAM_USERNAME			=	"userName";
    const PXYPARAM_USERPASSKEY		=	"userPassKey";

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT       = 'CREDIT';
    const REQUEST_TYPE_VOID         = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    const RESPONSE_CODE_APPROVED = 'approved';
    const RESPONSE_CODE_DECLINED = 'declined';
    const RESPONSE_CODE_ERROR  =	'error';
    const RESPONSE_CODE_REVIEW	=	'review';

    const PXYPARAM_PXY_TRANSACTION_INSTALLMENT		=	"pxyTransaction.txnInstallmentCount";
    const PXYPARAM_PXY_TRANSACTION_CUSISO			=	"pxyTransaction.txnCurISO";
    const PXYPARAM_PXY_TRANSACTION_AMOUNT			=	"pxyTransaction.txnAmount";
    const PXYPARAM_PXY_TRANSACTION_MER_ACCOUNT_NAME	=	"pxyTransaction.merchantAccountName";
    const PXYPARAM_PXY_TRANSACTION_MER_PRODUCT_NAME	=	"pxyTransaction.merchantProductName";
    const PXYPARAM_PXY_TRANSACTION_MER_PHONE_NUM	=	"pxyTransaction.merchantPhoneNum";
    const PXYPARAM_PXY_TRANSACTION_MER_REFID_NAME	=	"pxyTransaction.merchantRefIdName";
    const PXYPARAM_PXY_TRANSACTION_MER_REFID		=	"pxyTransaction.merchantRefId";
    const PXYPARAM_PXY_TRANSACTION_PROCESSOR_REFID	=	"pxyTransaction.processorRefId";
    const PXYPARAM_PXY_TRANSACTION_FRAUD_CHECK	    =	"pxyTransaction.gtwyFraudCheck";
    const PXYPARAM_PXY_TRANSACTION_PAYMENT_PROFILE	=	"pxyTransaction.txnPayName";

    const PXYPARAM_PXY_CC_CARDTYPE	=	"pxyCreditCard.cardType";
    const PXYPARAM_PXY_CC_NUMBER	=	"pxyCreditCard.creditCardNumber";
    const PXYPARAM_PXY_CC_EXPMONTH	=	"pxyCreditCard.expirationMonth";
    const PXYPARAM_PXY_CC_EXPYEAR	=	"pxyCreditCard.expirationYear";
    const PXYPARAM_PXY_CC_CVV		=	"pxyCreditCard.cardCodeVerification";

    const PXYPARAM_PXY_CUSTINFO_CUSTOMERID	=	"pxyCustomerInfo.customerId";
    const PXYPARAM_PXY_CUSTINFO_EMAIL		=	"pxyCustomerInfo.email";
    const PXYPARAM_PXY_CUSTINFO_INSTR		=	"pxyCustomerInfo.instructions";
    const PXYPARAM_PXY_CUSTINFO_CUSTIP		=	"pxyCustomerInfo.customerIP";

    const PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME		=	"pxyCustomerInfo.billingLocation.firstName";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME		=	"pxyCustomerInfo.billingLocation.lastName";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_COMPANYNAME	=	"pxyCustomerInfo.billingLocation.companyName";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_ADDRESS		=	"pxyCustomerInfo.billingLocation.address";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_CITY			=	"pxyCustomerInfo.billingLocation.city";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_STATE			=	"pxyCustomerInfo.billingLocation.state";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_ZIPCODE		=	"pxyCustomerInfo.billingLocation.zipCode";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_COUNTRY		=	"pxyCustomerInfo.billingLocation.country";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_PHONENUMBER	=	"pxyCustomerInfo.billingLocation.phoneNumber";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_FAX			=	"pxyCustomerInfo.billingLocation.fax";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_TAX1			=	"pxyCustomerInfo.billingLocation.tax1";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_TAX2			=	"pxyCustomerInfo.billingLocation.tax2";
    const PXYPARAM_PXY_CUSTINFO_BILLADDR_TAX3			=	"pxyCustomerInfo.billingLocation.tax3";

    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_FIRSTNAME		=	"pxyCustomerInfo.shippingLocation.firstName";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_LASTNAME		=	"pxyCustomerInfo.shippingLocation.lastName";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_COMPANYNAME	=	"pxyCustomerInfo.shippingLocation.companyName";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_ADDRESS		=	"pxyCustomerInfo.shippingLocation.address";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_CITY			=	"pxyCustomerInfo.shippingLocation.city";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_STATE			=	"pxyCustomerInfo.shippingLocation.state";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_ZIPCODE		=	"pxyCustomerInfo.shippingLocation.zipCode";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_COUNTRY		=	"pxyCustomerInfo.shippingLocation.country";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_PHONENUMBER	=	"pxyCustomerInfo.shippingLocation.phoneNumber";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_FAX			=	"pxyCustomerInfo.shippingLocation.fax";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_TAX1			=	"pxyCustomerInfo.shippingLocation.tax1";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_TAX2			=	"pxyCustomerInfo.shippingLocation.tax2";
    const PXYPARAM_PXY_CUSTINFO_SHIPADDR_TAX3			=	"pxyCustomerInfo.shippingLocation.tax3";

    const PXYPARAM_PXY_ORDER_INVNUM			=	"pxyOrder.invoiceNumber";
    const PXYPARAM_PXY_ORDER_DESC			=	"pxyOrder.description";
    const PXYPARAM_PXY_ORDER_TOTALAMT		=	"pxyOrder.totalAmount";
    const PXYPARAM_PXY_ORDER_SHIPPINGAMT	=	"pxyOrder.shippingAmount";

    const PXYPARAM_PXY_ORDER_ORDERITEMS			=	"pxyOrder.orderItems[";
    const PXYPARAM_PXY_ORDER_ORDERITEM_ID		=	"].itemId";
    const PXYPARAM_PXY_ORDER_ORDERITEM_NAME		=	"].itemName";
    const PXYPARAM_PXY_ORDER_ORDERITEM_DESC		=	"].itemDescription";
    const PXYPARAM_PXY_ORDER_ORDERITEM_QTY		=	"].itemQuantity";
    const PXYPARAM_PXY_ORDER_ORDERITEM_PRICE	=	"].itemPrice";
    const PXYPARAM_PXY_ORDER_ORDERITEM_TAXABLE	=	"].itemTaxable";

    const PXYRESP_CALL_STATUS				=	"status";
    const PXYRESP_RESPONSE_STATUS			=	"pxyResponse_responseStatus";
    const PXYRESP_PROCESSOR_REFID			=	"pxyResponse_processorRefId";
    const PXYRESP_RESPSTATUS_NAME			=	"pxyResponse_responseStatus_name";
    const PXYRESP_RESPSTATUS_CODE			=	"pxyResponse_responseStatus_code";
    const PXYRESP_RESPSTATUS_DESCRIPTION	=	"pxyResponse_responseStatus_description";

    const PXYRESP_CALL_STATUS_SUCCESS	=	"success";
    const PXYRESP_CALL_STATUS_ERROR		=	"error";

    const PXYRESP_CALL_ERRID	=	"errId";
    const PXYRESP_CALL_ERRMSG	=	"errMsg";

    const PXYRESP_RESPONSE_STATUS_APPROVED	=	"approved";
    const PXYRESP_RESPONSE_STATUS_DECLINED	=	"declined";
    const PXYRESP_RESPONSE_STATUS_ERROR		=	"error";
    const PXYRESP_RESPONSE_STATUS_FRAUD		=	"fraud";
    const PXYRESP_RESPONSE_STATUS_REVIEW	=	"\n";
    const NL	=	"review";


    /**
     * Send a POST requst using cURL
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return string
     */
    protected function _callUrl($url, array $post = NULL, array $options = array())
    {

        $client = new Zend_Http_Client($url);
        $client->setParameterPost($post);
//        $client->setEncType(Zend_Http_Client::ENC_FORMDATA);

        $result = $client->request(Zend_Http_Client::POST)->getBody();

        Mage::log($client->getLastRequest(), null, 'hpci_request.log');

        // parse the url
        parse_str($result, $resultMap);

        // add additional info
        if(isset($resultMap['errId']))
        {
            $resultMap['errAdditionalMessage'] = $this->getErrMessage($resultMap['errId']);
        }

        // return the parsed map
        return $resultMap;
    }

    /**
     * Get error message by code
     *
     * @param $code
     * @return string
     */
    public function getErrMessage($code)
    {
        switch ($code)
        {
            case 'PPA_ACT_1':
                return 'User not logged-in, please check the user name and passkey are valid';

            case 'PPA_ACT_2':
                return 'Invalid version, please use the correct version';

            case 'PPA_ACT_3':
                return 'Too many mapping failures, contact HPCI admin';

            case 'PPA_ACT_4':
                return 'SSL Required for the request, please https to submit the request';

            case 'PPA_ACT_5':
                return 'Credit card mapping failure, please make sure only mapped credit card numbers are used for the request';

            case 'PPA_ACT_6':
                return 'Payment gateway not configured for the currency, please contact HPCI to setup the correct gateway details';

            case 'PPA_ACT_7':
                return 'Credit card operation failed, please check the gateway specific return codes.';

            case 'PPA_ACT_8':
                return 'Invalid amount, please check the amount sent.';

            case 'PPA_ACT_9':
                return 'Dependent credit card operation not found, please provide the correct reference number for the dependent transaction.';

            case 'PPA_ACT_10':
                return 'Unknown error, please contact HPCI.';

            case 'PPA_ACT_11':
                return 'Invalid operation, please verify the request.';

            case 'PPA_ACT_12':
                return 'Required parameter missing, please make sure missing parameter is provided.';

            case 'PPA_ACT_13':
                return 'CVV mapping failed, please verify the request.';

            case 'MCC_1':
                return 'The credit card number you entered is invalid.';

            case 'MCC_2':
                return 'The credit card code you entered is invalid.';

            case 'PGT_SER_9':
                return 'Gateway parameter missing. Please review additional response fields for details.';

            case 'PGT_SER_14':
                return 'Unknown Auth transaction.';

            case 'PGT_SER_15':
                return 'Unknown Sale transaction.';

            case 'PGT_SER_16':
                return 'Unknown Void transaction.';

            case 'PGT_SER_17':
                return 'Unknown Capture transaction.';

            case 'PGT_SER_18':
                return 'Unknown Credit transaction.';

            case 'PGT_SER_19':
                return 'Could not complete credit.';

            case 'PGT_SER_20':
                return 'Call timeout.';
        }
        return '';
    }

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order)
        {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }

    /**
     * Add customer credit card and authorize card if needed
     *
     * @param $payment
     * @param bool $authorize
     * @return $this
     */
    public function storeCustomerCc($payment, $authorize = false)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

        Mage::dispatchEvent('hpci_cc_save_before', array('payment' => $payment));

        // store customer card
        if(Mage::registry('hpci_save_cc') == 1 and $quote->getCheckoutMethod() != 'guest')
        {
            $collection = Mage::getModel('hpci/card')->getCollection();
            $collection->getSelect()
                ->where('customer_id = ?', $order->getCustomerId())
                ->where('cc_number = ?', $payment->getCcNumber());
            /** @var Hostedpci_Hpci_Model_Card $card */
            $card = $collection->getFirstItem();
            // add card and authorize it if needed
            if(!$card->getId())
            {
                // authorize card
                if($authorize)
                {
                    $this->authorize($payment, 1);
                }
                $card
                    ->setCustomerId($order->getCustomerId())
                    ->setCcNumber($payment->getCcNumber())
                    ->setCcType($payment->getCcType())
                    ->setCcCid($payment->getCcCid())
                    ->setCcExpYear($payment->getCcExpYear())
                    ->setCcExpMonth($payment->getCcExpMonth())
                    ->setDateLastUsed(Mage::getModel('core/date')->gmtDate())
                    ->setInstallments($payment->getInstallments())
                    ->setIsDefault(Mage::registry('hpci_default_cc') ? 1 : 0)
                    ->setNickname(Mage::registry('hpci_save_nickname'))
                    ->save();
            }
            // update last used date
            elseif($card->getId() and $card->getCcNumber() == $payment->getCcNumber())
            {
                $card
                    ->setDateLastUsed(Mage::getModel('core/date')->gmtDate())
                    ->save();
            }
        }
        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @param $paramMap
     * @param $method
     * @return mixed
     */
    public function AddOrderDetailsToHPCIMap(Varien_Object $payment, $amount, $paramMap, $method = false)
    {
        //get additional configuration variable: siteId
        $_configVars    = Mage::helper('hpci')->getConfigVars($payment->getOrder()->getStoreId());
        $siteId         = $_configVars['hpci_sid'];
        /** @var Mage_Sales_Model_Order $order */
        $order          = $payment->getOrder();
        $billing        = $order->getBillingAddress();
        $email          = $billing->getEmail() ? $billing->getEmail() : $order->getCustomerEmail();
        $custId         = $billing->getCustomerId();

        //format the amount, for pick processors (like Moneris)
        $amount         = number_format ($amount, 2, ".", "");

        /** @var Hostedpci_Hpci_Model_Card $card */
        $card           = Mage::getModel('hpci/card')->load($order->getCustomerId(), 'customer_id');

        // $paramMap[self::PXYPARAM_PXY_CC_CARDTYPE] = $payment->getCcType();
        $paramMap[self::PXYPARAM_PXY_CC_CARDTYPE]                   = '';
        $paramMap[self::PXYPARAM_PXY_CC_NUMBER]                     = $payment->getCcNumber();
        $paramMap[self::PXYPARAM_PXY_CC_EXPMONTH]                   = $payment->getCcExpMonth();
        $paramMap[self::PXYPARAM_PXY_CC_EXPYEAR]                    = $payment->getCcExpYear();
        $paramMap[self::PXYPARAM_PXY_CC_CVV]                        = $payment->getCcCid();
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_AMOUNT]            = $amount;
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_CUSISO]            = $order->getOrderCurrencyCode();
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_REFID]         = $siteId . ':' . $order->getRealOrderId();
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_ACCOUNT_NAME]  = $_configVars['account_name'];
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_PHONE_NUM]     = $_configVars['phone_num'];
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_PRODUCT_NAME]  = $_configVars['product_name'];
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_PAYMENT_PROFILE]   = $_configVars['payment_profile'];
        $paramMap[self::PXYPARAM_PXY_CUSTINFO_EMAIL]                = $email;
        $paramMap[self::PXYPARAM_PXY_CUSTINFO_CUSTOMERID]           = empty($custId) ? $email : $custId;

        if (!empty($billing))
        {
            // setup addresses
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME]   = $billing->getFirstname();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME]    = $billing->getLastname();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_ADDRESS]     = $billing->getStreet(1);
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_CITY]        = $billing->getCity();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_STATE]       = $billing->getRegion();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_ZIPCODE]     = $billing->getPostcode();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_COUNTRY]     = $billing->getCountry();
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping))
        {
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_FIRSTNAME] = $shipping->getFirstname();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_LASTNAME] = $shipping->getLastname();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_ADDRESS] = $shipping->getStreet(1);
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_CITY] = $shipping->getCity();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_STATE] = $shipping->getRegion();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_ZIPCODE] = $shipping->getPostcode();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_SHIPADDR_COUNTRY] = $shipping->getCountry();
        }

        $paramMap[self::PXYPARAM_PXY_CUSTINFO_CUSTIP]   = Mage::helper('hpci')->getClientIp();
        $paramMap[self::PXYPARAM_PXY_ORDER_INVNUM]      = $order->getRealOrderId();
        $paramMap[self::PXYPARAM_PXY_ORDER_DESC]        = $order->getStore()->getWebsite()->getName() . " Order: " . $order->getRealOrderId();
        $paramMap[self::PXYPARAM_PXY_ORDER_TOTALAMT]    = $amount;
        $paramMap[self::PXYPARAM_PXY_ORDER_SHIPPINGAMT] = $order->getShippingAmount();

        $items = $order->getAllVisibleItems();

        $idx = 0;
        if(!$items)
        {
            return $paramMap;
        }
        foreach ($items as $itemId => $item)
        {
            $paramMap[self::PXYPARAM_PXY_ORDER_ORDERITEMS . $idx . self::PXYPARAM_PXY_ORDER_ORDERITEM_ID]       = $item->getProductId();
            $paramMap[self::PXYPARAM_PXY_ORDER_ORDERITEMS . $idx . self::PXYPARAM_PXY_ORDER_ORDERITEM_NAME]     = $item->getProductId();
            $paramMap[self::PXYPARAM_PXY_ORDER_ORDERITEMS . $idx . self::PXYPARAM_PXY_ORDER_ORDERITEM_DESC]     = $item->getName();
            $paramMap[self::PXYPARAM_PXY_ORDER_ORDERITEMS . $idx . self::PXYPARAM_PXY_ORDER_ORDERITEM_QTY]      = $item->getQtyOrdered();
            $paramMap[self::PXYPARAM_PXY_ORDER_ORDERITEMS . $idx . self::PXYPARAM_PXY_ORDER_ORDERITEM_PRICE]    = $item->getPrice();
            $paramMap[self::PXYPARAM_PXY_ORDER_ORDERITEMS . $idx . self::PXYPARAM_PXY_ORDER_ORDERITEM_TAXABLE]  = 'N'; // Y/N

            //increment index for HPCI param array
            $idx = $idx + 1;
        }

        $paramsObj = new Varien_Object($paramMap);

        Mage::dispatchEvent('hpci_cc_data_prepare', array('params' => $paramsObj, 'payment' => $payment, 'method' => $method));

        return $paramsObj->getData();
    }

    /**
     * Send authorize request to gateway
     *
     * @param  Varien_Object $payment
     * @param  float $amount
     * @return Mage_Paygate_Model_Authorizenet
     * @throws Mage_Core_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $amount = number_format ($amount, 2, ".", "");
        $order = $payment->getOrder();
        $log = Mage::getModel('hpci/transaction');
        $log
            ->setDateCreated(Varien_Date::now())
            ->setOrderId(is_a($payment->getOrder(), 'Mage_Sales_Model_Order') ? $order->getRealOrderId() : null)
            ->setClientIp(Mage::helper('hpci')->getClientIp())
            ->setMethod('authorize');

        if ($amount <= 0)
        {
            $log
                ->setMessage('Invalid amount for authorization.')
                ->save();
            throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Invalid amount for authorization.'));
        }

        $_configVars    = Mage::helper('hpci')->getConfigVars($order->getStoreId());
        $passKey        = $_configVars['hpci_apipk'];
        $userName       = $_configVars['hpci_apiun'];
        $serviceUrl     = $_configVars['hpci_serviceurl'];

        // make the remote call
        $callUrl        = $serviceUrl . self::PXY_AUTH;

        // prepare the parameter array
        $paramMap                               = array();
        $paramMap[self::PXYPARAM_APIVERSION]    = "1.0.1";
        $paramMap[self::PXYPARAM_APITYPE]       = self::PXYPARAM_APITYPE_PXYHPCI;
        $paramMap[self::PXYPARAM_USERNAME]      = $userName;
        $paramMap[self::PXYPARAM_USERPASSKEY]   = $passKey;

        //fill parameter map with order details
        $paramMap   = $this->AddOrderDetailsToHPCIMap($payment, $amount, $paramMap, 'authorize');
        $resultMap  = $this->_callUrl($callUrl, $paramMap);
        if(isset($paramMap[self::PXYPARAM_PXY_TRANSACTION_INSTALLMENT]))
        {
            unset($paramMap[self::PXYPARAM_PXY_TRANSACTION_INSTALLMENT]);
        }

        if(Mage::registry('hpci_capture_paramMap'))
        {
            Mage::unregister('hpci_capture_paramMap');
        }

        Mage::register('hpci_capture_paramMap', $resultMap);

        // get the network call status
        $callStatus = $resultMap[self::PXYRESP_CALL_STATUS];

        $log
            ->setUrl($callUrl)
            ->setRequest(print_r($paramMap, true))
            ->setResponse(print_r($resultMap, true))
            ->setResponseStatus($callStatus)
            ->setLast4(substr($paramMap[self::PXYPARAM_PXY_CC_NUMBER], -4))
            ->setBillingName($paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME] . ' ' . $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME])
            ->save()
        ;

        // get the payment processing status
        $paymentStatus  = $resultMap[self::PXYRESP_RESPONSE_STATUS];
        $processorRefId = '';
        $statusCode     = '';

        if ($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == self::PXYRESP_RESPONSE_STATUS_APPROVED)
        {
            $processorRefId = $resultMap[self::PXYRESP_PROCESSOR_REFID];
        }
        elseif($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == 'success' && $resultMap[self::PXYRESP_RESPSTATUS_CODE] == 4)
        {
            $statusCode = 1;
            $paymentStatus  = self::PXYRESP_RESPONSE_STATUS_APPROVED;
            $processorRefId = $resultMap[self::PXYRESP_PROCESSOR_REFID];
            if(is_a($payment->getOrder(), 'Mage_Sales_Model_Order'))
            {
                Mage::register('hpci_fraud_order', $order->getRealOrderId());
                Mage::register('hpci_fraud_state', 'pending_payment');
            }
        }
        else
        {
            $statusCode = $resultMap[self::PXYRESP_RESPSTATUS_CODE];
        }

        if(Mage::getIsDeveloperMode())
        {
            $statusCode = 1;
            $paymentStatus = self::PXYRESP_RESPONSE_STATUS_APPROVED;
        }

        $payment
            ->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY)
            ->setAmount($amount)
            ->setCcApproval($statusCode)
            ->setLastTransId($processorRefId)
            ->setTransactionId($processorRefId)
            ->setIsTransactionClosed(0)
            ->setCcTransId($processorRefId)
        ;

        $callbackObject = new Varien_Object();
        Mage::dispatchEvent('hpci_authorize', array('payment' => $payment, 'payment_status' => $paymentStatus, 'callback_object' => $callbackObject, 'result_map' => $resultMap));

        switch ($paymentStatus)
        {
            case self::PXYRESP_RESPONSE_STATUS_APPROVED:
                $payment->setStatus(self::STATUS_APPROVED);
                break;
            case self::PXYRESP_RESPONSE_STATUS_DECLINED:
                $message = $this->getErrorMessage($resultMap);
                $log
                    ->setMessage('Payment authorization transaction has been declined. ' . $message)
                    ->save()
                ;
                throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Payment authorization transaction has been declined. ' . $message));
                break;
            default:
                $message = $this->getErrorMessage($resultMap);
                $log
                    ->setMessage('Payment authorization error. ' . $message)
                    ->save()
                ;
                throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Payment authorization error. ' . $message));
                break;
        }

        $this->storeCustomerCc($payment, false);

        Mage::dispatchEvent('hpci_authorize_after', array('payment' => $payment, 'payment_status' => $paymentStatus, 'result_map' => $resultMap));

        return $this;
    }

    /**
     * Void the payment through gateway
     *
     * @param Varien_Object $payment
     * @return $this
     * @throws Hostedpci_Hpci_Exception
     */
    public function void(Varien_Object $payment)
    {
        $log = Mage::getModel('hpci/transaction');
        $log
            ->setOrderId($payment->getOrder()->getRealOrderId())
            ->setDateCreated(Varien_Date::now())
            ->setMethod('void')
            ->setClientIp(Mage::helper('hpci')->getClientIp())
            ->save();

        if (!$payment->getCcTransId())
        {
            $log
                ->setMessage('Error in voiding the payment.')
                ->save()
            ;
            throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Error in voiding the payment.'));
        }

        /** @var Mage_Sales_Model_Order $order */
        $order          = $payment->getOrder();

        $_configVars    = Mage::helper('hpci')->getConfigVars($order->getStoreId());
        $passKey        = $_configVars['hpci_apipk'];
        $userName       = $_configVars['hpci_apiun'];
        $serviceUrl     = $_configVars['hpci_serviceurl'];
        $siteId         = $_configVars['hpci_sid'];
        // make the remote call to the url
        $callUrl        = $serviceUrl . self::PXY_VOID;

        // prepare the parameter array
        $paramMap                               = array();
        $paramMap[self::PXYPARAM_APIVERSION]    = "1.0.1";
        $paramMap[self::PXYPARAM_APITYPE]       = self::PXYPARAM_APITYPE_PXYHPCI;
        $paramMap[self::PXYPARAM_USERNAME]      = $userName;
        $paramMap[self::PXYPARAM_USERPASSKEY]   = $passKey;

        // format amount
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_AMOUNT]            = $order->getGrandTotal();
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_CUSISO]            = $order->getBaseCurrencyCode();
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_REFID]         = $siteId . ":" . $order->getRealOrderId();
        $paramMap[self::PXYPARAM_PXY_TRANSACTION_PROCESSOR_REFID]   = $payment->getCcTransId(); //= "V25A1D0BD606";

        $resultMap = $this->_callUrl($callUrl, $paramMap);

        // get the network call status
        $callStatus = $resultMap[self::PXYRESP_CALL_STATUS]; //status

        $log
            ->setUrl($callUrl)
            ->setRequest(print_r($paramMap, true))
            ->setResponse(print_r($resultMap, true))
            ->setResponseStatus($callStatus)
            ->setLast4(substr($paramMap[self::PXYPARAM_PXY_CC_NUMBER], -4))
            ->setBillingName($paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME] . ' ' . $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME])
            ->save()
        ;

        // get processing status
        $paymentStatus  = $resultMap[self::PXYRESP_RESPONSE_STATUS];

        $callbackObject = new Varien_Object();
        Mage::dispatchEvent('hpci_void', array('payment' => $payment, 'payment_status' => $paymentStatus, 'callback_object' => $callbackObject, 'result_map' => $resultMap));

        // if we need to stop processing
        if($callbackObject->getStop())
        {
            return $this;
        }
        elseif ($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == self::PXYRESP_RESPONSE_STATUS_APPROVED)
        {
            $payment->setStatus(self::STATUS_SUCCESS);
        }
        else
        {
            $log
                ->setMessage($this->_wrapGatewayError($resultMap))
                ->save()
            ;
            throw new Hostedpci_Hpci_Exception($this->_wrapGatewayError($resultMap));
        }

        return $this;
    }

    /**
     * Capture payment through Hpci api
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Hostedpci_Hpci_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $log = Mage::getModel('hpci/transaction');
        $log
            ->setOrderId($payment->getOrder()->getRealOrderId())
            ->setDateCreated(Varien_Date::now())
            ->setClientIp(Mage::helper('hpci')->getClientIp())
            ->setMethod('capture');

        /** @var Mage_Sales_Model_Order $order */
        $order          = $payment->getOrder();
        $_configVars    = Mage::helper('hpci')->getConfigVars($order->getStoreId());
        $passKey        = $_configVars['hpci_apipk'];
        $userName       = $_configVars['hpci_apiun'];
        $serviceUrl     = $_configVars['hpci_serviceurl'];
        $authOrSale     = Mage::getStoreConfig('payment/hpci_acc/payment_action');
        $siteId         = $_configVars['hpci_sid'];
        $amount         = number_format ($amount, 2, ".", "");

//        if(0 and !Mage::helper('hpci')->paymentAllowed())
        if(!Mage::helper('hpci')->paymentAllowed() and !Mage::getIsDeveloperMode())
        {
            $log
                ->setMessage('Payment disabled for 5 minutes.')
                ->setResponseStatus('error')
                ->save()
            ;
            throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Payment disabled for 5 minutes.'));
        }

        if(!empty($authOrSale) && $authOrSale == 'authorize_capture')
        {
            if ($amount <= 0)
            {
                // authorize customer card instead of capture
                return $this->storeCustomerCc($payment, true);
            }
            else
            {
                // update customer card last used date
                $this->storeCustomerCc($payment, false);
            }

            $log->setMethod('authorize_capture');

            $callUrl = $serviceUrl . self::PXY_SALE;

            $paramMap = array();
            $paramMap[self::PXYPARAM_APIVERSION] = "1.0.1";
            $paramMap[self::PXYPARAM_APITYPE] = self::PXYPARAM_APITYPE_PXYHPCI;
            $paramMap[self::PXYPARAM_USERNAME] = $userName;
            $paramMap[self::PXYPARAM_USERPASSKEY] = $passKey;

            //fill parameter map with order details
            $paramMap = $this->AddOrderDetailsToHPCIMap($payment, $amount, $paramMap, 'authorize_capture');

            //make the call the the HPCI URL
            $resultMap = $this->_callUrl($callUrl, $paramMap);

            if(Mage::registry('hpci_capture_paramMap'))
            {
                Mage::unregister('hpci_capture_paramMap');
            }

            Mage::register('hpci_capture_paramMap', $resultMap);

            // get the network call status
            $callStatus = $resultMap[self::PXYRESP_CALL_STATUS];

            $log
                ->setUrl($callUrl)
                ->setRequest(print_r($paramMap, true))
                ->setResponse(print_r($resultMap, true))
                ->setResponseStatus($callStatus)
                ->setLast4(substr($paramMap[self::PXYPARAM_PXY_CC_NUMBER], -4))
                ->setBillingName($paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME] . ' ' . $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME])
                ->save()
            ;

            // get the payment processing status
            $paymentStatus = $resultMap[self::PXYRESP_RESPONSE_STATUS];

            $processorRefId = '';
            $statusCode     = '';

            if ($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == self::PXYRESP_RESPONSE_STATUS_APPROVED)
            {
                $processorRefId = $resultMap[self::PXYRESP_PROCESSOR_REFID];
            }
            elseif($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == 'success' && $resultMap[self::PXYRESP_RESPSTATUS_CODE] == 4)
            {
                $statusCode = 1;
                $paymentStatus  = self::PXYRESP_RESPONSE_STATUS_APPROVED;
                $processorRefId = $resultMap[self::PXYRESP_PROCESSOR_REFID];
                if(is_a($payment->getOrder(), 'Mage_Sales_Model_Order'))
                {
                    Mage::register('hpci_fraud_order', $order->getRealOrderId());
                    Mage::register('hpci_fraud_state', 'processing');
                }
            }
            else
            {
                $statusCode = $resultMap[self::PXYRESP_RESPSTATUS_CODE];
            }

            if(Mage::getIsDeveloperMode())
            {
                $processorRefId = md5(microtime());
                $paymentStatus  = self::PXYRESP_RESPONSE_STATUS_APPROVED;
            }

            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);

            $payment
                ->setAmount($amount)
                ->setCcApproval($statusCode)
                ->setLastTransId($processorRefId)
                ->setTransactionId($processorRefId)
                ->setIsTransactionClosed(1)              //set transaction to closed
                ->setCcTransId($processorRefId)
            ;

            $callbackObject = new Varien_Object();
            Mage::dispatchEvent('hpci_authorize_capture', array('payment' => $payment, 'payment_status' => $paymentStatus, 'callback_object' => $callbackObject, 'result_map' => $resultMap));

            // if we need to stop processing
            if($callbackObject->getStop())
            {
                return $this;
            }

            switch ($paymentStatus)
            {
                case self::PXYRESP_RESPONSE_STATUS_APPROVED:
                    $payment->setStatus(self::STATUS_APPROVED);

                    $resultMap['cc_exp_month']  = $payment->getCcExpMonth();
                    $resultMap['cc_exp_year']   = $payment->getCcExpYear();
                    $resultMap['cc_number']     = $payment->getCcNumber();
                    $resultMap['cc_cid']        = $payment->getCcCid();
                    $resultMap['cc_type']       = $payment->getCcType();

                    $order
                        ->setHpciPaymentData(serialize($resultMap))
                        ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)
                        ->save()
                    ;

                    // store customer credit card
                    $this->storeCustomerCc($payment, false);
                    break;
                case self::PXYRESP_RESPONSE_STATUS_DECLINED:
                    $message = $this->getErrorMessage($resultMap);
                    $log
                        ->setMessage('Payment authorization transaction has been declined.' . $message)
                        ->save()
                    ;
                    throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Payment authorization transaction has been declined.' . $message));
                    break;
                default:
                    $message = $this->getErrorMessage($resultMap);
                    $log
                        ->setMessage('Payment authorization error. ' . $message)
                        ->save()
                    ;
                    throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Payment authorization error. ' . $message));
            }


            Mage::dispatchEvent('hpci_authorize_capture_after', array('payment' => $payment, 'payment_status' => $paymentStatus, 'result_map' => $resultMap));
            return $this;
        }
        else
        {
            $billing        = $order->getBillingAddress();
            $custId         = $billing->getCustomerId();
            $email          = $billing->getEmail() ? $billing->getEmail() : $order->getCustomerEmail();

            $callUrl        = $serviceUrl . self::PXY_CAPTURE;

            // get the auth to capture
            // prepare the parameter array
            $paramMap = array();
            $paramMap[self::PXYPARAM_APIVERSION]    = "1.0.1";
            $paramMap[self::PXYPARAM_APITYPE]       = self::PXYPARAM_APITYPE_PXYHPCI;
            $paramMap[self::PXYPARAM_USERNAME]      = $userName;
            $paramMap[self::PXYPARAM_USERPASSKEY]   = $passKey;

            // format amount
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_CUSTIP]               = Mage::helper('hpci')->getClientIp();
            $paramMap[self::PXYPARAM_PXY_ORDER_INVNUM]                  = $order->getRealOrderId();
            $paramMap[self::PXYPARAM_PXY_ORDER_DESC]                    = $order->getStore()->getWebsite()->getName() . " Order: " . $order->getRealOrderId();
            $paramMap[self::PXYPARAM_PXY_ORDER_TOTALAMT]                = $amount;
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_AMOUNT]            = $amount;
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_CUSISO]            = $order->getBaseCurrencyCode();
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_REFID]         = $siteId . ":" . $order->getRealOrderId();
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_EMAIL]                = $email;
            $paramMap[self::PXYPARAM_PXY_CUSTINFO_CUSTOMERID]           = empty($custId) ? $email : $custId;
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_PROCESSOR_REFID]   = rtrim($payment->getTransactionId(),'-capture');

            $resultMap = $this->_callUrl($callUrl, $paramMap);
            // get the network call status
            $callStatus = $resultMap[self::PXYRESP_CALL_STATUS];

            $log
                ->setUrl($callUrl)
                ->setRequest(print_r($paramMap, true))
                ->setResponse(print_r($resultMap, true))
                ->setResponseStatus($callStatus)
                ->setLast4(substr($paramMap[self::PXYPARAM_PXY_CC_NUMBER], -4))
                ->setBillingName($paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME] . ' ' . $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME])
                ->save()
            ;

            // get the payment processing status
            $paymentStatus  = $resultMap[self::PXYRESP_RESPONSE_STATUS];
            $processorRefId = '';

            if ($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == self::PXYRESP_RESPONSE_STATUS_APPROVED)
            {
                $processorRefId = $resultMap[self::PXYRESP_PROCESSOR_REFID];
            }

            $payment
                ->setStatus(self::STATUS_APPROVED)
                ->setTransactionId($processorRefId)
                ->setIsTransactionClosed(0)
            ;

            $callbackObject = new Varien_Object();
            Mage::dispatchEvent('hpci_capture', array('payment' => $payment, 'payment_status' => $paymentStatus, 'callback_object' => $callbackObject, 'result_map' => $resultMap));

            return $this;
        }

    }

    /**
     * Cancel payment
     *
     * @param Varien_Object $payment
     * @return $this
     */
    public function cancel(Varien_Object $payment)
    {
        $payment
            ->setStatus(self::STATUS_DECLINED)
            ->setTransactionId($this->getTransactionId())
            ->setIsTransactionClosed(1)
        ;

        return $this;
    }

    /**
     * Refund
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     * @throws Hostedpci_Hpci_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $amount = number_format ($amount, 2, ".", "");
        $log = Mage::getModel('hpci/transaction');
        $log
            ->setOrderId($payment->getOrder()->getRealOrderId())
            ->setDateCreated(Varien_Date::now())
            ->setClientIp(Mage::helper('hpci')->getClientIp())
            ->setMethod('refund');

        if ($payment->getRefundTransactionId() && $amount > 0)
        {
            /** @var Mage_Sales_Model_Order $order */
            $order          = $payment->getOrder();

            $_configVars    = Mage::helper('hpci')->getConfigVars($order->getStoreId());
            $passKey        = $_configVars['hpci_apipk'];
            $userName       = $_configVars['hpci_apiun'];
            $serviceUrl     = $_configVars['hpci_serviceurl'];
            $siteId         = $_configVars['hpci_sid'];
            // make the remote call to the url
            $callUrl        = $serviceUrl . self::PXY_CREDIT;

            // get the capture to credit
            // prepare the parameter array
            $paramMap                               = array();
            $paramMap[self::PXYPARAM_APIVERSION]    = "1.0.1";
            $paramMap[self::PXYPARAM_APITYPE]       = self::PXYPARAM_APITYPE_PXYHPCI;
            $paramMap[self::PXYPARAM_USERNAME]      = $userName;
            $paramMap[self::PXYPARAM_USERPASSKEY]   = $passKey;

            // format amount
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_AMOUNT]            = $amount;
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_CUSISO]            = $order->getBaseCurrencyCode();
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_MER_REFID]         = $siteId . ":" . $order->getRealOrderId();
            $paramMap[self::PXYPARAM_PXY_TRANSACTION_PROCESSOR_REFID]   = $payment->getRefundTransactionId(); //= "V25A1D0BD606";

            $resultMap = $this->_callUrl($callUrl, $paramMap);

            // get the network call status
            $callStatus = $resultMap[self::PXYRESP_CALL_STATUS]; //status

            $log
                ->setUrl($callUrl)
                ->setRequest(print_r($paramMap, true))
                ->setResponse(print_r($resultMap, true))
                ->setResponseStatus($callStatus)
                ->setLast4(substr($paramMap[self::PXYPARAM_PXY_CC_NUMBER], -4))
                ->setBillingName($paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_FIRSTNAME] . ' ' . $paramMap[self::PXYPARAM_PXY_CUSTINFO_BILLADDR_LASTNAME])
                ->save()
            ;

            // get the payment processing status
            $paymentStatus  = $resultMap[self::PXYRESP_RESPONSE_STATUS];

            $callbackObject = new Varien_Object();
            Mage::dispatchEvent('hpci_refund', array('payment' => $payment, 'payment_status' => $paymentStatus, 'callback_object' => $callbackObject, 'result_map' => $resultMap));

            // if we need to stop processing
            if($callbackObject->getStop())
            {
                return $this;
            }
            elseif ($callStatus == self::PXYRESP_CALL_STATUS_SUCCESS && $paymentStatus == self::PXYRESP_RESPONSE_STATUS_APPROVED)
            {
                $payment->setStatus(self::STATUS_SUCCESS);
                return $this;
            }
            else
            {
                $log
                    ->setMessage($this->_wrapGatewayError($resultMap))
                    ->save()
                ;
                throw new Hostedpci_Hpci_Exception($this->_wrapGatewayError($resultMap));
            }
        }
        $log
            ->setMessage('Error in refunding the payment.')
            ->save()
        ;
        throw new Hostedpci_Hpci_Exception(Mage::helper('paygate')->__('Error in refunding the payment.'));
    }

    /**
     * Get initialized flag status
     * @return true
     */
    public function isInitializeNeeded()
    {
        return false;
    }

    /**
     * Get config action to process initialization
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        $paymentAction = Mage::getStoreConfig('payment/hpci_acc/payment_action');
        return empty($paymentAction) ? true : $paymentAction;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return $this
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object))
        {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info
            ->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
        ;

        Mage::dispatchEvent('hpci_assign_data_after', array('payment_data' => $data, 'payment_info' => $info));

        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return $this
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc)
        {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }
        $info
            ->setCcNumber(null)
            ->setCcCid(null);
        return $this;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param   string $field
     * @param   string $storeId
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (is_null($storeId))
        {
            $storeId = $this->getStore();
        }
        return Mage::getStoreConfig('payment/hpci_acc/'.$field, $storeId);
    }

    /**
     * Gateway response wrapper
     *
     * @param array $resultMap
     * @return string
     */
    protected function _wrapGatewayError($resultMap)
    {
        return Mage::helper('hpci')->__(
            'Gateway error: %s (%s)',
            $resultMap[self::PXYRESP_RESPSTATUS_DESCRIPTION],
            $resultMap[self::PXYRESP_RESPSTATUS_CODE]
        );
    }

    /**
     * @param $resultMap
     * @return string
     */
    public function getErrorMessage($resultMap)
    {
        if(isset($resultMap['pxyResponse_fullNativeResp']))
        {
            $params = array();
            parse_str($resultMap['pxyResponse_fullNativeResp'], $params);

            if(isset($params['InternalResponseDescription']))
            {
                return $params['InternalResponseDescription'];
            }
            elseif(isset($params['txnResponse_responseText']))
            {
                return $params['txnResponse_responseText'];
            }
            elseif(isset($params['txnResponse.responseText']))
            {
                return $params['txnResponse.responseText'];
            }
        }
        elseif(isset($resultMap['errAdditionalMessage']))
        {
            return $resultMap['errAdditionalMessage'];
        }
        elseif(isset($resultMap[self::PXYRESP_RESPSTATUS_DESCRIPTION]))
        {
            return $resultMap[self::PXYRESP_RESPSTATUS_DESCRIPTION];
        }
        return '';
    }
}
