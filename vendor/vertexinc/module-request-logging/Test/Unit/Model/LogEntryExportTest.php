<?php
/**
 * @author    Blue Acorn iCi <code@blueacornici.com>
 * @copyright 2021 Vertex, Inc. All Rights Reserved.
 */

declare(strict_types=1);

namespace Vertex\RequestLogging\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Stdlib\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\RequestLoggingApi\Api\Data\LogEntryInterface;
use Vertex\RequestLogging\Model\LogEntryExport;
use Vertex\RequestLogging\Test\Unit\TestCase;

/**
 * Test that LogEntryExport can work with its filesystem.
 */
class LogEntryExportTest extends TestCase
{
    /** @var MockObject|DateTime */
    private $dateTimeMock;

    /** @var MockObject|DirectoryWriteInterface */
    private $directoryWriteMock;

    /** @var MockObject|Filesystem */
    private $fileSystemMock;

    /** @var MockObject|FileWriteInterface */
    private $fileWriteMock;

    /** @var LogEntryExport */
    private $logEntryExport;

    /**
     * Perform test setup.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->directoryWriteMock = $this->createMock(DirectoryWriteInterface::class);
        $this->fileWriteMock = $this->createMock(FileWriteInterface::class);

        $this->dateTimeMock->method('formatDate')
            ->willReturn('2018-06-01');

        $this->directoryWriteMock->method('openFile')
            ->willReturn($this->fileWriteMock);

        $this->fileSystemMock->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);

        $this->logEntryExport = $this->getObject(
            LogEntryExport::class,
            [
                'fileSystem' => $this->fileSystemMock,
                'dateTime' => $this->dateTimeMock,
            ]
        );
    }

    /**
     * Test the expected file path on export close.
     *
     * @covers LogEntryExport::close()
     * @throws FileSystemException
     * @return void
     */
    public function testExpectedFilePathOnClose(): void
    {
        $expectedBasePath = '/path/to/output/';
        $expectedFilename = 'test.csv';

        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn($expectedBasePath);

        $this->logEntryExport->open($expectedFilename);

        $actualFilePath = $this->logEntryExport->close();

        $this->assertEquals($expectedBasePath . $expectedFilename, $actualFilePath);
    }

    /**
     * Test an unexpected file path on export close.
     *
     * @covers LogEntryExport::close()
     * @throws FileSystemException
     * @return void
     */
    public function testUnexpectedFilePathOnClose(): void
    {
        $expectedBasePath = '/path/to/output/';
        $unexpectedBasePath = '/path/to/another/output/';
        $expectedFilename = 'test.csv';

        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn($expectedBasePath);

        $this->logEntryExport->open($expectedFilename);

        $actualFilePath = $this->logEntryExport->close();

        $this->assertNotEquals($unexpectedBasePath . $expectedFilename, $actualFilePath);
    }

    /**
     * Test that the file write stream can be acquired.
     *
     * @covers LogEntryExport::open()
     * @throws FileSystemException
     * @return void
     */
    public function testStreamOpenSuccess(): void
    {
        $expectedBasePath = '/path/to/output/';

        $this->directoryWriteMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn($expectedBasePath);

        $this->directoryWriteMock->expects($this->once())
            ->method('openFile')
            ->willReturn($this->fileWriteMock);

        $this->logEntryExport->open();
    }

    /**
     * Test that the export does not re-open a file if already working on an existing file.
     *
     * @covers LogEntryExport::open()
     * @throws FileSystemException
     * @return void
     */
    public function testBlockOpenWhenStreamExists(): void
    {
        $this->directoryWriteMock->expects($this->once())
            ->method('openFile')
            ->willReturn($this->fileWriteMock);

        $this->logEntryExport->open(); // will succeed
        $this->logEntryExport->open(); // will be blocked
    }

    /**
     * Test that archiving fails appropriately when no stream is available.
     *
     * @covers LogEntryExport::write()
     * @return void
     */
    public function testExpectedExceptionOnWriteFailure(): void
    {
        $expectedException = NotFoundException::class;
        $actualException = '';

        try {
            $this->logEntryExport->write(
                $this->createMock(LogEntryInterface::class)
            );
        } catch (\Exception $error) {
            $actualException = get_class($error);
        }

        $this->assertEquals($expectedException, $actualException);
    }

    /**
     * Provider for {@see testWriteSucceedsWithAnyDataset}.
     *
     * @return array
     */
    public function provideMockEntryData(): array
    {
        return [
            [
                'entryData' => [
                    'request_id' => 1,
                    'request_type' => 'tax_area_lookup',
                    'quote_id' => 1,
                    'order_id' => 1,
                    'total_tax' => 2.00,
                    'source_path' => '',
                    'tax_area_id' => '',
                    'sub_total' => 10.00,
                    'total' => 12.00,
                    'lookup_result' => '',
                    'request_date' => '',
                    'request_xml' => '',
                    'response_xml' => '',
                ],
            ]
        ];
    }
}
