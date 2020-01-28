<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Model;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Stdlib\DateTime;
use Vertex\Tax\Api\Data\LogEntryInterface;
use Vertex\Tax\Model\Data\LogEntry;
use Vertex\Tax\Model\LogEntryExport;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test that LogEntryExport can work with its filesystem.
 */
class LogEntryExportTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|DateTime */
    private $dateTimeMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|DirectoryWriteInterface */
    private $directoryWriteMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Filesystem */
    private $fileSystemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|FileWriteInterface */
    private $fileWriteMock;

    /** @var LogEntryExport */
    private $logEntryExport;

    /**
     * Perform test setup.
     */
    protected function setUp()
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
     * @covers \Vertex\Tax\Model\LogEntryExport::close()
     */
    public function testExpectedFilePathOnClose()
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
     * @covers \Vertex\Tax\Model\LogEntryExport::close()
     */
    public function testUnexpectedFilePathOnClose()
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
     * @covers \Vertex\Tax\Model\LogEntryExport::open()
     */
    public function testStreamOpenSuccess()
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
     * @covers \Vertex\Tax\Model\LogEntryExport::open()
     */
    public function testBlockOpenWhenStreamExists()
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
     * @covers \Vertex\Tax\Model\LogEntryExport::write()
     */
    public function testExpectedExceptionOnWriteFailure()
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
    public function provideMockEntryData()
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
