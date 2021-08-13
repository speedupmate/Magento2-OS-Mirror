<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Common\LineItemMapperTest;

use PHPUnit\Framework\TestCase;
use Vertex\Data\FlexibleCodeField;
use Vertex\Data\FlexibleCodeFieldInterface;
use Vertex\Data\FlexibleDateField;
use Vertex\Data\FlexibleDateFieldInterface;
use Vertex\Data\FlexibleFieldInterface;
use Vertex\Data\FlexibleNumericField;
use Vertex\Data\FlexibleNumericFieldInterface;
use Vertex\Data\LineItem;
use Vertex\Data\LineItemInterface;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\LineItemMapperInterface;
use Vertex\Test\Integration\Mapper\Utility\CommonMapperProvider;

/**
 * Flexible Field tests for {@see LineItemMapperInterface}
 *
 * Tests functionality common among all API Levels
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlexibleFieldsTest extends TestCase
{
    /**
     * Retrieve all mappers to test
     *
     * @return array
     */
    public function provideMappers()
    {
        return CommonMapperProvider::getAllMappers(LineItemInterface::class);
    }

    /**
     * Test {@see LineItemMapperInterface::build()}
     *
     * @dataProvider provideMappers
     * @param LineItemMapperInterface $mapper
     * @return void
     */
    public function testBuild(LineItemMapperInterface $mapper)
    {
        $map = new \stdClass();
        $map->FlexibleFields = new \stdClass();
        $map->FlexibleFields->FlexibleCodeField = [];
        $map->FlexibleFields->FlexibleCodeField[0] = new \stdClass();
        $map->FlexibleFields->FlexibleCodeField[0]->fieldId = 10;
        $map->FlexibleFields->FlexibleCodeField[0]->_ = 'flexible code field';
        $map->FlexibleFields->FlexibleNumericField = new \stdClass();
        $map->FlexibleFields->FlexibleNumericField->fieldId = 2;
        $map->FlexibleFields->FlexibleNumericField->_ = 3.14159265358979;
        $map->FlexibleFields->FlexibleDateField = [];
        $map->FlexibleFields->FlexibleDateField[0] = new \stdClass();
        $map->FlexibleFields->FlexibleDateField[0]->fieldId = 5;
        $map->FlexibleFields->FlexibleDateField[0]->_ = '2019-01-01';

        $object = $mapper->build($map);

        $this->assertCount(3, $object->getFlexibleFields());
        /** @var FlexibleCodeFieldInterface[] $codeFields */
        $codeFields = array_values(
            array_filter(
                $object->getFlexibleFields(),
                function (FlexibleFieldInterface $flexibleField) {
                    return $flexibleField instanceof FlexibleCodeFieldInterface;
                }
            )
        );
        $this->assertCount(1, $codeFields);
        $this->assertEquals('flexible code field', $codeFields[0]->getFieldValue());
        $this->assertEquals('10', $codeFields[0]->getFieldId());

        /** @var FlexibleNumericFieldInterface[] $numericFields */
        $numericFields = array_values(
            array_filter(
                $object->getFlexibleFields(),
                function (FlexibleFieldInterface $flexibleField) {
                    return $flexibleField instanceof FlexibleNumericFieldInterface;
                }
            )
        );
        $this->assertCount(1, $numericFields);
        $this->assertEquals(3.14159265358979, $numericFields[0]->getFieldValue());
        $this->assertEquals(2, $numericFields[0]->getFieldId());

        /** @var FlexibleDateFieldInterface[] $dateFields */
        $dateFields = array_values(
            array_filter(
                $object->getFlexibleFields(),
                function (FlexibleFieldInterface $flexibleField) {
                    return $flexibleField instanceof FlexibleDateFieldInterface;
                }
            )
        );
        $this->assertCount(1, $dateFields);
        $this->assertEquals('2019-01-01', $dateFields[0]->getFieldValue()->format('Y-m-d'));
        $this->assertEquals(5, $dateFields[0]->getFieldId());
    }

    /**
     * Test {@see LineItemMapper::map()}
     *
     * @dataProvider provideMappers
     * @param LineItemMapperInterface $mapper
     * @return void
     * @throws ValidationException
     */
    public function testMap(LineItemMapperInterface $mapper)
    {
        $object = new LineItem();
        $object->setFlexibleFields(
            [
                (new FlexibleCodeField())->setFieldId(5)->setFieldValue('weird flex but ok'),
                (new FlexibleDateField())->setFieldId(2)->setFieldValue(new \DateTimeImmutable('2018-12-31')),
                (new FlexibleNumericField())->setFieldId(3)->setFieldValue(3.14159),
            ]
        );

        $map = $mapper->map($object);

        $this->assertEquals('weird flex but ok', $map->FlexibleFields->FlexibleCodeField[0]->_);
        $this->assertEquals(5, $map->FlexibleFields->FlexibleCodeField[0]->fieldId);
        $this->assertEquals('2018-12-31', $map->FlexibleFields->FlexibleDateField[0]->_);
        $this->assertEquals(2, $map->FlexibleFields->FlexibleDateField[0]->fieldId);
        $this->assertEquals(3.14159, $map->FlexibleFields->FlexibleNumericField[0]->_);
        $this->assertEquals(3, $map->FlexibleFields->FlexibleNumericField[0]->fieldId);
    }
}
