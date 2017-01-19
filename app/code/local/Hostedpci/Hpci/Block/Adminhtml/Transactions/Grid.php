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
class Hostedpci_Hpci_Block_Adminhtml_Transactions_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('hpci_transactions_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('date_created');
        $this->setDefaultDir('DESC');
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('hpci/transaction')->getCollection();
        if($this->getRequest()->getParam('order_id'))
        {
            $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
            $collection->addFieldToFilter('order_id', $order->getRealOrderId());
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('hpci')->__('ID'),
            'type'   => 'number',
            'index'  => 'id',
        ));

        if(!$this->getRequest()->getParam('order_id'))
        {
            $this->addColumn('order_id', array(
                'header' => Mage::helper('hpci')->__('Order ID'),
                'type'   => 'number',
                'index'  => 'order_id',
            ));
        }

        $this->addColumn('date_created', array(
            'header'=> Mage::helper('hpci')->__('Date'),
            'width' => '160px',
            'type'  => 'datetime',
            'index' => 'date_created',
        ));

        $this->addColumn('method', array(
            'header' => Mage::helper('hpci')->__('Method'),
            'index' => 'method',
        ));

        $this->addColumn('response_status', array(
            'header' => Mage::helper('hpci')->__('Response Status'),
            'index' => 'response_status',
        ));

        $this->addColumn('message', array(
            'header' => Mage::helper('hpci')->__('Message'),
            'index' => 'message',
        ));

        $this->addColumn('last4', array(
            'header' => Mage::helper('hpci')->__('CC'),
            'index' => 'last4',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('hpci')->__('Billing Name'),
            'index' => 'billing_name',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @param $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/*/transaction', array('transaction_id' => $row->getId()));
        }
        return false;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('_current'=>true));
    }
}
