<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Login;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\AuthenticatorInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see Authenticator}
 */
class AuthenticatorTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(AuthenticatorInterface::class);
    }

    /**
     * Test {@see Authenticator::addLogin()}
     *
     * @dataProvider provideMappers
     * @param AuthenticatorInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testAddLogin(AuthenticatorInterface $mapper)
    {
        $trustedId = uniqid('', false);
        $map = new \stdClass();

        $login = new Login();
        $login->setTrustedId($trustedId);

        $map = $mapper->addLogin($map, $login);

        $this->assertEquals($trustedId, $map->Login->TrustedId);
    }
}
