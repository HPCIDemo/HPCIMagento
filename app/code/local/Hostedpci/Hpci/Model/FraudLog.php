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
 * Class Hostedpci_Hpci_Model_FraudLog
 */
class Hostedpci_Hpci_Model_FraudLog extends Mage_Core_Model_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('hpci/fraudLog');
    }

    public function log($system, $order, $data, $method, $statusFrom, $statusTo)
    {
        $this
            ->setData(array(
                'date_created'  => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
                'order_increment_id'    => $order->getIncrementId(),
                'order_status_from'    => $statusFrom,
                'order_status_to'    => $statusTo,
                'fraud_data'    => $data,
                'fraud_system'  => $system,
                'fraud_method'  => $method,
            ))
            ->save();

        return true;
    }
}
