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

class Hostedpci_Hpci_KountController extends Mage_Core_Controller_Front_Action
{
    protected $_masks = array(
        '64.128.87.0/24',
        '64.128.91.0/24',
        '209.81.12.0/24',
        '205.147.95.0/24',
        '192.157.18.0/24',
        '192.157.19.0/24',
    );

    protected $_ips = array(
        '107.6.43.68',
        '205.147.105.145',
        '205.147.105.237',
        '52.0.101.155',
        '52.0.124.194',
        '52.0.235.19',
        '52.1.10.255',
        '52.1.101.154',
        '52.17.0.17',
        '52.17.214.105',
        '52.17.239.195',
        '52.2.139.144',
        '52.2.169.22',
        '52.2.172.54',
        '52.2.178.246',
        '52.2.211.15',
        '52.2.211.70',
        '52.2.221.152',
        '52.21.39.226',
        '52.22.121.129',
        '52.24.248.45',
        '52.25.102.18',
        '52.28.196.122',
        '52.28.32.104',
        '52.28.88.202',
        '52.29.101.97',
        '52.29.11.122',
        '52.29.13.176',
        '52.29.13.254',
        '52.29.15.88',
        '52.29.5.12',
        '52.29.53.241',
        '52.29.89.207',
        '52.3.102.87',
        '52.3.110.223',
        '52.3.123.200',
        '52.3.126.122',
        '52.3.133.10',
        '52.3.135.199',
        '52.3.135.213',
        '52.3.167.4',
        '52.3.90.148',
        '52.3.97.229',
        '52.4.103.52',
        '52.4.115.41',
        '52.4.179.88',
        '52.49.113.112',
        '52.5.126.2',
        '52.6.153.248',
        '52.6.85.195',
        '52.7.103.204',
        '52.7.163.84',
        '52.74.81.57',
        '52.76.194.87',
        '52.76.243.93',
        '52.77.2.6',
        '52.8.210.194',
        '52.8.59.82',
        '52.8.66.223',
        '52.8.72.1',
        '52.8.8.170',
        '52.9.19.95',
        '52.9.2.89',
        '52.9.21.101',
        '52.9.21.160',
        '54.149.187.251',
        '54.152.44.225',
        '54.169.73.21',
        '54.174.105.46',
        '54.174.141.252',
        '54.174.158.231',
        '54.179.171.22',
        '54.183.107.148',
        '54.207.0.197',
        '54.64.74.187',
        '54.65.1.119',
        '54.67.102.40',
        '54.67.24.149',
        '54.67.97.5',
        '54.68.190.72',
        '54.84.70.229',
        '54.88.27.137',
        '54.93.126.109',
        '54.94.144.82',
        '64.69.71.145',
        '64.69.71.201',
        '65.39.180.191',
        '65.39.180.192',
        '65.39.180.199',
        '65.39.180.202',
        '66.155.100.133',
        '66.155.100.164',
        '66.155.100.170',
        '66.155.100.181',
        '66.155.100.182',
        '52.30.255.63',
        '52.50.123.164',
        '52.18.184.244',
        '52.48.8.158',
        '52.70.80.31',
        '69.28.224.195',
        '65.39.184.19',
        '52.63.124.208',
        '52.63.128.58',
        '52.63.18.107',
    );
    /**
     * Index action
     */
    public function indexAction()
    {
        // Nothing to do
    }

    /**
     * Iframe action
     */
    public function iframeAction()
    {
        Mage::app()->getResponse()->setRedirect($this->_getUrl('htm'));
        Mage::app()->getResponse()->sendResponse();
    }

    /**
     * Gif action
     */
    public function gifAction()
    {
        Mage::app()->getResponse()->setRedirect($this->_getUrl('gif'));
        Mage::app()->getResponse()->sendResponse();
    }

    /**
     * Get url
     * @param string
     * @return string
     */
    private function _getUrl($sMode)
    {
        // Url for logo
        return sprintf(
            '%s/logo.%s?m=%s&s=%s',
            Mage::getStoreConfig('payment/hpci_acc/antifraud/kount_test') ? 'https://tst.kaptcha.com' : 'https://ssl.kaptcha.com',
            $sMode,
            Mage::getStoreConfig('payment/hpci_acc/antifraud/kount_merchantnum'),
            Mage::helper('hpci/kount')->getSessionID());
    }

