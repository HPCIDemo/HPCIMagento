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
class Hostedpci_Hpci_Helper_Data extends Mage_Payment_Helper_Data
{
    /**
     *
     */
    const XML_PATH_SID         = 'hpci/settings/hpci_sid';
    /**
     *
     */
    const XML_PATH_PASS_KEY    = 'hpci/settings/hpci_apipk';
    /**
     *
     */
    const XML_PATH_IFRAME_URL  = 'hpci/settings/hpci_url';
    /**
     *
     */
    const XML_PATH_LOCATION    = 'hpci/settings/hpci_location';
    /**
     *
     */
    const XML_PATH_USERNAME    = 'hpci/settings/hpci_apiun';
    /**
     *
     */
    const XML_PATH_SERVICE_URL = 'hpci/settings/hpci_serviceurl';

    /**
     * Internal parameters for validation
     */
	public function getConfigVars($store = null)
    {
		return Mage::getStoreConfig('hpci/settings', $store);
	}

    /**
     * @return bool
     */
    public function getOscCheck()
    {
        $_configVars = Mage::helper('hpci')->getConfigVars();
        $result = false;

        switch($_configVars['payment_type'])
        {
            case 'auto':
                $result = ($osc = Mage::getConfig()->getNode('modules/Idev_OneStepCheckout') && Mage::helper('onestepcheckout')->isRewriteCheckoutLinksEnabled());
                break;
            case 'onepage':
                $result = true;
                break;
        }

        return $result;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getCcTypeName($type)
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        if (isset($types[$type]))
        {
            return $types[$type];
        }

        return $type;
    }

    /**
     * @return null|string
     */
    public function getClientIp()
    {
        $ip = null;
        if (getenv('HTTP_CLIENT_IP'))
            $ip = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ip = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ip = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ip = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ip = getenv('REMOTE_ADDR');
        else
            $ip = 'UNKNOWN';

        return $ip;
    }

    /**
     * @return bool
     */
    public function paymentAllowed()
    {
        $ip = $this->getClientIp();

        $disableFor = (int)Mage::getStoreConfig('hpci/settings/fraud_protection_interval');
        if($disableFor == 0)
        {
            $disableFor = 5;
        }

        $collection = Mage::getModel('hpci/transaction')->getCollection();
        $collection->getSelect()
            ->where('response_status = ?', 'error')
            ->where('client_ip = ?', $ip)
            ->where('date_created > DATE_SUB(?, INTERVAL '.$disableFor.' MINUTE)', Varien_Date::now())
        ;

        return $collection->getSize() < 3;
    }
}
