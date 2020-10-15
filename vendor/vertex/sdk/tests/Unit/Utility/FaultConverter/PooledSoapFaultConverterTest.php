<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Unit\Utility\FaultConverter;

use PHPUnit\Framework\TestCase;
use Vertex\Exception\ApiException;
use Vertex\Utility\FaultConverter\PooledSoapFaultConverter;
use Vertex\Utility\SoapFaultConverterInterface;

/**
 * Tests the functionality of the Pooled Converter
 */
class PooledSoapFaultConverterTest extends TestCase
{
    /**
     * Test that the pooled converter will call all ->convert methods if one doesn't return an Exception
     *
     * @return void
     */
    public function testAllConvertersCalledWhenAllReturnNull()
    {
        $amt = rand(5, 15);
        $converters = [];
        for ($i = 0; $i < $amt; ++$i) {
            $converter = $this->getMockBuilder(SoapFaultConverterInterface::class)
                ->getMockForAbstractClass();

            $converter->expects($this->once())
                ->method('convert')
                ->willReturn(null);

            $converters[] = $converter;
        }

        $fault = new \SoapFault('Server', 'Test');
        $pooledConverter = new PooledSoapFaultConverter($converters);
        $pooledConverter->convert($fault);
    }

    /**
     * Test that non-SoapFaultConverterInterfaces cause errors when supplied as constructor parameters
     *
     * @return void
     */
    public function testExceptionThrownIfNonConverterProvidedInConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $nonConverter = new \stdClass();

        new PooledSoapFaultConverter([$nonConverter]);
    }

    /**
     * Test that SoapFaultConverterInterfaces do not cause errors when supplied as constructor parameters
     *
     * @return void
     */
    public function testNoExceptionThrownWhenConstructorGivenOnlyConverters()
    {
        $this->expectNotToPerformAssertions();

        $converter = $this->getMockBuilder(SoapFaultConverterInterface::class)
            ->getMockForAbstractClass();

        new PooledSoapFaultConverter([$converter]);
    }

    /**
     * Test that the pooled converter will stop calling convert methods if one returns an Exception
     *
     * @return void
     */
    public function testOnlyCallsUntilExceptionIsGiven()
    {
        $converter1 = $this->getMockBuilder(SoapFaultConverterInterface::class)
            ->getMockForAbstractClass();
        $converter1->expects($this->once())
            ->method('convert')
            ->willReturn(null);

        $converter2 = $this->getMockBuilder(SoapFaultConverterInterface::class)
            ->getMockForAbstractClass();
        $converter2->expects($this->once())
            ->method('convert')
            ->willReturn(new ApiException('Test'));

        $converter3 = $this->getMockBuilder(SoapFaultConverterInterface::class)
            ->getMockForAbstractClass();
        $converter3->expects($this->never())
            ->method('convert');

        $fault = new \SoapFault('Server', 'Test');
        $pooledConverter = new PooledSoapFaultConverter([$converter1, $converter2, $converter3]);
        $pooledConverter->convert($fault);
    }
}
