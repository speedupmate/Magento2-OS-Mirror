<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoCache all disabled
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /** @var \Magento\Framework\View\FileSystem $viewFileSystem */
        $viewFileSystem = $this->getMock(
            'Magento\Framework\View\FileSystem',
            ['getLocaleFileName'],
            [],
            '',
            false
        );

        $viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will(
                $this->returnValue(
                    dirname(__DIR__) . '/Translation/Model/_files/Magento/design/Magento/theme/i18n/en_US.csv'
                )
            );

        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->getMock('Magento\Framework\View\Design\ThemeInterface', []);
        $theme->expects($this->once())->method('getThemePath')->will($this->returnValue('Magento/luma'));

        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($viewFileSystem, 'Magento\Framework\View\FileSystem');

        /** @var $moduleReader \Magento\Framework\Module\Dir\Reader */
        $moduleReader = $objectManager->get('Magento\Framework\Module\Dir\Reader');
        $moduleReader->setModuleDir(
            'Magento_Store',
            'i18n',
            dirname(__DIR__) . '/Translation/Model/_files/Magento/Store/i18n'
        );
        $moduleReader->setModuleDir(
            'Magento_Catalog',
            'i18n',
            dirname(__DIR__) . '/Translation/Model/_files/Magento/Catalog/i18n'
        );

        /** @var \Magento\Theme\Model\View\Design $designModel */
        $designModel = $this->getMock(
            'Magento\Theme\Model\View\Design',
            ['getDesignTheme'],
            [
                $objectManager->get('Magento\Store\Model\StoreManagerInterface'),
                $objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory'),
                $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Theme\Model\ThemeFactory'),
                $objectManager->get('Magento\Framework\ObjectManagerInterface'),
                $objectManager->get('Magento\Framework\App\State'),
                ['frontend' => 'Test/default']
            ]
        );

        $designModel->expects($this->once())->method('getDesignTheme')->will($this->returnValue($theme));

        $objectManager->addSharedInstance($designModel, 'Magento\Theme\Model\View\Design\Proxy');

        $model = $objectManager->create('Magento\Framework\Translate');
        $objectManager->addSharedInstance($model, 'Magento\Framework\Translate');
        $objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Composite');
        $objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Translate');
        \Magento\Framework\Phrase::setRenderer($objectManager->get('Magento\Framework\Phrase\RendererInterface'));
        $model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND);
    }

    /**
     * @dataProvider translateDataProvider
     */
    public function testTranslate($inputText, $expectedTranslation)
    {
        $actualTranslation = new \Magento\Framework\Phrase($inputText);
        $this->assertEquals($expectedTranslation, $actualTranslation);
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return [
            ['', ''],
            [
                'Theme phrase will be translated',
                'Theme phrase is translated',
            ],
            [
                'Magento_Store module phrase will be translated',
                'Magento_Store module translated phrase',
            ],
            [
                'Magento_Catalog module phrase will be translated',
                'Magento_Catalog module translated phrase',
            ],
            [
                'Phrase in Magento_Store module that doesn\'t need translation',
                'Phrase in Magento_Store module that doesn\'t need translation',
            ],
            [
                'Phrase in Magento_Catalog module that doesn\'t need translation',
                'Phrase in Magento_Catalog module that doesn\'t need translation',
            ],
            [
                'Magento_Store module phrase will be override by theme translation',
                'Magento_Store module phrase is override by theme translation',
            ],
            [
                'Magento_Catalog module phrase will be override by theme translation',
                'Magento_Catalog module phrase is override by theme translation',
            ],
        ];
    }
}