    /**
     *
     */
    public function fraudAction()
    {
        $this->getResponse()
            ->setBody('OK')
            ->sendResponse();

        $ip = Mage::helper('hpci')->getClientIp();
        Mage::log('request', null, 'kount_endpoint.log');
        Mage::log($ip, null, 'kount_endpoint.log');
        Mage::log('IPs check: ' . (int)in_array($ip, $this->_ips), null, 'kount_endpoint.log');
        Mage::log('Mask check: ' . (int)$this->ipcheck($ip, $this->_masks), null, 'kount_endpoint.log');

        if(!in_array($ip, $this->_ips) and !$this->ipcheck($ip, $this->_masks))
        {
            Mage::log('Access Denied', null, 'kount_endpoint.log');
            echo 'Access Denied';
            exit;
        }

        $xmlData = file_get_contents('php://input');
        Mage::log($xmlData, null, 'kount_endpoint.log');

        if(!$xmlData)
        {
            echo 'Error';
            exit;
        }

        $xml = simplexml_load_string($xmlData);
        if(!$xml)
        {
            echo 'Wrong XML';
            exit;
        }
        foreach($xml->event as $event)
        {
            $oid = (string)$event->key['order_number'];
            Mage::log('#'.$oid, null, 'kount_endpoint.log');

            if($oid)
            {
                Mage::log($event->asXML(), null, 'kount_endpoint.log');

                $order = Mage::getModel('sales/order')->loadByIncrementId($oid);

                if(!$order->getId())
                {
                    Mage::log('Wrong order ID', null, 'kount_endpoint.log');
                    echo 'Wrong Order';
                    exit;
                }

                $newStatus = $order->getStatusLabel();
                $oldStatus = $order->getStatusLabel();

//                if($oldStatus === 'canceled')
//                {
//                    Mage::log('Unable to change status, because current status = Canceled', null, 'kount_endpoint.log');
//                    continue;
//                }

                if($order->getStatus() != 'hpci_order_review')
                {
                    $order
                        ->addStatusHistoryComment($event->new_value['reason_code'] . ' - '. $event->new_value)
                        ->save()
                    ;
                    continue;
                }

                if($event->name == 'WORKFLOW_STATUS_EDIT')
                {
                    switch((string)$event->new_value)
                    {
                        case 'A':
                            $order
                                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'processing', 'Kount - Approved. Agent - '. $event->agent)
                                ->save();

                            $newStatus = 'Processing';
                            break;
                        case 'R':
                        case 'E':
                            $order
                                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'hpci_order_review', 'Kount - Review. Agent - '. $event->agent)
                                ->save();

                            $newStatus = 'Order Review';
                            break;
                        case 'D':
                            $order
                                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'hpci_pending_cancel', 'Kount - Declined. Agent - '. $event->agent)
                                ->save();

                            $newStatus = 'Pending Cancel';
                            break;
                        default:
                            break;
                    }
                }

                if($event->name == 'WORKFLOW_NOTES_ADD')
                {
                    if($order->getState() == 'complete')
                    {
                        $order->addStatusHistoryComment($event->new_value['reason_code'] . ' - '. $event->new_value);
                    }
                    else
                    {
                        $order
                            ->setState($order->getState(), $order->getStatus(), $event->new_value['reason_code'] . ' - '. $event->new_value)
                            ->save();
                    }
                }

                Mage::log('From: '. $oldStatus . ' To:'. $newStatus, null, 'kount_endpoint.log');

                // log
                Mage::getModel('hpci/fraudLog')
                    ->log('kount', $order, print_r($event->asXML(), true), 'endpoint', $oldStatus, $newStatus)
                ;
            }
            
        }

        Mage::log('Done', null, 'kount_endpoint.log');

        echo 'OK';
        exit;
    }

    /**
     * @param $IP
     * @param $CIDR
     * @return bool
     */
    protected function ipcheck($IP, $CIDR)
    {
        if(is_array($CIDR))
        {
            foreach($CIDR as $mask)
            {
                if($this->ipcheck($IP, $mask))
                {
                    return true;
                }
            }
            return false;
        }
        list ($net, $mask) = explode('/',$CIDR);

        $ip_net = ip2long ($net);
        $ip_mask = ~((1 << (32 - $mask)) - 1);

        $ip_ip = ip2long ($IP);

        $ip_ip_net = $ip_ip & $ip_mask;
        return ($ip_ip_net == $ip_net);
    }
}
