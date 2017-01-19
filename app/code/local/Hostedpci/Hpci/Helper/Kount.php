<?php

/**
 * Class Hostedpci_Hpci_Helper_Kount
 */
class Hostedpci_Hpci_Helper_Kount
{
    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('payment/hpci_acc/antifraud/kount_enabled');
    }

    /**
     * Need to call that method to generate session ID for IFrame
     *
     * @return string
     */
    public function generateSessionID()
    {
        $sessionModel = Mage::app()->getStore()->isAdmin() ? 'admin/session' : 'customer/session';
        $session = Mage::getSingleton($sessionModel);
        $sessionID = md5($session->getSessionId() . '-' . microtime(true));
        $session->setData('hpci_kount_session', $sessionID);

        return $sessionID;
    }

    /**
     * @return mixed
     */
    public function getSessionID()
    {
        if(Mage::helper('hpci')->getClientIp() == 'UNKNOWN')
        {
            return md5(microtime(true));
        }
        $sessionModel = Mage::app()->getStore()->isAdmin() ? 'admin/session' : 'customer/session';
        return Mage::getSingleton($sessionModel)->getData('hpci_kount_session');
    }

    /**
     * @param $observer
     * @return $this|bool
     */
    public function addRequestData($observer)
    {
        if (!$this->isEnabled())
        {
            return false;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getPayment()->getOrder();

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();

        $observer->getParams()
            ->addData(array(
                'fraudChkParamList[0].name' => 'AUTH',
                'fraudChkParamList[0].value' => 'A',
                'fraudChkParamList[1].name' => 'CURR',
                'fraudChkParamList[1].value' => $order->getStoreCurrencyCode(),
                'fraudChkParamList[2].name' => 'EMAL',
                'fraudChkParamList[2].value' => $order->getCustomerEmail(),
                'fraudChkParamList[3].name' => 'IPAD',
                'fraudChkParamList[3].value' => $order->getRemoteIp() ? $order->getRemoteIp() : '10.0.0.1',
                'fraudChkParamList[4].name' => 'MACK',
                'fraudChkParamList[4].value' => 'Y',
                'fraudChkParamList[5].name' => 'MERC',
                'fraudChkParamList[5].value' => Mage::getStoreConfig('payment/hpci_acc/antifraud/kount_merchantnum'),
                'fraudChkParamList[6].name' => 'MODE',
                'fraudChkParamList[6].value' => $order->getRemoteIp() ? 'Q' : 'P',
            ));

        $i = 1;
        $p = 6;
        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllItems() as $item)
        {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->setStoreId($order->getStoreId())->load($item->getProductId());
            $attributeSetModel = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());
            $observer->getParams()
                ->addData(array(
                    'fraudChkParamList['.++$p.'].groupIdx' => $i,
                    'fraudChkParamList['.$p.'].name' => 'PROD_DESC',
                    'fraudChkParamList['.$p.'].value' => $product->getName(),
                    'fraudChkParamList['.++$p.'].groupIdx' => $i,
                    'fraudChkParamList['.$p.'].name' => 'PROD_ITEM',
                    'fraudChkParamList['.$p.'].value' => $attributeSetModel->getAttributeSetName(),
                    'fraudChkParamList['.++$p.'].groupIdx' => $i,
                    'fraudChkParamList['.$p.'].name' => 'PROD_PRICE',
                    'fraudChkParamList['.$p.'].value' => number_format($item->getPrice(), 2, '', ''),
                    'fraudChkParamList['.++$p.'].groupIdx' => $i,
                    'fraudChkParamList['.$p.'].name' => 'PROD_QUANT',
                    'fraudChkParamList['.$p.'].value' => $item->getQtyOrdered(),
                    'fraudChkParamList['.++$p.'].groupIdx' => $i,
                    'fraudChkParamList['.$p.'].name' => 'PROD_TYPE',
                    'fraudChkParamList['.$p.'].value' => $item->getSku(),
                ));
            //
            $i++;
        }

        $shippingMethod = 'ST';
        switch($order->getShippingMethod())
        {
            case 'amtable_amtable1': $shippingMethod = 'ST'; break;
            case 'amtable_amtable2': $shippingMethod = '2D'; break;
            case 'amtable_amtable3': $shippingMethod = 'ND'; break;
            //Canada
            case 'deviantpurolator_PurolatorGround': $shippingMethod = 'ST'; break;
            case 'deviantpurolator_PurolatorExpress': $shippingMethod = '2D'; break;
        }

        $observer->getParams()
            ->addData(array(
                'fraudChkParamList['.++$p.'].name' => 'PTYP',
                'fraudChkParamList['.$p.'].value' => 'CARD',
                'fraudChkParamList['.++$p.'].name' => 'SESS',
                'fraudChkParamList['.$p.'].value' => $this->getSessionID(),
                'fraudChkParamList['.++$p.'].name' => 'SITE',
                'fraudChkParamList['.$p.'].value' => Mage::getStoreConfig('payment/hpci_acc/antifraud/kount_website'),
                'fraudChkParamList['.++$p.'].name' => 'TOTL',
                'fraudChkParamList['.$p.'].value' => number_format($order->getGrandTotal(), 2, '', ''),
                'fraudChkParamList['.++$p.'].name' => 'VERS',
                'fraudChkParamList['.$p.'].value' => '0630',

                'fraudChkParamList['.++$p.'].name' => 'AVST',
                'fraudChkParamList['.$p.'].type' => 'map',
                'fraudChkParamList['.$p.'].mappedValue' => 'M',
                'fraudChkParamList['.$p.'].value' => 'Y',
                'fraudChkParamList['.++$p.'].name' => 'AVSZ',
                'fraudChkParamList['.$p.'].type' => 'map',
                'fraudChkParamList['.$p.'].mappedValue' => 'M',
                'fraudChkParamList['.$p.'].value' => 'Y',
                'fraudChkParamList['.++$p.'].name' => 'B2A1',
                'fraudChkParamList['.$p.'].value' => $billing->getStreet1(),
                'fraudChkParamList['.++$p.'].name' => 'B2A2',
                'fraudChkParamList['.$p.'].value' => $billing->getStreet2(),
                'fraudChkParamList['.++$p.'].name' => 'B2CC',
                'fraudChkParamList['.$p.'].value' => $billing->getCountryId(),
                'fraudChkParamList['.++$p.'].name' => 'B2CI',
                'fraudChkParamList['.$p.'].value' => $billing->getCity(),
                'fraudChkParamList['.++$p.'].name' => 'B2PC',
                'fraudChkParamList['.$p.'].value' => $billing->getPostcode(),
                'fraudChkParamList['.++$p.'].name' => 'B2PN',
                'fraudChkParamList['.$p.'].value' => $billing->getTelephone() ? $billing->getTelephone() : '0123456789',
                'fraudChkParamList['.++$p.'].name' => 'B2ST',
                'fraudChkParamList['.$p.'].value' => $billing->getRegionCode(),
                'fraudChkParamList['.++$p.'].name' => 'CASH',
                'fraudChkParamList['.$p.'].value' => number_format($order->getGrandTotal(), 2, '', ''),
                'fraudChkParamList['.++$p.'].name' => 'CVVR',
                'fraudChkParamList['.$p.'].type' => 'map',
                'fraudChkParamList['.$p.'].mappedValue' => 'M',
                'fraudChkParamList['.$p.'].value' => 'Y',
                'fraudChkParamList['.++$p.'].name' => 'DOB',
                'fraudChkParamList['.$p.'].value' => $order->getCustomerDob(),
                'fraudChkParamList['.++$p.'].name' => 'NAME',
                'fraudChkParamList['.$p.'].value' => $order->getCustomerName(),
                'fraudChkParamList['.++$p.'].name' => 'ORDR',
                'fraudChkParamList['.$p.'].value' => $order->getIncrementId(),
                'fraudChkParamList['.++$p.'].name' => 'S2A1',
                'fraudChkParamList['.$p.'].value' => $shipping->getStreet1(),
                'fraudChkParamList['.++$p.'].name' => 'S2A2',
                'fraudChkParamList['.$p.'].value' => $shipping->getStreet2(),
                'fraudChkParamList['.++$p.'].name' => 'S2CC',
                'fraudChkParamList['.$p.'].value' => $shipping->getCountryId(),
                'fraudChkParamList['.++$p.'].name' => 'S2CI',
                'fraudChkParamList['.$p.'].value' => $shipping->getCity(),
                'fraudChkParamList['.++$p.'].name' => 'S2EM',
                'fraudChkParamList['.$p.'].value' => $shipping->getEmail(),
                'fraudChkParamList['.++$p.'].name' => 'S2NM',
                'fraudChkParamList['.$p.'].value' => $shipping->getName(),
                'fraudChkParamList['.++$p.'].name' => 'S2PC',
                'fraudChkParamList['.$p.'].value' => $shipping->getPostcode(),
                'fraudChkParamList['.++$p.'].name' => 'S2PN',
                'fraudChkParamList['.$p.'].value' => $shipping->getTelephone() ? $shipping->getTelephone() : '0123456789',
                'fraudChkParamList['.++$p.'].name' => 'S2ST',
                'fraudChkParamList['.$p.'].value' => $shipping->getRegionCode(),
                'fraudChkParamList['.++$p.'].name' => 'SHTP',
                'fraudChkParamList['.$p.'].value' => $shippingMethod,

                'fraudChkParamList['.++$p.'].name' => 'UNIQ',
                'fraudChkParamList['.$p.'].value' => $order->getCustomerId(),

                // UDF Fields
                'fraudChkParamList['.++$p.'].groupIdx' => 1,
                'fraudChkParamList['.$p.'].name' => 'UDFN',
                'fraudChkParamList['.$p.'].value' => 'PAYFLOW_ID',
                'fraudChkParamList['.++$p.'].groupIdx' => 1,
                'fraudChkParamList['.$p.'].name' => 'UDFV',
                'fraudChkParamList['.$p.'].value' => 'hpcirespval-pxyResponse.processorRefId',
                'fraudChkParamList['.++$p.'].groupIdx' => 2,
                'fraudChkParamList['.$p.'].name' => 'UDFN',
                'fraudChkParamList['.$p.'].value' => 'AUCT',
                'fraudChkParamList['.++$p.'].groupIdx' => 2,
                'fraudChkParamList['.$p.'].name' => 'UDFV',
                'fraudChkParamList['.$p.'].value' => isset($item) && $item->getAuctionId() ? 'Y' : 'N',
                'fraudChkParamList['.++$p.'].groupIdx' => 3,
                'fraudChkParamList['.$p.'].name' => 'UDFN',
                'fraudChkParamList['.$p.'].value' => 'COMPANY_BILL',
                'fraudChkParamList['.++$p.'].groupIdx' => 3,
                'fraudChkParamList['.$p.'].name' => 'UDFV',
                'fraudChkParamList['.$p.'].value' => $billing->getCompany(),
                'fraudChkParamList['.++$p.'].groupIdx' => 4,
                'fraudChkParamList['.$p.'].name' => 'UDFN',
                'fraudChkParamList['.$p.'].value' => 'COMPANY_SHIP',
                'fraudChkParamList['.++$p.'].groupIdx' => 4,
                'fraudChkParamList['.$p.'].name' => 'UDFV',
                'fraudChkParamList['.$p.'].value' => $shipping->getCompany(),
            ));

        if(!$order->getRemoteIp())
        {
            $observer->getParams()
                ->addData(array(
                    'fraudChkParamList['.++$p.'].name' => 'ANID',
                    'fraudChkParamList['.$p.'].value' => '0123456789',
                ));
        }

