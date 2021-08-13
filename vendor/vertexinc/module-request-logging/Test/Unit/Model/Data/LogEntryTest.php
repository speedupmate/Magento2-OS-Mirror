<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Test\Unit\Model\Data;

use Vertex\RequestLoggingApi\Model\Data\LogEntry;
use Vertex\RequestLogging\Test\Unit\TestCase;

class LogEntryTest extends TestCase
{
    public function testType(): void
    {
        $entry = $this->createEntry();
        $entry->setType('type');
        $this->assertEquals('type', $entry->getType());
        $this->assertOthersNull($entry, 'getType');
    }

    public function testCartId(): void
    {
        $entry = $this->createEntry();
        $entry->setCartId(2);
        $this->assertEquals(2, $entry->getCartId());
        $this->assertOthersNull($entry, 'getCartId');
    }

    public function testOrderId(): void
    {
        $entry = $this->createEntry();
        $entry->setOrderId(3);
        $this->assertEquals(3, $entry->getOrderId());
        $this->assertOthersNull($entry, 'getOrderId');
    }

    public function testTotalTax(): void
    {
        $entry = $this->createEntry();
        $entry->setTotalTax(3.14);
        $this->assertEquals(3.14, $entry->getTotalTax());
        $this->assertOthersNull($entry, 'getTotalTax');
    }

    public function testSourcePath(): void
    {
        $entry = $this->createEntry();
        $entry->setSourcePath('path');
        $this->assertEquals('path', $entry->getSourcePath());
        $this->assertOthersNull($entry, 'getSourcePath');
    }

    public function testTaxAreaId(): void
    {
        $entry = $this->createEntry();
        $entry->setTaxAreaId(258);
        $this->assertEquals(258, $entry->getTaxAreaId());
        $this->assertOthersNull($entry, 'getTaxAreaId');
    }

    public function testSubTotal(): void
    {
        $entry = $this->createEntry();
        $entry->setSubTotal(4.0);
        $this->assertEquals(4.0, $entry->getSubTotal());
        $this->assertOthersNull($entry, 'getSubTotal');
    }

    public function testTotal(): void
    {
        $entry = $this->createEntry();
        $entry->setTotal(8.0);
        $this->assertEquals(8.0, $entry->getTotal());
        $this->assertOthersNull($entry, 'getTotal');
    }

    public function testLookupResult(): void
    {
        $entry = $this->createEntry();
        $entry->setLookupResult('val');
        $this->assertEquals('val', $entry->getLookupResult());
        $this->assertOthersNull($entry, 'getLookupResult');
    }

    public function testDate(): void
    {
        $entry = $this->createEntry();
        $entry->setDate('val');
        $this->assertEquals('val', $entry->getDate());
        $this->assertOthersNull($entry, 'getDate');
    }

    public function testRequestXml(): void
    {
        $entry = $this->createEntry();
        $entry->setRequestXml('val');
        $this->assertEquals('val', $entry->getRequestXml());
        $this->assertOthersNull($entry, 'getRequestXml');
    }

    public function testResponseXml(): void
    {
        $entry = $this->createEntry();
        $entry->setResponseXml('val');
        $this->assertEquals('val', $entry->getResponseXml());
        $this->assertOthersNull($entry, 'getResponseXml');
    }

    /**
     * @return LogEntry
     */
    private function createEntry(): LogEntry
    {
        return $this->getObject(LogEntry::class);
    }

    /**
     * Helper method for ensuring there are no side effects on the data class
     *
     * @param LogEntry $object
     * @param string $test Method we should expect a result from
     */
    private function assertOthersNull(LogEntry $object, string $test): void
    {
        $methods = [
            'getType',
            'getCartId',
            'getOrderId',
            'getTotalTax',
            'getSourcePath',
            'getTaxAreaId',
            'getSubTotal',
            'getTotal',
            'getLookupResult',
            'getDate',
            'getRequestXml',
            'getResponseXml'
        ];

        foreach ($methods as $method) {
            if ($method !== $test) {
                $this->assertNull($object->{$method}());
            }
        }
    }
}
