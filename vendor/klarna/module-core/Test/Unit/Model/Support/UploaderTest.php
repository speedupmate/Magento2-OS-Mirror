<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Model\Support;

use Klarna\Core\Model\Support\Uploader;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Model\Support\Uploader
 */
class UploaderTest extends TestCase
{
    /**
     * @var Uploader
     */
    private $model;
    /**
     * @var Write
     */
    private $write;
    /**
     * @var FileUploader
     */
    private $fileUploader;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::upload()
     * @doesNotPerformAssertions
     */
    public function testUploaderSaveIsCalledOnce(): void
    {
        $this->write->method('getAbsolutePath')
            ->willReturn('');
        $this->dependencyMocks['filesystem']->method('getDirectoryWrite')
            ->willReturn($this->write);
        $this->dependencyMocks['fileUploaderFactory']->method('create')
            ->willReturn($this->fileUploader);
        $this->fileUploader
            ->expects($this->once())
            ->method('save')
            ->willReturn([]);
        $this->model->upload();
    }

    /**
     * @covers ::upload()
     */
    public function testUploaderReturnsArray(): void
    {
        $this->write->method('getAbsolutePath')
            ->willReturn('');
        $this->dependencyMocks['filesystem']->method('getDirectoryWrite')
            ->willReturn($this->write);
        $this->dependencyMocks['fileUploaderFactory']->method('create')
            ->willReturn($this->fileUploader);
        $this->fileUploader
            ->expects($this->once())
            ->method('save')
            ->willReturn([]);
        self::assertIsArray($this->model->upload());
    }

    /**
     * @covers ::upload()
     * @doesNotPerformAssertions
     */
    public function testUploaderAddsErrorMessageWhenExceptionIsThrown(): void
    {
        $this->write->method('getAbsolutePath')
            ->willReturn('');
        $this->dependencyMocks['filesystem']->method('getDirectoryWrite')
            ->willReturn($this->write);
        $this->dependencyMocks['fileUploaderFactory']->method('create')
            ->willReturn($this->fileUploader);
        $this->fileUploader
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new FileSystemException(__('Some error has occurred.'))));
        $this->dependencyMocks['messageManager']
            ->expects($this->once())
            ->method('addErrorMessage');
        $this->model->upload();
    }

    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->model           = $objectFactory->create(Uploader::class);
        $this->write           = $mockFactory->create(Write::class);
        $this->fileUploader    = $mockFactory->create(FileUploader::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
