<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Json;

use Magento\Translation\Model\Js\Config;
use Magento\Translation\Model\Js\DataProvider;
use Magento\Translation\Model\Json\PreProcessor;

class PreProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PreProcessor
     */
    protected $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderMock;

    /**
     * @var \Magento\Framework\App\AreaList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $areaListMock;

    /**
     * @var \Magento\Framework\TranslateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $designMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(\Magento\Translation\Model\Js\Config::class);
        $this->dataProviderMock = $this->createMock(\Magento\Translation\Model\Js\DataProvider::class);
        $this->areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $this->translateMock = $this->getMockForAbstractClass(\Magento\Framework\TranslateInterface::class);
        $this->designMock = $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class);
        $this->model = new PreProcessor(
            $this->configMock,
            $this->dataProviderMock,
            $this->areaListMock,
            $this->translateMock,
            $this->designMock
        );
    }

    public function testGetData()
    {
        $chain = $this->createMock(\Magento\Framework\View\Asset\PreProcessor\Chain::class);
        $asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $context = $this->createMock(\Magento\Framework\View\Asset\File\FallbackContext::class);
        $fileName = 'js-translation.json';
        $targetPath = 'path/js-translation.json';
        $themePath = '*/*';
        $dictionary = ['hello' => 'bonjour'];
        $areaCode = 'adminhtml';
        $area = $this->createMock(\Magento\Framework\App\Area::class);

        $chain->expects($this->once())
            ->method('getTargetAssetPath')
            ->willReturn($targetPath);
        $this->configMock->expects($this->once())
            ->method('getDictionaryFileName')
            ->willReturn($fileName);
        $chain->expects($this->once())
            ->method('getAsset')
            ->willReturn($asset);
        $asset->expects($this->once())
            ->method('getContext')
            ->willReturn($context);
        $context->expects($this->once())
            ->method('getThemePath')
            ->willReturn($themePath);
        $context->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($areaCode);
        $context->expects($this->once())
            ->method('getLocale')
            ->willReturn('en_US');

        $this->designMock->expects($this->once())->method('setDesignTheme')->with($themePath, $areaCode);

        $this->areaListMock->expects($this->once())
            ->method('getArea')
            ->with($areaCode)
            ->willReturn($area);

        $this->dataProviderMock->expects($this->once())
            ->method('getData')
            ->with($themePath)
            ->willReturn($dictionary);
        $chain->expects($this->once())
            ->method('setContent')
            ->with(json_encode($dictionary));
        $chain->expects($this->once())
            ->method('setContentType')
            ->with('json');

        $this->translateMock->expects($this->once())->method('setLocale')->with('en_US')->willReturnSelf();
        $this->translateMock->expects($this->once())->method('loadData')->with($areaCode, true);

        $this->model->process($chain);
    }
}
