<?php
/**
 * This file is part of the Klarna Kp module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Upgrades DB data for a module
     *
     * @param ModuleDataSetupInterface $installer
     * @param ModuleContextInterface   $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        if (version_compare($context->getVersion(), '4.0.2', '<')) {
            $this->disableAllQuotes($installer);
        }
        if (version_compare($context->getVersion(), '5.3.2', '<')) {
            $this->disableInvalidQuotes($installer);
            $methods = [
                'klarna_pay_later',
                'klarna_pay_now',
                'klarna_pay_over_time',
                'klarna_direct_debit',
                'klarna_direct_bank_transfer'
            ];
            $methods = "'" . implode("','", $methods) . "'";

            $this->updateAdditionalInformation($installer, $methods);
            $this->changePaymentKeyToGeneric($installer, $methods);
        }
        if (version_compare($context->getVersion(), '5.4.5', '<')) {
            $this->removeStrongHtmlTag($installer);
        }
        if (version_compare($context->getVersion(), '5.5.4', '<')) {
            $this->clearDesignConfig($installer);
        }
        $installer->endSetup();
    }

    /**
     * Mark all quotes as inactive so that switch over to new payments endpoint happens
     *
     * @param ModuleDataSetupInterface $installer
     */
    private function disableAllQuotes(ModuleDataSetupInterface $installer)
    {
        $table = $installer->getTable('klarna_payments_quote');
        $installer->getConnection()->update($table, ['is_active' => 0]);
    }

    /**
     * Updating the additional information
     *
     * @param ModuleDataSetupInterface $installer
     * @param string $methods
     */
    private function updateAdditionalInformation(ModuleDataSetupInterface $installer, $methods)
    {
        $installer->getConnection()
            ->query("update `{$installer->getTable('sales_order_payment')}`" .
                " set `additional_information`=" .
                " replace(`additional_information`, '}', concat(',\"method_code\":\"', `method`, '\"}'))" .
                " where `method` in ({$methods})");
    }

    /**
     * Disabled klarna quotes where we don't have any payment method information
     *
     * @param ModuleDataSetupInterface $installer
     */
    private function disableInvalidQuotes(ModuleDataSetupInterface $installer)
    {
        $installer->getConnection()->update(
            $installer->getTable('klarna_payments_quote'),
            ['is_active' => 0, 'payment_method_info' => '{}'],
            '`payment_method_info` is null'
        );
    }

    /**
     * Change the klarna payment keys to a generic payment key: klarna_kp
     *
     * @param ModuleDataSetupInterface $installer
     * @param $methods
     */
    private function changePaymentKeyToGeneric(ModuleDataSetupInterface $installer, $methods)
    {
        $installer->getConnection()
            ->update(
                $installer->getTable('sales_order_payment'),
                ['method' => 'klarna_kp'],
                "`method` in ({$methods})"
            );
        foreach (['sales_order_grid', 'sales_invoice_grid', 'sales_creditmemo_grid'] as $table) {
            $installer->getConnection()
                ->update(
                    $installer->getTable($table),
                    ['payment_method' => 'klarna_kp'],
                    "`payment_method` in ({$methods})"
                );
        }
    }

    /**
     * Remove the html tag 'strong' from the additional information of the payments
     *
     * @param ModuleDataSetupInterface $installer
     */
    private function removeStrongHtmlTag(ModuleDataSetupInterface $installer)
    {
        $values = [
            '<strong>',
            '<\/strong>'
        ];
        foreach ($values as $value) {
            $manipulation = new \Zend_Db_Expr("replace(`additional_information`, '$value', '')");
            $installer->getConnection()
                ->update(
                    $installer->getTable('sales_order_payment'),
                    ['additional_information' => $manipulation],
                    "`method` = 'klarna_kp'"
                );
        }
    }

    /**
     * clear unused kp design config settings
     *
     * @param ModuleDataSetupInterface $installer
     */
    private function clearDesignConfig(ModuleDataSetupInterface $installer)
    {
        $configPaths = [
            'checkout/klarna_kp_design/color_button',
            'checkout/klarna_kp_design/color_button_text',
            'checkout/klarna_kp_design/color_checkbox',
            'checkout/klarna_kp_design/color_checkbox_checkmark',
            'checkout/klarna_kp_design/color_header',
            'checkout/klarna_kp_design/color_link',
            'checkout/klarna_kp_design/color_text_secondary'
        ];

        $configTable = $installer->getTable('core_config_data');
        $keys = '\'' . implode('\',\'', $configPaths) . '\'';
        $installer->getConnection()->delete($configTable, "`path` in ({$keys})");
    }
}
