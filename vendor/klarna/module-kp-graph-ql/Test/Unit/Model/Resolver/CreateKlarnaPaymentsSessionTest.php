<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\KpGraphQl\Test\Unit\Model\Resolver;

use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\Kp\Model\Api\Response;
use Klarna\KpGraphQl\Model\Resolver\CreateKlarnaPaymentsSession;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\KpGraphQl\Model\Resolver\CreateKlarnaPaymentsSession
 */
class CreateKlarnaPaymentsSessionTest extends TestCase
{
    /**
     * @var CreateKlarnaPaymentsSession|MockObject
     */
    private $resolver;
    /**
     * @var Field|MockObject
     */
    private $fieldMock;
    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;
    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;
    /**
     * @var Response|MockObject
     */
    private $responseMock;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * Test mutation when cart isn't provided.
     *
     * @covers:: resolve()
     */
    public function testCartIdIsMissing(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Required parameter 'cart_id' is missing");
        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['input' => ['cart_id' => '']]
        );
    }

    /**
     * Test mutation when Klarna Payments is not active.
     *
     * @covers:: resolve()
     */
    public function testKlarnaPaymentsIsNotActive(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Klarna Payments method is not active');
        $this->dependencyMocks['configHelper']
            ->expects($this->once())
            ->method('isPaymentConfigFlag')
            ->willReturn(false);

        $maskedCartId = 'dJZiNPYrSgbglDuCFs1q4zk0CYn7iXC9';

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['input' => ['cart_id' => $maskedCartId]]
        );
    }

    /**
     * Test mutation when provided cart id isn't matching a real cart.
     *
     * @covers:: resolve()
     * @dataProvider invalidResponse
     * @param string $maskedCartId
     * @param int    $currentUserId
     * @param int    $storeId
     * @param int    $cartId
     * @throws \Exception
     */
    public function testWrongCartIdIsProvided(string $maskedCartId, int $currentUserId, int $storeId, int $cartId): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage("Could not find a cart with ID '${maskedCartId}'");

        $this->dependencyMocks['configHelper']
            ->expects($this->once())
            ->method('isPaymentConfigFlag')
            ->willReturn(true);

        $this->mockEverything($maskedCartId, $currentUserId, $storeId, $cartId, false);

        $this->responseMock
            ->expects($this->once())
            ->method('getResponseStatusMessage')
            ->willReturn("Could not find a cart with ID '${maskedCartId}'");

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['input' => ['cart_id' => $maskedCartId]]
        );
    }

    /**
     * Test mutation when API response was not successful.
     *
     * @covers:: resolve()
     * @dataProvider validResponse
     * @param string $maskedCartId
     * @param int    $currentUserId
     * @param int    $storeId
     * @param int    $cartId
     * @throws \Exception
     */
    public function testUnsuccessfulResponse(string $maskedCartId, int $currentUserId, int $storeId, int $cartId): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->dependencyMocks['configHelper']
            ->expects($this->once())
            ->method('isPaymentConfigFlag')
            ->willReturn(true);

        $this->mockEverything($maskedCartId, $currentUserId, $storeId, $cartId, false);

        $this->responseMock
            ->expects($this->once())
            ->method('getResponseStatusMessage')
            ->willReturn("Could not find a cart with ID '${maskedCartId}'");

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['input' => ['cart_id' => $maskedCartId]]
        );
    }

    /**
     * Test mutation when API response was successful.
     *
     * @covers:: resolve()
     * @dataProvider validResponse
     * @param string $maskedCartId
     * @param int    $currentUserId
     * @param int    $storeId
     * @param int    $cartId
     * @throws \Exception
     */
    public function testSuccessfulResponse(string $maskedCartId, int $currentUserId, int $storeId, int $cartId): void
    {
        $this->dependencyMocks['configHelper']
            ->expects($this->once())
            ->method('isPaymentConfigFlag')
            ->willReturn(true);

        $this->mockEverything($maskedCartId, $currentUserId, $storeId, $cartId, true);

        $this->responseMock
            ->expects($this->once())
            ->method('getClientToken')
            ->willReturn('eyJhbGciOiJSUzI1NiIsImtpZCI6IjgyMzA1s');

        $this->responseMock
            ->expects($this->once())
            ->method('getPaymentMethodCategories')
            ->willReturn([
                [
                    'identifier' => 'pay_later',
                    'name'       => 'Pay later in 30 days',
                ]
            ]);

        $expected = [
            'client_token'              => 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjgyMzA1s',
            'payment_method_categories' => [
                [
                    'identifier' => 'pay_later',
                    'name'       => 'Pay later in 30 days',
                ]
            ]
        ];
        $actual   = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            null,
            ['input' => ['cart_id' => $maskedCartId]]
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $maskedCartId
     * @param int    $currentUserId
     * @param int    $storeId
     * @param int    $cartId
     * @param bool   $success
     */
    private function mockEverything(
        string $maskedCartId,
        int $currentUserId,
        int $storeId,
        int $cartId,
        bool $success
    ): void {
        $cartMock = $this->createMock(Quote::class);
        $this->dependencyMocks['getCartForUser']
            ->expects($this->once())
            ->method('execute')
            ->with(
                $maskedCartId,
                $currentUserId,
                $storeId
            )
            ->willReturn($cartMock);

        $cartMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $this->dependencyMocks['session']
            ->expects($this->once())
            ->method('initWithCartId')
            ->with($cartId)
            ->willReturn($this->responseMock);

        $this->contextMock
            ->expects($this->once())
            ->method('getUserId')
            ->willReturn($currentUserId);

        $this->responseMock
            ->expects($this->once())
            ->method('isSuccessfull')
            ->willReturn($success);
    }

    public function validResponse(): array
    {
        return [
            [
                'dJZiNPYrSgbglDuCFs1q4zk0CYn7iXC9', // maskedCartId
                1,                                  // userId
                1,                                  // storeId
                1                                   // cartId
            ]
        ];
    }

    public function invalidResponse(): array
    {
        return [
            [
                'dJZiNPYrSgbglDuCFs1q4zk0CYn7iXC9', // maskedCartId
                1,                                  // userId
                1,                                  // storeId
                2                                   // cartId
            ]
        ];
    }

    protected function setUp(): void
    {
        $mockFactory   = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->resolver        = $objectFactory->create(CreateKlarnaPaymentsSession::class);
        $this->fieldMock       = $mockFactory->create(Field::class);
        $this->resolveInfoMock = $mockFactory->create(ResolveInfo::class);
        $this->contextMock     = $mockFactory->create(ContextInterface::class);
        $this->responseMock    = $mockFactory->create(Response::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $contextExtensionMock = $mockFactory->create(
            ContextExtensionInterface::class,
            ['getStore', 'setStore', 'getIsCustomer', 'setIsCustomer']
        );
        $storeInterfaceMock   = $mockFactory->create(StoreInterface::class);

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($contextExtensionMock);

        $contextExtensionMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($storeInterfaceMock);

        $storeInterfaceMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
    }
}
