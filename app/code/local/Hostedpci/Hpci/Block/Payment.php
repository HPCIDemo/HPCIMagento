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
class Hostedpci_Hpci_Block_Payment extends Mage_Core_Block_Template
{
    /**
     * Return Payment logo src
     *
     * @return string
     */
    public function getHpciLogoSrc()
    {
        $locale = Mage::getModel('hpci/acc')->getLocale();
        $logoFilename = Mage::getDesign()
            ->getFilename('images' . DS . 'hpci' . DS . 'banner_120_' . $locale . '.png', array('_type' => 'skin'));

        if (file_exists($logoFilename)) {
            return $this->getSkinUrl('images/hpci/banner_120_'.$locale.'.png');
        }

        return $this->getSkinUrl('images/hpci/banner_120_int.png');
    }
}
