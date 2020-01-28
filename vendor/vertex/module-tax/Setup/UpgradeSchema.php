<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Module\Setup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Schema Upgrade Script
 *
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @throws Zend_Db_Exception
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $db = $installer->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $installer->getConnection()->changeColumn(
                $installer->getTable('vertex_taxrequest'),
                'quote_id',
                'quote_id',
                [
                    'type' => Table::TYPE_BIGINT,
                    'length' => 20,
                ]
            );

            $installer->getConnection()->changeColumn(
                $installer->getTable('vertex_taxrequest'),
                'order_id',
                'order_id',
                [
                    'type' => Table::TYPE_BIGINT,
                    'length' => 20,
                ]
            );
        }

        if (version_compare($context->getVersion(), '100.0.1') < 0) {
            $table = $installer->getTable('vertex_taxrequest');
            $db->changeColumn(
                $table,
                'request_id',
                'request_id',
                [
                    'type' => Table::TYPE_BIGINT,
                    'length' => 20,
                    'unsigned' => true,
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true,
                ]
            );
        }

        if (version_compare($context->getVersion(), '100.1.0') < 0) {
            $this->createCustomerCodeTable($setup);
            $this->dropTaxAreaIdColumns($setup);
            $this->createVertexInvoiceSentTable($setup);
            $this->migrateInvoiceSentData($setup);
            $this->deleteInvoiceSentColumnFromInvoiceTable($setup);
        }

        if (version_compare($context->getVersion(), '100.2.0') < 0) {
            $this->createOrderInvoiceStatusTable($setup);
        }

        if (version_compare($context->getVersion(), '100.3.0') < 0) {
            $this->createOrderItemTaxCodeTable($setup);
            $this->createOrderItemInvoiceTextCodeTable($setup);
            $this->createOrderItemVertexTaxCodeTable($setup);
            $this->createCreditmemoItemInvoiceTextCodeTable($setup);
            $this->createCreditmemoItemTaxCodeTable($setup);
            $this->createCreditmemoItemVertexTaxCodeTable($setup);
        }

        if (version_compare($context->getVersion(), '100.4.0') < 0) {
            $this->createCustomOptionFlexFieldTable($setup);
        }
      
        if (version_compare($context->getVersion(), '100.5.0') < 0) {
            $this->addResponseTimeToLogTable($setup);
        }
    }

    /**
     * Create a response_time column on the logging table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addResponseTimeToLogTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_taxrequest');

        if (!$setup->getConnection()->tableColumnExists($tableName, 'response_time')) {
            $setup->getConnection()->addColumn(
                $tableName,
                'response_time',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => null,
                    'unsigned' => true,
                    'comment' => 'Milliseconds taken for Vertex API call to complete',
                    'after' => 'request_type',
                ]
            );
        }
    }

    /**
     * Create a table holding a string with invoice text code for creditmemo item from Vertex Invoice request
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createCreditmemoItemInvoiceTextCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_sales_creditmemo_item_invoice_text_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => false,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Creditmemo Item ID'
            )
            ->addColumn(
                'invoice_text_code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ],
                'Invoice text code from Vertex'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['item_id', 'invoice_text_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['item_id', 'invoice_text_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create a table holding a string with tax code for creditmemo item from Vertex Invoice request
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createCreditmemoItemTaxCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_sales_creditmemo_item_tax_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => false,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Creditmemo Item ID'
            )
            ->addColumn(
                'tax_code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ],
                'Invoice text code from Vertex'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['item_id', 'tax_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['item_id', 'tax_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create a table holding a string with vertex tax code for creditmemo item from Vertex Invoice request
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createCreditmemoItemVertexTaxCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_sales_creditmemo_item_vertex_tax_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => false,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Creditmemo Item ID'
            )->addColumn(
                'vertex_tax_code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ],
                'Text code from Vertex'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['item_id', 'vertex_tax_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['item_id', 'vertex_tax_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create table to store option_id to flex field map
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createCustomOptionFlexFieldTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_custom_option_flex_field');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Map Entity ID'
            )->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Customizable Option ID'
            )->addColumn(
                'website_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                    'unsigned' => true,
                ],
                'Website ID'
            )->addColumn(
                'flex_field',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Flexible Field ID'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['option_id', 'website_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'website_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Customizable Option to Flex Field Map');

        $setup->getConnection()->createTable($table);
    }

    /**
     * Create the Vertex Customer Code table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createCustomerCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_customer_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Customer ID'
            )
            ->addColumn(
                'customer_code',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                ],
                'Customer Code for Vertex'
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create a table holding a boolean flag for whether or not a Vertex Invoice has been sent for the order
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createOrderInvoiceStatusTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_order_invoice_status');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Order ID'
            )
            ->addColumn(
                'sent_to_vertex',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ],
                'Invoice has been logged in Vertex'
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create a table holding a string with invoice text code for order item from Vertex Invoice request
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createOrderItemInvoiceTextCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_sales_order_item_invoice_text_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => false,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Order Item ID'
            )
            ->addColumn(
                'invoice_text_code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ],
                'Invoice text code from Vertex'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['item_id', 'invoice_text_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['item_id', 'invoice_text_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create a table holding a string with tax code for order item from Vertex Invoice request
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createOrderItemTaxCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_sales_order_item_tax_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => false,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Order Item ID'
            )
            ->addColumn(
                'tax_code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ],
                'Invoice text code from Vertex'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['item_id', 'tax_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['item_id', 'tax_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create a table holding a string with vertex tax code for order item from Vertex Invoice request
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    private function createOrderItemVertexTaxCodeTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_sales_order_item_vertex_tax_code');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => false,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Order Item ID'
            )->addColumn(
                'vertex_tax_code',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                ],
                'Text code from Vertex'
            )->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['item_id', 'vertex_tax_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['item_id', 'vertex_tax_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Create the Vertex Invoice Sent table
     *
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function createVertexInvoiceSentTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('vertex_invoice_sent');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'invoice_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Invoice ID'
            )
            ->addColumn(
                'sent_to_vertex',
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ],
                'Invoice has been logged in Vertex'
            );

        $setup->getConnection()
            ->createTable($table);
    }

    /**
     * Delete the old Invoice Sent column from the Invoice table
     *
     * @param SchemaSetupInterface $setup
     */
    private function deleteInvoiceSentColumnFromInvoiceTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('sales_invoice');
        if ($this->getConnection($setup, 'sales')->tableColumnExists($table, 'vertex_invoice_sent')) {
            $this->getConnection($setup, 'sales')->dropColumn($table, 'vertex_invoice_sent');
        }
    }

    /**
     * Drop Tax Area IDs from the Address Tables
     *
     * @param SchemaSetupInterface $setup
     */
    private function dropTaxAreaIdColumns(SchemaSetupInterface $setup)
    {
        $orderTable = $setup->getTable('sales_order_address');
        if ($this->getConnection($setup, 'sales')->tableColumnExists($orderTable, 'tax_area_id')) {
            $this->getConnection($setup, 'sales')->dropColumn($orderTable, 'tax_area_id');
        }

        $quoteTable = $setup->getTable('quote_address');
        if ($this->getConnection($setup, 'checkout')->tableColumnExists($quoteTable, 'tax_area_id')) {
            $this->getConnection($setup, 'checkout')->dropColumn($quoteTable, 'tax_area_id');
        }
    }

    /**
     * Retrieve Connection
     *
     * @param SchemaSetupInterface $setup
     * @param string $connectionName
     * @return AdapterInterface
     */
    private function getConnection(SchemaSetupInterface $setup, $connectionName)
    {
        if ($setup instanceof Setup) {
            return $setup->getConnection($connectionName);
        }
        return $setup->getConnection();
    }

    /**
     * Migrate Invoice Sent data from the old table column to the new table
     *
     * @param SchemaSetupInterface $setup
     */
    private function migrateInvoiceSentData(SchemaSetupInterface $setup)
    {
        $salesDb = $this->getConnection($setup, 'sales');
        $db = $setup->getConnection();
        $oldTableName = $setup->getTable('sales_invoice');
        $newTableName = $setup->getTable('vertex_invoice_sent');

        if (!$salesDb->tableColumnExists($oldTableName, 'vertex_invoice_sent')) {
            return;
        }

        $select = $salesDb->select()
            ->from($oldTableName)
            ->where('vertex_invoice_sent = 1');

        $results = array_map(
            static function ($rawResult) {
                return [
                    'invoice_id' => $rawResult['entity_id'],
                    'sent_to_vertex' => 1,
                ];
            },
            $salesDb->fetchAll($select)
        );

        if (!count($results)) {
            return;
        }

        $db->insertMultiple(
            $newTableName,
            $results
        );
    }
}
