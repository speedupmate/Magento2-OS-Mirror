<?php

namespace PayPal\Braintree\Test\Unit\Model\Paypal\Helper;

use InvalidArgumentException;
use PayPal\Braintree\Gateway\Config\PayPal\Config;
use PayPal\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteUpdaterTest extends TestCase
{
    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Address|MockObject
     */
    private $addressMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->quoteRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)->getMockForAbstractClass();
        $this->addressMock = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();

        $this->quoteUpdater = new QuoteUpdater(
            $this->configMock,
            $this->quoteRepositoryMock,
            $this->messageManagerMock,
            $this->addressMock
        );
    }

    public function testExecuteException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->quoteUpdater->execute('', [], $this->getQuoteMock());
    }

    /**
     * @return Quote|MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->setMethods([
                'collectTotals',
                'getBillingAddress',
                'getShippingAddress',
                'getIsVirtual'
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
