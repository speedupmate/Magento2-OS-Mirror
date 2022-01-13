<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\FlexibleDateField;
use Vertex\Data\FlexibleDateFieldInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\FlexibleDateFieldMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see FlexibleDateFieldMapperInterface}
 */
class FlexibleDateFieldMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(FlexibleDateFieldInterface::class);
    }

    /**
     * Test {@see FlexibleDateFieldMapperInterface::build()}
     *
     * @dataProvider provideMappers
     * @param FlexibleDateFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testBuild(FlexibleDateFieldMapperInterface $fieldMapper)
    {
        $time = rand(0, time());
        $date = date('Y-m-d', $time);

        $fieldId = rand(1, 5);

        $map = new \stdClass();
        $map->fieldId = $fieldId;
        $map->_ = $date;

        $object = $fieldMapper->build($map);

        $this->assertEquals($fieldId, $object->getFieldId());
        $this->assertEquals($date, $object->getFieldValue()->format('Y-m-d'));
    }

    /**
     * Test {@see FlexibleDateFieldMapperInterface::map()}
     *
     * @dataProvider provideMappers
     * @param FlexibleDateFieldMapperInterface $fieldMapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(FlexibleDateFieldMapperInterface $fieldMapper)
    {
        $time = rand(0, time());
        $date = date('Y-m-d', $time);

        $fieldId = rand(1, 5);

        $timestamp = new \DateTime();
        $timestamp->setTimestamp($time);

        $object = new FlexibleDateField();
        $object->setFieldValue($timestamp)
            ->setFieldId($fieldId);

        $map = $fieldMapper->map($object);

        $this->assertEquals($date, $map->_);
        $this->assertEquals($fieldId, $map->fieldId);
    }

    /**
     * Test {@see FlexibleFieldMapperInterface::map()} throws error with out-of-bounds id
     *
     * @dataProvider provideMappers
     * @param FlexibleDateFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testMapFailsWithLargeId(FlexibleDateFieldMapperInterface $fieldMapper)
    {
        $this->expectException(ValidationException::class);
        $fieldId = rand(5, getrandmax());

        $time = rand(0, time());

        $timestamp = new \DateTime();
        $timestamp->setTimestamp($time);

        $flexibleField = new FlexibleDateField();
        $flexibleField->setFieldId($fieldId)
            ->setFieldValue($timestamp);

        $fieldMapper->map($flexibleField);
    }
}
