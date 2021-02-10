<?php

namespace Dotdigitalgroup\Email\Test\Unit\Model\Message\Text;

use Dotdigitalgroup\Sms\Api\Data\SmsOrderInterface;
use Dotdigitalgroup\Sms\Model\Message\Text\Compiler;
use Dotdigitalgroup\Sms\Model\Message\Variable\Resolver;
use Dotdigitalgroup\Sms\Model\SmsOrder;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /**
     * @var Resolver
     */
    private $variableResolverMock;

    /**
     * @var Compiler
     */
    private $compiler;

    protected function setUp() :void
    {
        $this->variableResolverMock = $this->createMock(Resolver::class);
        $this->smsOrderMock = $this->createMock(SmsOrder::class);

        $this->compiler = new Compiler(
            $this->variableResolverMock
        );
    }

    public function testThatMatchesAreIdentified()
    {
        $rawText = $this->getRawText();

        $this->variableResolverMock->expects($this->exactly(5))
            ->method('resolve')
            ->willReturnOnConsecutiveCalls(
                'Chaz',
                'Kangeroo',
                '1',
                'Default Store View',
                'processing'
            );

        $compiledText = $this->compiler->compile($rawText, $this->smsOrderMock);
        $this->assertEquals($compiledText, $this->getTargetText());
    }

    private function getRawText()
    {
        // @codingStandardsIgnoreLine
        return "Thanks {{first_name}} {{last_name}}, your order {{order_id}} has been placed on {{store_name}} and is now {{order_status}}. We'll notify you when it ships.";
    }

    private function getTargetText()
    {
        // @codingStandardsIgnoreLine
        return "Thanks Chaz Kangeroo, your order 1 has been placed on Default Store View and is now processing. We'll notify you when it ships.";
    }
}
