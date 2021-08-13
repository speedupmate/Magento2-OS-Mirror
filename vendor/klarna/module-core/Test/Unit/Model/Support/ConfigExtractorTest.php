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

use Klarna\Core\Model\Support\ConfigExtractor;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Model\Support\ConfigExtractor
 */
class ConfigExtractorTest extends TestCase
{
    /**
     * @var ConfigExtractor
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getConfig()
     */
    public function testGetConfigSortKeys(): void
    {
        $this
            ->dependencyMocks['config']
            ->method('getValue')
            ->willReturn([
                'a' => [
                    'z' => null,
                    'c' => null
                ],
                '0' => [
                    'b' => null
                ]
            ]);

        $default = $this->model->getConfig();

        static::assertSame([
            '0/b' => null,
            'a/c' => null,
            'a/z' => null,
        ], $default);
    }

    /**
     * @covers ::getConfig()
     */
    public function testGetConfigPassesArgumentsCorrectly(): void
    {
        $this
            ->dependencyMocks['config']
            ->method('getValue')
            ->willReturnCallback(function ($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null) {
                return [
                    'path' => $path,
                    'scopeType' => $scopeType,
                    'id' => $scopeCode
                ];
            });

        $default = $this->model->getConfig();

        static::assertSame([
            'id' => null,
            'path' => '',
            'scopeType' => 'default'
        ], $default);

        $nonDefault = $this->model->getConfig([], [], 'customType', 987);

        static::assertSame([
            'id' => 987,
            'path' => '',
            'scopeType' => 'customType'
        ], $nonDefault);
    }

    /**
     * @covers ::getConfig()
     */
    public function testGetConfigFilterContains(): void
    {
        $this
            ->dependencyMocks['config']
            ->method('getValue')
            ->willReturn([
                'needle' => [
                    'okay' => [
                        'okay' => null
                    ]
                ],
                'okay' => [
                    'needle' => [
                        'okay' => null
                    ],
                    'okay' => [
                        'needle' => null,
                        'okay' => '99'
                    ]
                ],
            ]);

        $result = $this->model->getConfig(['needle']);

        static::assertSame([
            'needle/okay/okay' => null,
            'okay/needle/okay' => null,
            'okay/okay/needle' => null,
        ], $result);
    }

    /**
     * @covers ::getConfig()
     */
    public function testGetConfigFilterNotContains(): void
    {
        $this
            ->dependencyMocks['config']
            ->method('getValue')
            ->willReturn([
                'okay' => [
                    'okay' => [
                        'okay' => null,
                        'not_okay' => '99'
                    ],
                    'not_okay' => [
                        'okay' => '99',
                    ],
                ],
                'not_okay' => [
                    'okay' => [
                        'okay' => '99',
                    ],
                ],
            ]);

        $result = $this->model->getConfig([], ['not_okay']);

        static::assertSame([
            'okay/okay/okay' => null,
        ], $result);
    }

    /**
     * @covers ::getConfig()
     */
    public function testGetConfigWithDifferentSizePaths(): void
    {
        $this
            ->dependencyMocks['config']
            ->method('getValue')
            ->willReturn([
                '1' => '7',
                '2' => [
                    '1' => '6'
                ],
                '3' => [
                    '4' => [
                        '5' => '5'
                    ],
                ],
                '4' => [
                    '4' => [
                        '4' => [
                            '4' => '3'
                        ],
                    ],
                ],
                '5' => [
                    '5' => [
                        '5' => [
                            '5' => [
                                '5' => '2'
                            ],
                        ],
                    ],
                ],
            ]);

        $default = $this->model->getConfig();

        static::assertSame([
            '1' => '7',
            '2/1' => '6',
            '3/4/5' => '5',
            '4/4/4/4' => '3',
            '5/5/5/5/5' => '2',
        ], $default);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->model           = $objectFactory->create(ConfigExtractor::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
