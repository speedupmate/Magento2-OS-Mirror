<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Unit;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\AddressInterface;
use Vertex\Data\Configuration;
use Vertex\Data\ConfigurationInterface;
use Vertex\Data\Login;
use Vertex\Mapper\MapperFactory;

/**
 * Tests for {@see MapperFactory}
 */
class MapperFactoryTest extends TestCase
{
    /** @var MapperFactory */
    private $mapperFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperFactory = new MapperFactory(
            [
                AddressInterface::class => [
                    '60' => Address::class,
                    '70' => Login::class,
                ],
                ConfigurationInterface::class => [
                    '60' => Configuration::class
                ]
            ]
        );
    }

    /**
     * Provide data for tests
     *
     * Returns an array of arrays, where the value is the three parameters used by the below tests:
     * - Interface to query against MapperFactory
     * - Object type we expect to be returned
     * - API Level
     *
     * @return array
     */
    public function getDataForTest()
    {
        return [
            [AddressInterface::class, Address::class, '60'],
            [AddressInterface::class, Login::class, '70'],
            [ConfigurationInterface::class, Configuration::class, '60']
        ];
    }

    /**
     * Test method createForClass
     *
     * Ensure that the expected object type is returned
     *
     * @dataProvider getDataForTest
     * @param string $interface
     * @param string $result
     * @param string $apiLevel
     * @return void
     */
    public function testCreateForClass($interface, $result, $apiLevel)
    {
        $object = $this->mapperFactory->createForClass($interface, $apiLevel);
        $this->assertInstanceOf($result, $object);
    }

    /**
     * Test method getForClass
     *
     * Ensure that when called twice, the second object is the same instance as the first object
     *
     * @dataProvider getDataForTest
     * @param string $interface
     * @param string $result
     * @param string $apiLevel
     * @return void
     */
    public function testGetForClass($interface, $result, $apiLevel)
    {
        $object = $this->mapperFactory->getForClass($interface, $apiLevel);
        $this->assertInstanceOf($result, $object);
        $object2 = $this->mapperFactory->getForClass($interface, $apiLevel);
        $this->assertEquals($object, $object2);
    }
}
