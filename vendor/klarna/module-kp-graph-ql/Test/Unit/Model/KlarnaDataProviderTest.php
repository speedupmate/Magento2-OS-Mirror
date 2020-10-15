<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\KpGraphQl\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use Klarna\KpGraphQl\Model\KlarnaDataProvider;

/**
 * @coversDefaultClass \Klarna\KpGraphQl\Model\KlarnaDataProvider
 */
class KlarnaDataProviderTest extends TestCase
{
    /**
     * @var KlarnaDataProvider|MockObject
     */
    private $klarnaDataProvider;

    /**
     * Test if the return additional data contains the authorisation_token
     *
     * @covers:: getData()
     */
    public function testAdditionalDataContainsAuthorizationToken(): void
    {
        $expected = ['authorization_token' => 'e9abc610-6748-256f-a506-355626551326'];
        $actual   = $this->klarnaDataProvider->getData([
            'code'   => 'klarna',
            'klarna' => $expected
        ]);
        static::assertEquals($expected, $actual);
    }

    /**
     * Test if an exception is thrown, when no authorisation_token is provided
     *
     * @covers:: getData()
     */
    public function testAuthorizationTokenisMissing(): void
    {
        $this->expectException('\Magento\Framework\GraphQl\Exception\GraphQlInputException');
        $this->klarnaDataProvider->getData(['code' => 'klarna', 'klarna' => ['authorization_token' => '']]);
    }

    protected function setUp(): void
    {
        $mockFactory   = new MockFactory();
        $objectFactory = new TestObjectFactory($mockFactory);

        $this->klarnaDataProvider = $objectFactory->create(KlarnaDataProvider::class);
    }
}
