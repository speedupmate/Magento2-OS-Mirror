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

namespace Klarna\Core\Test\Unit\Logger\Api;

use Klarna\Core\Logger\Api\Container;
use Klarna\Core\Logger\Api\Update;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\Core\Model\Log;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @coversDefaultClass \Klarna\Core\Logger\Api\Update
 */
class UpdateTest extends TestCase
{
    /**
     * @var Update
     */
    private $updateLogger;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var MockFactory
     */
    private $mockFactory;
    /**
     * @var Container
     */
    private $container;

    /**
     * @doesNotPerformAssertions
     * @covers ::addEntry
     */
    public function testAddEntryDataIsAnonymized(): void
    {
        $request = [
            'billing_address' => [
                'lastname' => 'my last name'
            ],
            'shipping_address' => [
                'lastname' => 'my last name'
            ]
        ];
        $response = array_merge(['response_status_code' => 1], $request);

        $this->container->method('getRequest')
            ->willReturn($request);
        $this->container->method('getResponse')
            ->willReturn($response);

        $logModel = $this->mockFactory->create(Log::class);
        $logModel->method('setRequest')
            ->with(json_encode($request));
        $logModel->method('setResponse')
            ->with(json_encode($response));

        $this->dependencyMocks['json']->method('serialize')
            ->will($this->returnCallback(function ($value) {
                return json_encode($value);
            }));
        $this->dependencyMocks['cleanser']->method('checkForSensitiveData')
            ->will($this->returnCallback(function ($value) {
                return $value;
            }));

        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($logModel);
        $this->updateLogger->addEntry($this->container);
    }

    /**
     * @doesNotPerformAssertions
     * @covers ::addEntry
     */
    public function testAddEntryDataIsNotAnonymized(): void
    {
        $request = [
            'billing_address' => [
                'lastname' => 'my last name'
            ],
            'shipping_address' => [
                'lastname' => 'my last name'
            ]
        ];
        $response = array_merge(['response_status_code' => 1], $request);

        $this->container->method('getRequest')
            ->willReturn($request);
        $this->container->method('getResponse')
            ->willReturn($response);

        $logModel = $this->mockFactory->create(Log::class);
        $logModel->method('setRequest')
            ->with(json_encode($request));
        $logModel->method('setResponse')
            ->with(json_encode($response));

        $this->dependencyMocks['config']->method('isSetFlag')
            ->willReturn(true);

        $this->dependencyMocks['json']->method('serialize')
            ->will($this->returnCallback(function ($value) {
                return json_encode($value);
            }));

        $this->dependencyMocks['logFactory']->method('create')
            ->willReturn($logModel);

        $this->dependencyMocks['cleanser']->expects(static::never())
            ->method('checkForSensitiveData');
        $this->updateLogger->addEntry($this->container);
    }

    protected function setUp(): void
    {
        $this->mockFactory     = new MockFactory();
        $objectFactory         = new TestObjectFactory($this->mockFactory);
        $this->updateLogger    = $objectFactory->create(Update::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $this->container = $this->mockFactory->create(Container::class);
    }
}
