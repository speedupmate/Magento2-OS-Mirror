<?php

namespace Klarna\Core\Logger;

use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass  \Klarna\Core\Logger\Logger
 */
class LoggerTest extends TestCase
{
    /** @var Cleanser | \PHPUnit_Framework_MockObject_MockObject */
    private $cleanserMock;
    /** @var Logger */
    private $model;
    /** @var string */
    private $mockName;
    /** @var \Magento\Framework\App\Config | \PHPUnit_Framework_MockObject_MockObject */
    private $configMock;
    /** @var \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $mockStoreManager;
    /** @var array */
    private $mockHandlers;
    /** @var array */
    private $mockProcessors;

    /**
     * @covers ::addRecord()
     */
    public function testWillAddRecord()
    {
        $data = 'Testing True';
        $this->configMock->expects(static::once())->method('isSetFlag')->with(
            'klarna/api/debug',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(true);
        static::assertTrue($this->model->addRecord(200, $data, []));
    }

    /**
     * @covers ::addRecord()
     */
    public function testWillNotAddRecord()
    {
        $data = 'Testing False';
        $this->configMock->expects(static::once())->method('isSetFlag')->with(
            'klarna/api/debug',
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(false);
        static::assertFalse($this->model->addRecord(200, $data, []));
    }

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->mockStoreManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['isSetFlag'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cleanserMock = $this->getMockBuilder(\Klarna\Core\Logger\Cleanser::class)
            ->setMethods(['checkForSensitiveData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockHandlers = [];
        $this->mockProcessors = [];
        $this->model = $objectManager->getObject(
            Logger::class,
            [
                'cleanser'      => $this->cleanserMock,
                'config'        => $this->configMock,
                '$storeManager' => $this->mockStoreManager,
                'handlers'      => $this->mockHandlers,
                'processors'    => $this->mockProcessors
            ]
        );
    }
}
