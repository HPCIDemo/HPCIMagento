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
class Varien_Data_Form_Element_Version extends Varien_Data_Form_Element_Abstract
{
    /**
     * @var string
     */
    protected $_extensionName;

    /**
     * Assigns attributes for Element
     *
     * @param array $attributes
     */
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');

        $this->_extensionName = (string)$attributes['field_config']->extension;
    }

    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBold() ? '<strong>' : '';
        $html.= Mage::getConfig()->getNode('modules/'.$this->_extensionName.'/version');
        $html.= $this->getBold() ? '</strong>' : '';
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}
