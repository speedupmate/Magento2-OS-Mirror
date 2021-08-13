<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Login;
use Vertex\Data\LoginInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\LoginMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see LoginMapper}
 */
class LoginMapperTest extends TestCase
{
    /**
     * Get Login data for testing
     *
     * @return array
     */
    public function getLoginData()
    {
        $randomString = uniqid('', false);

        $data1Map = new \stdClass();
        $data1Map->TrustedId = 'TrustedId';

        $data2Map = new \stdClass();
        $data2Map->TrustedId = $randomString;

        return CommonMapperProvider::getAllMappersWithProvidedData(
            LoginInterface::class,
            [
                ['TrustedId', $data1Map],
                [$randomString, $data2Map],
                [null, new \stdClass()],
            ]
        );
    }

    /**
     * Test {@see LoginMapper::build()}
     *
     * @dataProvider getLoginData
     * @param LoginMapperInterface $mapper
     * @param string $trustedId
     * @param \stdClass $mapping
     * @return void
     */
    public function testBuild(LoginMapperInterface $mapper, $trustedId, \stdClass $mapping)
    {
        $object = $mapper->build($mapping);
        $this->assertEquals($trustedId, $object->getTrustedId());
    }

    /**
     * Test {@see LoginMapper::map()}
     *
     * @dataProvider getLoginData
     * @param LoginMapperInterface $mapper
     * @param string $trustedId
     * @param \stdClass $expectation
     * @return void
     * @throws ValidationException
     */
    public function testMap(LoginMapperInterface $mapper, $trustedId, \stdClass $expectation)
    {
        $object = new Login();
        $object->setTrustedId($trustedId);
        $map = $mapper->map($object);

        $this->assertEquals($expectation, $map);
    }
}
