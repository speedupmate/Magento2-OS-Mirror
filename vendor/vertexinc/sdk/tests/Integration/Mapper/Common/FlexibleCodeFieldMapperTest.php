<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Common;

use PHPUnit\Framework\TestCase;
use Vertex\Data\FlexibleCodeField;
use Vertex\Data\FlexibleCodeFieldInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\FlexibleCodeFieldMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Tests for {@see FlexibleCodeFieldMapperInterface}
 */
class FlexibleCodeFieldMapperTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(FlexibleCodeFieldInterface::class);
    }

    /**
     * Test {@see FlexibleCodeFieldMapperInterface::build()}
     *
     * @dataProvider provideMappers
     * @param FlexibleCodeFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testBuild(FlexibleCodeFieldMapperInterface $fieldMapper)
    {
        $code = uniqid('code-', false);
        $fieldId = rand(1, 25);

        $map = new \stdClass();
        $map->fieldId = $fieldId;
        $map->_ = $code;

        $object = $fieldMapper->build($map);

        $this->assertEquals($fieldId, $object->getFieldId());
        $this->assertEquals($code, $object->getFieldValue());
    }

    /**
     * Test {@see FlexibleCodeFieldMapperInterface::map()}
     *
     * @dataProvider provideMappers
     * @param FlexibleCodeFieldMapperInterface $fieldMapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(FlexibleCodeFieldMapperInterface $fieldMapper)
    {
        $code = uniqid('code-', false);
        $fieldId = rand(1, 25);

        $object = new FlexibleCodeField();
        $object->setFieldValue($code)
            ->setFieldId($fieldId);

        $map = $fieldMapper->map($object);

        $this->assertEquals($code, $map->_);
        $this->assertEquals($fieldId, $map->fieldId);
    }

    /**
     * Test {@see FlexibleFieldMapperInterface::map()} throws error with too-long code
     *
     * @dataProvider provideMappers
     * @param FlexibleCodeFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testMapFailsWithLargeCode(FlexibleCodeFieldMapperInterface $fieldMapper)
    {
        $this->expectException(ValidationException::class);
        $fieldId = rand(1, 25);
        $code = str_repeat(uniqid('code-', false), 3);

        $flexibleField = new FlexibleCodeField();
        $flexibleField->setFieldId($fieldId)
            ->setFieldValue($code);

        $fieldMapper->map($flexibleField);
    }

    /**
     * Test {@see FlexibleFieldMapperInterface::map()} throws error with out-of-bounds id
     *
     * @dataProvider provideMappers
     * @param FlexibleCodeFieldMapperInterface $fieldMapper
     * @return void
     */
    public function testMapFailsWithLargeId(FlexibleCodeFieldMapperInterface $fieldMapper)
    {
        $this->expectException(ValidationException::class);
        $fieldId = rand(26, getrandmax());
        $code = uniqid('code-', false);

        $flexibleField = new FlexibleCodeField();
        $flexibleField->setFieldId($fieldId)
            ->setFieldValue($code);

        $fieldMapper->map($flexibleField);
    }
}
