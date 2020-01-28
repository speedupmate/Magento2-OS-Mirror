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

use Klarna\Kp\Api\QuoteInterface;

/**
 * Class QuoteTest
 *
 * @package Klarna\Kp\Model
 * @coversDefaultClass \Klarna\Kp\Model\Quote
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mageQuoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var QuoteInterface
     */
    protected $quote;

    /**
     * @var \Magento\Framework\DataObject\Factory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactoryMock;

    /**
     * @covers \Klarna\Kp\Model\Quote::getIsActive()
     * @covers \Klarna\Kp\Model\Quote::isActive()
     * @covers \Klarna\Kp\Model\Quote::setIsActive()
     */
    public function testIsActiveAccessors()
    {
        $value = 1;

        $result = $this->quote->setIsActive($value)->getIsActive();
        $this->assertEquals($value, $result);
        $this->assertEquals((bool)$value, $this->quote->isActive());
    }

    /**
     * @covers \Klarna\Kp\Model\Quote::getClientToken()
     * @covers \Klarna\Kp\Model\Quote::setClientToken()
     */
    public function testClientTokenAccessors()
    {
        $value = 'KLARNA-CLIENT-TOKEN';

        $result = $this->quote->setClientToken($value)->getClientToken();
        $this->assertEquals($value, $result);
    }

    /**
     * @covers \Klarna\Kp\Model\Quote::getSessionId()
     * @covers \Klarna\Kp\Model\Quote::setSessionId()
     */
    public function testSessionIdAccessors()
    {
        $value = 'klarna-session-id';

        $result = $this->quote->setSessionId($value)->getSessionId();
        $this->assertEquals($value, $result);
    }

    /**
     * @covers \Klarna\Kp\Model\Quote::getIdentities()
     */
    public function testGetIdentities()
    {
        $value = 1;

        $this->quote->setId($value);
        $result = $this->quote->getIdentities();
        $this->assertEquals([\Klarna\Kp\Model\Quote::CACHE_TAG . '_' . $value], $result);
    }

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObject\Factory::class)
                                        ->setMethods(['create'])
                                        ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Klarna\Kp\Model\ResourceModel\Quote::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->mageQuoteMock = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);

        $this->quote = $objectManager->getObject(\Klarna\Kp\Model\Quote::class);
    }
}
