<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Address;
use Vertex\Data\TaxRegistration;
use Vertex\Data\TaxRegistrationInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\TaxRegistrationMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see TaxRegistrationMapper}
 */
class TaxRegistrationMapperTest extends TestCase
{
    /**
     * Get Registration data for testings
     *
     * @return array
     */
    public function getRegistrationData()
    {
        $registration1Num = rand(0, 100);
        $registration1 = new \stdClass();
        $registration1->PhysicalLocation = new \stdClass();
        $registration1->PhysicalLocation->City = 'West Chester';
        $registration1->TaxRegistrationNumber = $registration1Num;
        $registration1->isoCountryCode = 'USA';
        $registration1->mainDivision = 'CA';
        $registration1->hasPhysicalPresenceIndicator = true;

        $registration2Num = rand(0, 100);
        $registration2 = new \stdClass();
        $loc = new \stdClass();
        $loc->City = 'King of Prussia';
        $registration2->PhysicalLocation = [$loc];
        $registration2->TaxRegistrationNumber = new \stdClass();
        $registration2->TaxRegistrationNumber->_ = $registration2Num;
        $registration2->isoCountryCode = 'CAN';
        $registration2->mainDivision = 'ON';
        $registration2->hasPhysicalPresenceIndicator = false;

        $registration3 = new \stdClass();

        return CommonMapperProvider::getAllMappersWithProvidedData(
            TaxRegistrationInterface::class,
            [
                'Use Registration' => [
                    'West Chester',
                    $registration1Num,
                    'USA',
                    'CA',
                    true,
                    $registration1
                ],
                'VAT Registration' => [
                    'King of Prussia',
                    $registration2Num,
                    'CAN',
                    'ON',
                    false,
                    $registration2
                ],
                'No Registration' => [
                    null,
                    null,
                    null,
                    null,
                    null,
                    $registration3
                ],
            ]
        );
    }

    /**
     * Test {@see TaxRegistrationMapper::build()}
     *
     * @dataProvider getRegistrationData
     * @param TaxRegistrationMapperInterface $mapper
     * @param string|null $city
     * @param string|null $registrationNumber
     * @param string|null $countryCode
     * @param string|null $mainDivision
     * @param string|null $physicalPresence
     * @param \stdClass|null $mapping
     * @return void
     */
    public function testBuild(
        TaxRegistrationMapperInterface $mapper,
        $city = null,
        $registrationNumber = null,
        $countryCode = null,
        $mainDivision = null,
        $physicalPresence = null,
        \stdClass $mapping = null
    ) {
        $object = $mapper->build($mapping);

        if ($city === null) {
            $this->assertEmpty($object->getPhysicalLocations());
        } else {
            $this->assertEquals($city, $object->getPhysicalLocations()[0]->getCity());
        }

        $this->assertEquals($registrationNumber, $object->getRegistrationNumber());
        $this->assertEquals($countryCode, $object->getCountryCode());
        $this->assertEquals($mainDivision, $object->getMainDivision());
        $this->assertEquals($physicalPresence, $object->hasPhysicalPresence());
    }

    /**
     * Test {@see TaxRegistrationMapper::map()}
     *
     * @dataProvider getRegistrationData
     * @param TaxRegistrationMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(TaxRegistrationMapperInterface $mapper)
    {
        $registrationNumber = rand(0, 100);

        $object = new TaxRegistration();
        $address = new Address();
        $address->setCity('West Chester');
        $object->setPhysicalLocations([$address]);
        $object->setRegistrationNumber($registrationNumber);
        $object->setCountryCode('CAN');
        $object->setMainDivision('ON');
        $object->setHasPhysicalPresence(true);
        $object->setImpositionType('VAT');

        $map = $mapper->map($object);

        $this->assertIsArray($map->PhysicalLocation);
        $this->assertCount(1, $map->PhysicalLocation);
        $this->assertEquals('West Chester', $map->PhysicalLocation[0]->City);
        $this->assertEquals($registrationNumber, $map->TaxRegistrationNumber);
        $this->assertEquals('CAN', $map->isoCountryCode);
        $this->assertEquals('ON', $map->mainDivision);
        $this->assertEquals(true, $map->hasPhysicalPresenceIndicator);
    }
}
