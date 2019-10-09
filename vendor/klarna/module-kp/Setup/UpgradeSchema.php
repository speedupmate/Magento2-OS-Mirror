<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface   $installer
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        if (version_compare($context->getVersion(), '4.0.3', '<')) {
            $this->addPaymentMethodsColumn($installer);
        }
        if (version_compare($context->getVersion(), '5.3.1', '<')) {
            $this->addPaymentMethodInfoColumn($installer);
        }

        if (version_compare($context->getVersion(), '5.5.3', '<')) {
            $this->dropForeignKeyInQuote($installer);
        }
        $installer->endSetup();
    }

    /**
     * Adding the payment_methods column to the klarna quote table
     *
     * @param SchemaSetupInterface $installer
     */
    private function addPaymentMethodsColumn(SchemaSetupInterface $installer)
    {
        $table = $installer->getTable('klarna_payments_quote');

        $ddl = $installer->getConnection()->describeTable($table);
        if (!isset($ddl['payment_methods'])) {
            $installer->getConnection()
                ->addColumn(
                    $table,
                    'payment_methods',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'length'  => 255,
                        'comment' => 'Payment Method Categories'
                    ]
                );
        }
    }

    /**
     * Adding the payment_method_info column to the klarna quote table
     *
     * @param SchemaSetupInterface $installer
     */
    private function addPaymentMethodInfoColumn(SchemaSetupInterface $installer)
    {
        $table = $installer->getTable('klarna_payments_quote');

        $ddl = $installer->getConnection()->describeTable($table);
        if (!isset($ddl['payment_method_info'])) {
            $installer->getConnection()
                ->addColumn(
                    $table,
                    'payment_method_info',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'length'  => 4096,
                        'comment' => 'Payment Method Category Info'
                    ]
                );
        }
    }

    /**
     * Dropping a foreign key in the klarna quote table
     *
     * @param SchemaSetupInterface $installer
     */
    private function dropForeignKeyInQuote(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->dropForeignKey(
            $installer->getTable('klarna_payments_quote'),
            $installer->getFkName(
                'klarna_payments_quote',
                'quote_id',
                'quote',
                'entity_id'
            )
        );
    }
}
