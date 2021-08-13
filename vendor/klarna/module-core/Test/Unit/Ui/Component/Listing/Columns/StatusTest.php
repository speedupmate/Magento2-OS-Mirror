<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 *
 */

namespace Klarna\Core\Test\Unit\Ui\Component\Listing\Columns;

use Klarna\Core\Ui\Component\Listing\Columns\Status;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Ui\Component\Listing\Columns\Status
 */
class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    private $status;

    /**
     * Test http status code 200
     * @covers ::prepareDataSource
     */
    public function testStatusCode200()
    {
        $dataSource['data']['items'][0]['status'] = 200;
        $expected = '<span class="grid-severity-notice">200</span>';
        $actual   = $this->status->prepareDataSource($dataSource);
        $this::assertEquals($expected, $actual['data']['items'][0]['status']);
    }

    /**
     * Test http status code 500
     * @covers ::prepareDataSource
     */
    public function testStatusCode500()
    {
        $dataSource['data']['items'][0]['status'] = 500;
        $expected = '<span class="grid-severity-critical">500</span>';
        $actual   = $this->status->prepareDataSource($dataSource);
        self::assertEquals($expected, $actual['data']['items'][0]['status']);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $mockFactory   = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);
        $this->status  = $objectFactory->create(Status::class);
    }
}
