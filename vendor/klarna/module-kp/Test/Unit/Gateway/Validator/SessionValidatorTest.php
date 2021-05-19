<?php
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 *
 */

namespace Klarna\Kp\Tests\Unit\Gateway;

use Klarna\Kp\Test\Unit\Mock\MockFactory;
use Klarna\Kp\Test\Unit\Mock\TestObjectFactory;
use PHPUnit\Framework\TestCase;
use Klarna\Kp\Gateway\Validator\SessionValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * @coversDefaultClass Klarna\Kp\Gateway\Validator\SessionValidator
 */
class SessionValidatorTest extends TestCase
{
    /**
     * @var SessionValidator
     */
    private $validator;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var ResultInterface|MockObject
     */
    private $resultInterface;

    /**
     * Sends expected argument and returns result depending on if Merchant ID or secret is set.
     *
     * @dataProvider validationCaseProvider
     * @param string $mid
     * @param bool   $expectedValidationArgument
     * @covers ::validate
     */
    public function testValidate(string $mid, bool $expectedValidationArgument): void
    {
        $expected = $this->resultInterface;

        $this->dependencyMocks['config']->method('getValue')->willReturn($mid);
        $this->dependencyMocks['resultFactory']->method('create')->willReturn($expected);
        $this->dependencyMocks['resultFactory']->expects(static::once())
            ->method('create')
            ->willReturnCallback(function($argument) use ($expected, $expectedValidationArgument) {
                if ($argument['isValid'] !== $expectedValidationArgument) {
                    static::fail(
                        sprintf(
                            "Array key 'isValid' was expected to be %s but %s was given",
                            var_export($expectedValidationArgument, true), var_export($argument['isValid'], true)
                        )
                    );
                }
                return $expected;
            });

        $actual = $this->validator->validate([]);
        static::assertSame($expected, $actual);
    }

    public function validationCaseProvider()
    {
        return [
            ['MID000001', true],
            ['', false]
        ];
    }

    /**
     * Basic setup for test
     */
    protected function setUp(): void
    {
        $mockFactory           = new MockFactory();
        $objectFactory         = new TestObjectFactory($mockFactory);
        $this->validator       = $objectFactory->create(SessionValidator::class, [
            ResultInterfaceFactory::class => ['create']
        ]);
        $this->dependencyMocks = $objectFactory->getDependencyMocks();
        $this->resultInterface = $mockFactory->create(ResultInterface::class);
    }
}
