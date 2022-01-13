<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Setup\Schema\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Sales\Model\ResourceModel\Order\Invoice;

class MigrateVertexInvoiceSent implements DDLTriggerInterface
{
    /**
     * Pattern with which we can match whether we can apply and use this trigger or not.
     */
    private const MATCH_PATTERN = 'migrateVertexInvoiceSent';

    /**
     * Amount of records to process at a single time
     */
    private const QUERY_BATCH_SIZE = 10000;

    /** @var Invoice */
    private $invoiceResource;

    /** @var ResourceConnection */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection, Invoice $invoiceResource)
    {
        $this->resourceConnection = $resourceConnection;
        $this->invoiceResource = $invoiceResource;
    }

    public function getCallback(ElementHistory $tableHistory): callable
    {
        /** @var Table $table */
        $table = $tableHistory->getNew();
        $vertexConnection = $this->resourceConnection->getConnection($table->getResource());
        $invoiceResource = $this->invoiceResource;

        return static function () use ($table, $vertexConnection, $invoiceResource) {
            $invoiceSentTableName = $table->getName();
            $salesInvoiceTableName = $invoiceResource->getMainTable();

            $invoiceConnection = $invoiceResource->getConnection();

            if (!$invoiceConnection->tableColumnExists($salesInvoiceTableName, 'vertex_invoice_sent')) {
                // Nothing to migrate
                return;
            }

            $page = 1;
            do {
                $select = $invoiceConnection->select()
                    ->from(
                        $salesInvoiceTableName,
                        ['invoice_id' => 'entity_id', 'sent_to_vertex' => 'vertex_invoice_sent']
                    )
                    ->where('vertex_invoice_sent = 1')
                    ->limitPage($page, static::QUERY_BATCH_SIZE);

                $results = $invoiceConnection->fetchAll($select);
                $resultCount = count($results);

                if ($resultCount > 0) {
                    $vertexConnection->insertMultiple($invoiceSentTableName, $results);
                }
                ++$page;
            } while ($resultCount === static::QUERY_BATCH_SIZE);
        };
    }

    public function isApplicable(string $statement): bool
    {
        return $statement === static::MATCH_PATTERN;
    }
}
