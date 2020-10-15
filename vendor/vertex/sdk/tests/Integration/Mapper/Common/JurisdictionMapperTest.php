<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\Jurisdiction;
use Vertex\Data\JurisdictionInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\Api60\JurisdictionMapper;
use Vertex\Mapper\JurisdictionMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see JurisdictionMapper}
 *
 * @covers \Vertex\Mapper\Api60\JurisdictionMapper
 */
class JurisdictionMapperTest extends TestCase
{
    /**
     * Get Jurisdiction data for testing
     *
     * @return array
     */
    public function getJurisdictionData()
    {
        $randomId1 = CommonMapperProvider::randBasedOnMethodAvaialbility(
            JurisdictionMapper::JURISDICTION_ID_MIN,
            JurisdictionMapper::JURISDICTION_ID_MAX
        );
        $randomId2 = CommonMapperProvider::randBasedOnMethodAvaialbility(
            JurisdictionMapper::JURISDICTION_ID_MIN,
            JurisdictionMapper::JURISDICTION_ID_MAX
        );
        return CommonMapperProvider::getAllMappersWithProvidedData(
            JurisdictionInterface::class,
            [
                [
                    'Test Jurisdiction',
                    JurisdictionInterface::JURISDICTION_LEVEL_STATE,
                    $randomId1,
                    '2018-07-04',
                    '2020-07-04',
                    'JURISDICTION',
                ],
                [
                    uniqid('juris-', false),
                    JurisdictionInterface::JURISDICTION_LEVEL_TOWNSHIP,
                    $randomId2,
                    '2018-07-05',
                    null,
                    'JURIS'
                ]
            ]
        );
    }

    /**
     * Test {@see JurisdictionMapper::build()}
     *
     * @dataProvider getJurisdictionData
     * @param JurisdictionMapperInterface $mapper
     * @param string|null $name
     * @param string|null $level
     * @param string|null $jurisdictionId
     * @param string|null $effectiveDate
     * @param string|null $expirationDate
     * @param string|null $externalCode
     * @return void
     */
    public function testBuild(
        JurisdictionMapperInterface $mapper,
        $name = null,
        $level = null,
        $jurisdictionId = null,
        $effectiveDate = null,
        $expirationDate = null,
        $externalCode = null
    ) {
        $mapping = new \stdClass();
        $mapping->_ = $name;
        $mapping->jurisdictionLevel = $level;
        $mapping->jurisdictionId = $jurisdictionId;
        $mapping->effectiveDate = $effectiveDate;
        $mapping->expirationDate = $expirationDate;
        $mapping->externalJurisdictionCode = $externalCode;

        $object = $mapper->build($mapping);
        $this->assertEquals($object->getName(), $name);
        $this->assertEquals($object->getLevel(), $level);
        $this->assertEquals($object->getId(), $jurisdictionId);
        $this->assertEquals(
            $object->getEffectiveDate() === null ? null : $object->getEffectiveDate()->format('Y-m-d'),
            $effectiveDate
        );
        $this->assertEquals(
            $object->getExpirationDate() === null ? null : $object->getExpirationDate()->format('Y-m-d'),
            $expirationDate
        );
        $this->assertEquals($object->getExternalJurisdictionCode(), $externalCode);
    }

    /**
     * Test {@see JurisdictionMapper::map()}
     *
     * @dataProvider getJurisdictionData
     * @param JurisdictionMapperInterface $mapper
     * @param string|null $name
     * @param string|null $level
     * @param string|null $jurisdictionId
     * @param string|null $effectiveDate
     * @param string|null $expirationDate
     * @param string|null $externalCode
     * @return void
     * @throws ValidationException
     */
    public function testMap(
        JurisdictionMapperInterface $mapper,
        $name = null,
        $level = null,
        $jurisdictionId = null,
        $effectiveDate = null,
        $expirationDate = null,
        $externalCode = null
    ) {
        $object = new Jurisdiction();
        $object->setName($name);
        $object->setLevel($level);
        $object->setId($jurisdictionId);
        if ($effectiveDate !== null) {
            $object->setEffectiveDate(new \DateTime($effectiveDate));
        }
        if ($expirationDate !== null) {
            $object->setExpirationDate(new \DateTime($expirationDate));
        }
        $object->setExternalJurisdictionCode($externalCode);

        $map = $mapper->map($object);

        $this->assertEquals($name, $map->_);
        $this->assertEquals($level, $map->jurisdictionLevel);
        $this->assertEquals($jurisdictionId, $map->jurisdictionId);
        if ($effectiveDate !== null) {
            $this->assertEquals($effectiveDate, $map->effectiveDate);
        } else {
            $this->assertFalse(isset($map->effectiveDate));
        }
        if ($expirationDate !== null) {
            $this->assertEquals($expirationDate, $map->expirationDate);
        } else {
            $this->assertFalse(isset($map->expirationDate));
        }
        $this->assertEquals($externalCode, $map->externalJurisdictionCode);
    }
}
