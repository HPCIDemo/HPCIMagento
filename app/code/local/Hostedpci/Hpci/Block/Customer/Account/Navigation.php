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

class Hostedpci_Hpci_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    /**
     * @param $name
     * @param $path
     * @param $label
     * @param array $urlParams
     * @param null $checkPath
     * @return $this
     */
    public function addLink($name, $path, $label, $urlParams=array(), $checkPath = null)
    {
        if($checkPath and !Mage::getStoreConfig($checkPath))
        {
            return $this;
        }

        $this->_links[$name] = new Varien_Object(array(
            'name' => $name,
            'path' => $path,
            'label' => $label,
            'url' => $this->getUrl($path, $urlParams),
        ));
        return $this;
    }
}
