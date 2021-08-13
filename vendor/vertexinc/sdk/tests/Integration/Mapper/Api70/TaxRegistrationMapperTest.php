<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Api70;

use PHPUnit\Framework\TestCase;
use Vertex\Data\TaxRegistration;
use Vertex\Data\TaxRegistrationInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api70\TaxRegistrationMapper;
use Vertex\Mapper\MapperFactory;

/**
 * Tests for {@see TaxRegistrationMapper}
 */
class TaxRegistrationMapperTest extends TestCase
{
    /** @var TaxRegistrationMapper */
    private $mapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $factory = new MapperFactory();
        $this->mapper = $factory->getForClass(TaxRegistrationInterface::class, '70');
    }

    /**
     * Get Registration data for testings
     *
     * @return array
     */
    public function getRegistrationData()
    {
        $registration1 = new \stdClass();
        $registration1->impositionType = 'Use';

        $registration2 = new \stdClass();
        $registration2->impositionType = 'VAT';

        $registration3 = new \stdClass();

        return [
            [
                'Use',
                $registration1
            ],
            [
                'VAT',
                $registration2
            ],
            [
                null,
                $registration3
            ],
        ];
    }

    /**
     * Test {@see TaxRegistrationMapper::build()}
     *
     * @dataProvider getRegistrationData
     * @param string|null $imposition
     * @param \stdClass $mapping
     * @return void
     */
    public function testBuild(
        $imposition,
        \stdClass $mapping
    ) {
        $object = $this->mapper->build($mapping);

        $this->assertEquals($imposition, $object->getImpositionType());
    }

    /**
     * Test {@see TaxRegistrationMapper::map()}
     *
     * @return void
     * @throws ValidationException
     */
    public function testMap()
    {
        $object = new TaxRegistration();
        $object->setImpositionType('VAT');

        $map = $this->mapper->map($object);

        $this->assertEquals('VAT', $map->impositionType);
    }
}
