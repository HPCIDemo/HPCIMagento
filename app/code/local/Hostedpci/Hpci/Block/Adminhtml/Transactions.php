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
class Hostedpci_Hpci_Block_Adminhtml_Transactions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'hpci';
        $this->_controller = 'adminhtml_transactions';
        $this->_headerText = Mage::helper('hpci')->__('HPCI Transactions');
        $this->setTemplate('widget/grid/container.phtml');
    }
}
