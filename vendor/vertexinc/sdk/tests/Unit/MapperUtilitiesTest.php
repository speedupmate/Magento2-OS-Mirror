<?php declare(strict_types=1);

/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype Development         <diveinto@mediotype.com>
 */

namespace Vertex\Test\Unit;

use PHPUnit\Framework\TestCase;
use Vertex\Exception\ValidationException;
use Vertex\Mapper\MapperUtilities;

/**
 * Tests for MapperUtilities
 *
 * @covers \Vertex\Mapper\MapperUtilities
 */
class MapperUtilitiesTest extends TestCase
{
    /** @var MapperUtilities */
    private $utilities;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->utilities = new MapperUtilities();
    }

    /**
     * Retrieve associative arrays
     *
     * Returns a group of associative arrays.  Compatible with dataProvider annotations
     *
     * @return array
     */
    public function getAssociativeArrays()
    {
        return [
            [['a' => 'b']],
            [[0 => 'a', 'b' => 'c']],
            [['a' => true]],
        ];
    }

    /**
     * Retrieve invalid dates
     *
     * Returns a group of data that is not valid dates.  Compatible with dataProvider annotations
     *
     * @return array
     */
    public function getInvalidDates()
    {
        return [
            ['hello'],
            [new \stdClass()],
            [10],
            [false]
        ];
    }

    /**
     * Retrieve invalid integers
     *
     * Returns a group of data that are not valid integers.  Compatible with dataProvider annotations
     *
     * @return array
     */
    public function getInvalidIntegers()
    {
        return [
            ['meow'],
            ['0b01'],
            ['0xFE'],
            [false],
            [new \stdClass()],
        ];
    }

    /**
     * Retrieve non-assocative arrays
     *
     * Returns a group of arrays that are not associative arrays.  Compatible with dataProvider annotations
     *
     * @return array
     */
    public function getNonAssociativeArrays()
    {
        return [
            [[1, 2, 3]],
            [[0 => 'a', 2 => 'b']]
        ];
    }

    /**
     * Retrieve valid dates
     *
     * Returns a group of data that are valid dates for MapperUtilities.  Compatible with dataProvider annotations
     *
     * @return array
     * @throws \Exception
     */
    public function getValidDates()
    {
        return [
            [new \DateTime('2018-07-03'), '2018-07-03'],
            [new \DateTimeImmutable('2018-07-03'), '2018-07-03'],
        ];
    }

    /**
     * Test data validation happy path
     *
     * @dataProvider getValidDates
     * @param \DateTimeInterface $data
     * @param string $expected
     * @return void
     * @throws ValidationException
     */
    public function testDateValidationHappyPath($data, $expected)
    {
        $map = $this->utilities->addToMapWithDateValidation(
            new \stdClass(),
            $data,
            'val',
            false
        );

        $this->assertEquals($expected, $map->val);
    }

    /**
     * Test date validation with invalid dates
     *
     * @dataProvider getInvalidDates
     * @param mixed $data Not a \DateTimeInterface
     * @return void
     * @throws ValidationException
     */
    public function testDateValidationInvalid($data)
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be a valid DateTimeInterface');
        $this->utilities->addToMapWithDateValidation(
            new \stdClass(),
            $data,
            'val',
            false
        );
    }

    /**
     * Test non-optional null for addToMapWithDateValidation
     *
     * In this scenario, we should receive an exception as the value was non-optional
     *
     * @return void
     */
    public function testDateValidationNonOptionalNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must not be null');
        $this->utilities->addToMapWithDateValidation(
            new \stdClass(),
            null,
            'val',
            false
        );
    }

    /**
     * Test optional null for addToMapWithDateValidation
     *
     * In this scenario, we should have no exceptions and our array should not have changed
     *
     * @return void
     * @throws ValidationException
     */
    public function testDateValidationOptionalNull()
    {
        $resultingMap = $this->utilities->addToMapWithDateValidation(
            new \stdClass(),
            null,
            'val',
            true
        );

        $this->assertNotTrue(isset($resultingMap->val));
    }

    /**
     * Test enumeration validation happy path
     *
     * @return void
     * @throws ValidationException
     */
    public function testEnumerationValidationHappyPath()
    {
        $map = $this->utilities->addToMapWithEnumerationValidation(
            new \stdClass(),
            2,
            'val',
            [1, 2, 3],
            false
        );

        $this->assertEquals(2, $map->val);
    }

    /**
     * Test enumeration validation when value is not in enumeration
     *
     * @return void
     */
    public function testEnumerationValidationInvalid()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be one of 1, 2, 3');
        $this->utilities->addToMapWithEnumerationValidation(
            new \stdClass(),
            5,
            'val',
            [1, 2, 3],
            false
        );
    }

    /**
     * Test non-optional null for addToMapWithEnumerationValidation
     *
     * In this scenario, we should receive an exception as the value was non-optional
     *
     * @return void
     */
    public function testEnumerationValidationNonOptionalNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must not be null');
        $this->utilities->addToMapWithEnumerationValidation(
            new \stdClass(),
            null,
            'val',
            [1, 2, 3],
            false
        );
    }

    /**
     * Test optional null for addToMapWithEnumerationValidation
     *
     * In this scenario, we should have no exceptions and our array should not have changed
     *
     * @return void
     * @throws ValidationException
     */
    public function testEnumerationValidationOptionalNull()
    {
        $resultingMap = $this->utilities->addToMapWithEnumerationValidation(
            new \stdClass(),
            null,
            'val',
            [1, 2, 3],
            true
        );

        $this->assertNotTrue(isset($resultingMap->val));
    }

    /**
     * Test integer validation happy path
     *
     * @return void
     * @throws ValidationException
     */
    public function testIntegerValidationHappyPath()
    {
        $result = $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            50,
            'val',
            0,
            100,
            false
        );

        $this->assertEquals(50, $result->val);

        $result = $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            '5',
            'val',
            0,
            100,
            false
        );

        $this->assertEquals(5, $result->val);
    }

    /**
     * Test integer validation when value is not an integer
     *
     * @dataProvider getInvalidIntegers
     * @param mixed $data Not an integer
     * @return void
     * @throws ValidationException
     */
    public function testIntegerValidationInteger($data)
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be an integer');
        $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            $data,
            'val'
        );
    }

    /**
     * Test integration validation when number is greater than the max
     *
     * @return void
     */
    public function testIntegerValidationMaximum()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be less than or equal to 100');
        $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            150,
            'val',
            PHP_INT_MIN,
            100,
            false
        );
    }

    /**
     * Test integration validation when number is greater than the max (and a min is specified)
     *
     * @return void
     */
    public function testIntegerValidationMaximumBetween()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be between 0 and 100, inclusive');
        $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            150,
            'val',
            0,
            100,
            false
        );
    }

    /**
     * Test integer validation when number is less than the minimum
     *
     * @return void
     */
    public function testIntegerValidationMinimum()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be greater than or equal to 0');
        $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            -50,
            'val',
            0,
            PHP_INT_MAX,
            false
        );
    }

    /**
     * Test integer validation when number is less than the minimum (and a max is set)
     *
     * @return void
     */
    public function testIntegerValidationMinimumBetween()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must be between 0 and 100, inclusive');
        $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            -50,
            'val',
            0,
            100,
            false
        );
    }

    /**
     * Test integer validation when number is required and null
     *
     * @return void
     */
    public function testIntegerValidationNonOptionalNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must not be null');
        $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            null,
            'val',
            0,
            100,
            false
        );
    }

    /**
     * Test integer validation when number is optional and null
     *
     * @return void
     * @throws ValidationException
     */
    public function testIntegerValidationOptionalNull()
    {
        $result = $this->utilities->addToMapWithIntegerValidation(
            new \stdClass(),
            null,
            'val',
            0,
            100,
            true
        );

        $this->assertNotTrue(isset($result->val));
    }

    /**
     * Test length validation happy path
     *
     * @return void
     * @throws ValidationException
     */
    public function testLengthValidationHappyPath()
    {
        $result = $this->utilities->addToMapWithLengthValidation(
            new \stdClass(),
            '1234',
            'val',
            4,
            5,
            false
        );

        $this->assertEquals('1234', $result->val);
    }

    /**
     * Test length validation when words are shorter than the maximum
     *
     * @return void
     */
    public function testLengthValidationMaximum()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val length (6) must be between 4 and 5 characters, inclusive');
        $this->utilities->addToMapWithLengthValidation(
            new \stdClass(),
            '123456',
            'val',
            4,
            5,
            false
        );
    }

    /**
     * Test length validation when words are shorter than the minimum
     *
     * @return void
     */
    public function testLengthValidationMinimum()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val length (3) must be between 4 and 5 characters, inclusive');
        $this->utilities->addToMapWithLengthValidation(
            new \stdClass(),
            '123',
            'val',
            4,
            5,
            false
        );
    }

    /**
     * Test non-optional null for addToMapWithLengthValidation
     *
     * In this scenario, we should receive an exception as the value was non-optional
     *
     * @return void
     */
    public function testLengthValidationNonOptionalNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('val must not be null');
        $this->utilities->addToMapWithLengthValidation(
            new \stdClass(),
            null,
            'val',
            MapperUtilities::DEFAULT_MIN,
            MapperUtilities::DEFAULT_MAX,
            false
        );
    }

    /**
     * Test optional null for addToMapWithLengthValidation
     *
     * In this scenario, we should have no exceptions and our array should not have changed
     *
     * @return void
     * @throws ValidationException
     */
    public function testLengthValidationOptionalNull()
    {
        $resultingMap = $this->utilities->addToMapWithLengthValidation(
            new \stdClass(),
            null,
            'val',
            MapperUtilities::DEFAULT_MIN,
            MapperUtilities::DEFAULT_MAX,
            true
        );

        $this->assertNotTrue(isset($resultingMap->val));
    }
}
