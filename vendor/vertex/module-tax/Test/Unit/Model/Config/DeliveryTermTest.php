<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Unit\Model\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Math\Random;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\Tax\Model\Config\DeliveryTerm;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test Class @see DeliveryTerm
 */
class DeliveryTermTest extends TestCase
{
    /** @var DeliveryTerm */
    private $deliveryTerm;

    /** @var MockObject */
    private $randomMathMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void // @codingStandardsIgnoreLine MEQP2.PHP.ProtectedClassMember.FoundProtected
    {
        parent::setUp();
        $this->randomMathMock = $this->createMock(Random::class);
        $this->deliveryTerm = $this->getObject(
            DeliveryTerm::class,
            [
                'mathRandom' => $this->randomMathMock,
            ]
        );
    }

    /**
     * Data Provider for test @see testMakeArrayFieldValue
     *
     * @return array
     */
    public function makeArrayFieldValueDataProvider()
    {
        return [
            'invalid bool' => [false, []],
            'invalid empty string' => ['', []],
            'valid empty array' => [[], []],
            'valid with serialized' => [
                '{"USA":"DAT"}',
                [
                    '' => ['country_id' => 'USA', 'delivery_term' => 'DAT'],
                ],
            ],
        ];
    }

    /**
     * Data Provider for test @see testMakeStorableArrayFieldValue
     *
     * @return array
     */
    public function makeStorableArrayFieldValueDataProvider()
    {
        return [
            'invalid bool' => [false, ''],
            'invalid empty string' => ['', ''],
            'valid empty array' => [[], '[]'],
            'valid delivery term' => [
                [
                    'DZA' => 'AD',
                    'USA' => 'CFR',
                ],
                '{"DZA":"AD","USA":"CFR"}',
            ],
        ];
    }

    /**
     * Test if value is readable by @see AbstractFieldArray
     *
     * @param string|array $value
     * @param array $result
     * @dataProvider makeArrayFieldValueDataProvider
     * @return void
     */
    public function testMakeArrayFieldValue($value, $result)
    {
        $this->assertSame($result, $this->deliveryTerm->makeArrayFieldValue($value));
    }

    /**
     * Test value to make ready for store
     *
     * @param string|array $value
     * @param string $result
     * @dataProvider makeStorableArrayFieldValueDataProvider
     * @return void
     */
    public function testMakeStorableArrayFieldValue($value, $result)
    {
        $this->assertSame($result, $this->deliveryTerm->makeStorableArrayFieldValue($value));
    }
}
