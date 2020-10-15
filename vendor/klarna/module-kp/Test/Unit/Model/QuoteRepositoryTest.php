<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kp\Model;

use Klarna\Kp\Api\QuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @coversDefaultClass \Klarna\Kp\Model\QuoteRepository
 */
class QuoteRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteRepositoryInterface|\Klarna\Kp\Model\QuoteRepository
     */
    protected $model;

    /**
     * @var QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \Klarna\Kp\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Klarna\Kp\Model\ResourceModel\Quote\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteCollectionMock;

    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mageQuoteMock;

    /**
     * @var \Klarna\Kp\Model\ResourceModel\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteResourceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var LoadHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mageLoadHandlerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @covers                   ::getById()
     * @covers                   ::loadQuote()
     */
    public function testGetByIdWithException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class, 'No such entity with payments_quote_id = 14');

        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(false);

        $this->model->getById($cartId);
    }

    /**
     *
     * @covers ::getById()
     * @covers ::cacheInstance()
     * @covers ::loadQuote()
     * @covers \Klarna\Kp\Model\Quote::getSessionId()
     * @covers \Klarna\Kp\Model\Quote::getClientToken()
     */
    public function testGetById()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($cartId);

        static::assertEquals($this->quoteMock, $this->model->getById($cartId));
        static::assertEquals($this->quoteMock, $this->model->getById($cartId));
    }

    /**
     * @covers ::loadQuote()
     * @dataProvider loadQuoteProvider
     */
    public function testLoadQuote($klarnaQuote, $klarnaQuoteId)
    {
        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($klarnaQuoteId)
            ->willReturn($klarnaQuote);
        $this->quoteMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($klarnaQuoteId);

        $quote = $this->model->loadQuote('load', 'payments_quote_id', $klarnaQuoteId);

        static::assertEquals($quote->getId(), $klarnaQuoteId);
    }

    public function loadQuoteProvider()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Klarna\Kp\Model\Quote $klarnaQuoteMock */
        $klarnaQuoteMock = $objectManager->getObject(\Klarna\Kp\Model\Quote::class);

        return [
            [
                $klarnaQuoteMock->setData([
                    'payments_quote_id'   => 14,
                    'session_id'          => '126e4109-c3ce-5c49-ac25-c5353c68e6b1',
                    // @codingStandardsIgnoreLine
                    'client_token'        => 'eyJhbGciOiJub25lIn0.ewogICJzZXNzaW9uX2lkIiA6ICIxMjZlNDEwOS1jM2NlLTVjNDktYWMyNS1jNTM1M2M2OGU2YjEiLAogICJiYXNlX3VybCIgOiAiaHR0cHM6Ly9jcmVkaXQtbmEucGxheWdyb3VuZC5rbGFybmEuY29tIiwKICAiZGVzaWduIiA6ICJrbGFybmEiLAogICJsYW5ndWFnZSIgOiAiZW4iLAogICJwdXJjaGFzZV9jb3VudHJ5IiA6ICJVUyIsCiAgInRyYWNlX2Zsb3ciIDogZmFsc2UKfQ.',
                    'authorization_token' => null,
                    'is_active'           => 1,
                    'quote_id'            => 21,
                    'payment_methods'     => null
                ]),
                14
            ]
        ];
    }

    /**
     * @covers                   ::loadQuote()
     */
    public function testLoadQuoteWithException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class, 'No such entity with payments_quote_id = 14');

        $quoteId = 14;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($quoteId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('getId')
            ->willReturn(false);

        static::assertEquals(
            $this->quoteMock,
            $this->model->loadQuote('load', 'payments_quote_id', $quoteId)
        );
    }

    /**
     * @covers                   ::getActiveByQuote()
     * @dataProvider             activeQuoteWithExceptionProvider
     */
    public function testGetActiveByQuoteWithException($mageQuoteMock)
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class, 'No such entity with quote_id =');

        $this->model->getActiveByQuote($mageQuoteMock);
    }

    public function activeQuoteWithExceptionProvider()
    {
        $mageQuoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            [$mageQuoteMock]
        ];
    }

    /**
     * @covers ::getActiveByQuote()
     */
    public function testGetActiveByQuote()
    {
        $klarnaQuoteId = 14;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($klarnaQuoteId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($klarnaQuoteId);
        $this->quoteResourceMock->expects(static::once())
            ->method('getActiveByQuote')
            ->willReturn($klarnaQuoteId);

        $klarnaQuote = $this->model->getActiveByQuote($this->mageQuoteMock);

        static::assertEquals($klarnaQuote->getId(), $klarnaQuoteId);
    }

    /**
     * @covers ::getActiveByQuoteId()
     */
    public function testGetActiveByQuoteId()
    {
        $klarnaQuoteId = 14;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($klarnaQuoteId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::exactly(2))
            ->method('getId')
            ->willReturn($klarnaQuoteId);

        $klarnaQuote = $this->model->getActiveByQuoteId($klarnaQuoteId);

        static::assertEquals($klarnaQuote->getId(), $klarnaQuoteId);
    }

    /**
     * @covers ::save()
     */
    public function testSave()
    {
        $this->quoteResourceMock->expects($this->once())
            ->method('save')
            ->willReturn($this->quoteMock);

        $this->model->save($this->quoteMock);
    }

    /**
     * @covers                   ::save()
     */
    public function testSaveWithException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class, 'No such entity with payments_quote_id =');

        $exceptionMessage = 'No such entity with payments_quote_id = ';
        $this->quoteResourceMock->expects(static::once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->model->save($this->quoteMock);
    }

    /**
     * @covers ::delete()
     * @covers \Klarna\Kp\Model\Quote::getSessionId()
     * @covers \Klarna\Kp\Model\Quote::getClientToken()
     */
    public function testDelete()
    {
        $this->quoteMock->expects($this->exactly(1))->method('getId')->willReturn(1);
        $this->quoteResourceMock->expects($this->once())
            ->method('delete');

        $this->model->delete($this->quoteMock);
    }

    /**
     * @covers ::deleteById()
     * @covers ::loadQuote()
     * @covers ::delete()
     * @covers ::getById()
     * @covers ::cacheInstance()
     * @covers \Klarna\Kp\Model\Quote::getSessionId()
     * @covers \Klarna\Kp\Model\Quote::getClientToken()
     */
    public function testDeleteById()
    {
        $quoteId = 14;

        $this->quoteFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects(static::once())
            ->method('load')
            ->with($quoteId)
            ->willReturn($this->quoteMock);

        $this->quoteResourceMock->expects($this->once())
            ->method('delete');
        $this->quoteMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($quoteId);

        $this->model->deleteById($quoteId);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->mageQuoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'load',
                'loadByIdWithoutStore',
                'loadByCustomer',
                'getIsActive',
                'getId',
                '__wakeup',
                'save',
                'delete',
                'getStoreId',
                'getData'
            ]
        );
        $this->mageLoadHandlerMock = $this->createMock(\Magento\Quote\Model\QuoteRepository\LoadHandler::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->quoteResourceMock = $this->getMockBuilder(\Klarna\Kp\Model\ResourceModel\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActiveByQuote', 'save', 'delete'])
            ->getMock();

        $this->quoteFactoryMock = $this->getMockBuilder(\Klarna\Kp\Model\QuoteFactory::class)->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(\Klarna\Kp\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                'getId',
                '__wakeup',
                'setClientToken'
            ])
            ->getMock();

        $apiMock = $this->createMock(\Klarna\Kp\Model\Api\Rest\Service\Payments::class);
        $this->model = $objectManager->getObject(
            \Klarna\Kp\Model\QuoteRepository::class,
            [
                'quoteFactory'  => $this->quoteFactoryMock,
                'resourceModel' => $this->quoteResourceMock,
                'api'           => $apiMock
            ]
        );
    }
}
