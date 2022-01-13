<?php declare(strict_types=1);

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\FlexibleNumericField;
use Vertex\Data\FlexibleNumericFieldInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\FlexibleNumericFieldMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see FlexibleNumericFieldMapperInterface}
 */
class FlexibleNumericFieldMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(FlexibleNumericFieldInterface::class);
    }

    /**
     * Test {@see FlexibleNumericFieldMapperInterface::build()}
     *
     * @dataProvider provideMappers
     * @param FlexibleNumericFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testBuild(FlexibleNumericFieldMapperInterface $fieldMapper)
    {
        $number = rand(PHP_INT_MIN, PHP_INT_MAX) / 1000;
        $fieldId = rand(1, 5);

        $map = new \stdClass();
        $map->fieldId = $fieldId;
        $map->_ = $number;

        $object = $fieldMapper->build($map);

        $this->assertEquals($fieldId, $object->getFieldId());
        $this->assertEquals($number, $object->getFieldValue());
    }

    /**
     * Test {@see FlexibleNumericFieldMapperInterface::map()}
     *
     * @dataProvider provideMappers
     * @param FlexibleNumericFieldMapperInterface $fieldMapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(FlexibleNumericFieldMapperInterface $fieldMapper)
    {
        $number = rand(PHP_INT_MIN, PHP_INT_MAX) / 1000;
        $fieldId = rand(1, 5);

        $object = new FlexibleNumericField();
        $object->setFieldValue($number)
            ->setFieldId($fieldId);

        $map = $fieldMapper->map($object);

        $this->assertEquals($number, $map->_);
        $this->assertEquals($fieldId, $map->fieldId);
    }

    /**
     * Test {@see FlexibleNumericFieldMapperInterface::map()} throws error with out-of-bounds id
     *
     * @dataProvider provideMappers
     * @param FlexibleNumericFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testMapFailsWithLargeId(FlexibleNumericFieldMapperInterface $fieldMapper)
    {
        $this->expectException(ValidationException::class);
        $fieldId = rand(50, getrandmax());
        $number = rand(PHP_INT_MIN, PHP_INT_MAX) / 1000;

        $flexibleField = new FlexibleNumericField();
        $flexibleField->setFieldId($fieldId)
            ->setFieldValue($number);

        $fieldMapper->map($flexibleField);
    }
}
