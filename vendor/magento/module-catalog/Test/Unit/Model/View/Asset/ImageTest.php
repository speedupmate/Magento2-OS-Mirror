<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\View\Asset;

use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\View\Asset\Image;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Asset\ContextInterface;

/**
 * Class ImageTest
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\View\Asset\Image
     */
    protected $model;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfig;

    /**
     * @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptor;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageContext;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepo;

    protected function setUp()
    {
        $this->mediaConfig = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $this->encryptor = $this->getMockBuilder(EncryptorInterface::class)->getMockForAbstractClass();
        $this->imageContext = $this->getMockBuilder(ContextInterface::class)->getMockForAbstractClass();
        $this->assetRepo = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Image(
            $this->mediaConfig,
            $this->imageContext,
            $this->encryptor,
            '/somefile.png',
            $this->assetRepo
        );
    }

    public function testModuleAndContentAndContentType()
    {
        $contentType = 'image';
        $this->assertEquals($contentType, $this->model->getContentType());
        $this->assertEquals($contentType, $this->model->getSourceContentType());
        $this->assertNull($this->model->getContent());
        $this->assertEquals('cache', $this->model->getModule());
    }

    public function testGetFilePath()
    {
        $this->assertEquals('/somefile.png', $this->model->getFilePath());
    }

    public function testGetSoureFile()
    {
        $this->mediaConfig->expects($this->once())->method('getBaseMediaPath')->willReturn('catalog/product');
        $this->assertEquals('catalog/product/somefile.png', $this->model->getSourceFile());
    }

    public function testGetContext()
    {
        $this->assertInstanceOf(ContextInterface::class, $this->model->getContext());
    }

    /**
     * @param string $filePath
     * @param array $miscParams
     * @dataProvider getPathDataProvider
     */
    public function testGetPath($filePath, $miscParams)
    {
        $imageModel = new Image(
            $this->mediaConfig,
            $this->imageContext,
            $this->encryptor,
            $filePath,
            $this->assetRepo,
            $miscParams
        );
        $absolutePath = '/var/www/html/magento2ce/pub/media/catalog/product';
        $hashPath = md5(implode('_', $miscParams));
        $this->imageContext->expects($this->once())->method('getPath')->willReturn($absolutePath);
        $this->encryptor->expects($this->once())->method('hash')->willReturn($hashPath);
        $this->assertEquals(
            $absolutePath . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $hashPath . $filePath,
            $imageModel->getPath()
        );
    }

    /**
     * @param string $filePath
     * @param array $miscParams
     * @dataProvider getPathDataProvider
     */
    public function testGetUrl($filePath, $miscParams)
    {
        $imageModel = new Image(
            $this->mediaConfig,
            $this->imageContext,
            $this->encryptor,
            $filePath,
            $this->assetRepo,
            $miscParams
        );
        $absolutePath = 'http://localhost/pub/media/catalog/product';
        $hashPath = md5(implode('_', $miscParams));
        $this->imageContext->expects($this->once())->method('getBaseUrl')->willReturn($absolutePath);
        $this->encryptor->expects($this->once())->method('hash')->willReturn($hashPath);
        $this->assertEquals(
            $absolutePath . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $hashPath . $filePath,
            $imageModel->getUrl()
        );
    }

    /**
     * Test retrieve URL pointing to a resource with default placeholder url.
     */
    public function testGetUrlWithDefaultPlaceHolder()
    {
        $filePath = '';
        $url = 'testUrl';
        $miscParams = $this->getPathDataProvider()[1][1];
        $this->assetRepo->expects(self::once())
            ->method('getUrl')
            ->with(self::identicalTo('Magento_Catalog::images/product/placeholder/thumbnail.jpg'))
            ->willReturn($url);
        $imageModel = new Image(
            $this->mediaConfig,
            $this->imageContext,
            $this->encryptor,
            $filePath,
            $this->assetRepo,
            $miscParams
        );

        self::assertEquals($url, $imageModel->getUrl());
    }

    public function getPathDataProvider()
    {
        return [
            [
                '/some_file.png',
                [], //default value for miscParams
            ],
            [
                '/some_file_2.png',
                [
                    'image_type' => 'thumbnail',
                    'image_height' => 75,
                    'image_width' => 75,
                    'keep_aspect_ratio' => 'proportional',
                    'keep_frame' => 'frame',
                    'keep_transparency' => 'transparency',
                    'constrain_only' => 'doconstrainonly',
                    'background' => 'ffffff',
                    'angle' => null,
                    'quality' => 80,
                ],
            ]
        ];
    }
}