//        print_r($observer->getParams()->getData()); exit;

        return $this;
    }

    /**
     * @param $result
     * @return array
     */
    protected function parseFraudResultMap($result)
    {
        $array = array();
        if(isset($result['pxyResponse_fullFraudNativeResp']))
        {
            parse_str(str_replace('& ', '&', $result['pxyResponse_fullFraudNativeResp']), $array);
        }
        return $array;
    }

    public function salesOrderPlaceAfter($observer)
    {
        if(!Mage::registry('hpci_capture_paramMap'))
        {
            return false;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        if($order->getPayment()->getMethod() !== 'hpci_acc')
        {
            return false;
        }

        $resultMap = Mage::registry('hpci_capture_paramMap');
        /** @var Mage_Payment_Model_Method_Abstract $payment */

        $result = $this->parseFraudResultMap($resultMap);
        $newStatus = $order->getStatusLabel();
        $oldStatus = $order->getStatusLabel();

        switch(isset($result['AUTO']) ? $result['AUTO']: '')
        {
            case 'A':
                $order
                    ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'processing', 'Kount - Approved')
                    ->save();

                $newStatus = 'Processing';
                break;
            case 'R':
            case 'E':
                $order
                    ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'hpci_order_review', 'Kount - Review')
                    ->save();

                $newStatus = 'Order Review';
                break;
            case 'D':
                $order
                    ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'hpci_pending_cancel', isset($result['RULE_DESCRIPTION_0'])? $result['RULE_DESCRIPTION_0'] : '')
                    ->save();

                $newStatus = 'Pending Cancel';
                break;
            default:
                break;
        }

        // log
        Mage::getModel('hpci/fraudLog')
            ->log('kount', $order, print_r($result, true), 'gateway', $oldStatus, $newStatus)
        ;
    }
}