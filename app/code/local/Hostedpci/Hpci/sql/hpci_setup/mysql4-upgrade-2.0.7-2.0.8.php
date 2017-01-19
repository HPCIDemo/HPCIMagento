<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

if(!Mage::getModel('core/config_data')->load('hpci/settings/card_exp_template', 'path')->getId())
{
    $installer->run("
    INSERT INTO {$this->getTable('core/email_template')} SET
        template_code = 'hpci_settings_card_exp_template',
        template_text = 'The stored credit card below will be expiring soon. Please login to your account to update your card information. \n\n{{var card.getCcTypeName()}} {{var card.getCcNumberLast4()}} {{var card.getCcExpMonth()}}/{{var card.getCcExpYear()}}',
        template_styles = '',
        template_subject = '{{var store.getFrontendName()}}: Credit Card Expiration',
        template_sender_name = null,
        template_sender_email = null,
        orig_template_code = '',
        orig_template_variables = '';
    ");

    $installer->run("
        INSERT INTO {$this->getTable('core/config_data')} SET
          scope = 'default',
          scope_id = 0,
          path = 'hpci/settings/card_exp_template',
          `value` = (select template_id from {$this->getTable('core/email_template')} where template_code = 'hpci_settings_card_exp_template')
    ");
}

$installer->endSetup();
