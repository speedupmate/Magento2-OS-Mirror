<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Core\Test\Unit\Ui\DataProvider;

use Magento\User\Model\User;
use Klarna\Core\Ui\DataProvider\Support;
use Klarna\Core\Test\Unit\Mock\MockFactory;
use Klarna\Core\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Core\Ui\DataProvider\Logs
 */
class SupportTest extends TestCase
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var Support
     */
    private $dataProvider;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::getData()
     */
    public function testGetDataReturnsAdminUserData(): void
    {
        $expected = [
            'new' => [
                'contact_name' => 'admin',
                'contact_email'   => 'test@klarna.de'
            ]
        ];

        $this->user->method('getName')
            ->willReturn('admin');
        $this->user->method('getEmail')
            ->willReturn('test@klarna.de');
        $this->dependencyMocks['auth']->method('getUser')
            ->willReturn($this->user);

        $actual = $this->dataProvider->getData();

        static::assertEquals($actual, $expected);
    }

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->dataProvider    = $objectFactory->create(Support::class);
        $this->user            = $mockFactory->create(User::class);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
    }
}
