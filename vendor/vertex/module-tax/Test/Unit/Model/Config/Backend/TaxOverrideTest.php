<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use Vertex\Tax\Model\Config\Backend\TaxOverride;
use Vertex\Tax\Model\Config\DeliveryTerm;
use Vertex\Tax\Test\Unit\TestCase;

/**
 * Test Class @see TaxOverride
 */
class TaxOverrideTest extends TestCase
{
    /** @var DeliveryTerm|MockObject */
    private $deliveryTerm;

    /** @var ScopeConfigInterface|MockObject */
    private $configMock;

    /** @var TypeListInterface|MockObject */
    private $cacheTypeListMock;

    /** @var Context|MockObject */
    private $context;

    /** @var ManagerInterface|MockObject */
    private $eventManagerMock;

    /** @var Value */
    private $model;

    /** @var Random|MockObject */
    private $random;

    /** @var Registry|MockObject */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void // @codingStandardsIgnoreLine MEQP2.PHP.ProtectedClassMember.FoundProtected
    {
        parent::setUp();
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->cacheTypeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->random = $this->createMock(Random::class);
        $this->deliveryTerm = $this->objectManager->getObject(
            DeliveryTerm::class,
            [
                'mathRandom' => $this->random,
            ]
        );
        $this->model = $this->objectManager->getObject(
            TaxOverride::class,
            [
                'registry' => $this->registry,
                'context' => $this->context,
                'config' => $this->configMock,
                'eventDispatcher' => $this->eventManagerMock,
                'cacheTypeList' => $this->cacheTypeListMock,
                'deliveryTermConfig' => $this->deliveryTerm,
            ]
        );
    }

    /**
     * Test method @see TaxOverride::_afterLoad
     *
     * @param string|bool|array $value
     * @param array $result
     * @dataProvider testAfterLoadDataProvider
     * @return void
     */
    public function testAfterLoad($value, $result)
    {
        $this->model->setData('value', $value);
        $this->model->afterLoad();
        $this->assertEquals(
            $result,
            $this->model->getValue()
        );
    }

    /**
     * Data provider for @see testAfterLoad
     *
     * @return array
     */
    public function testAfterLoadDataProvider()
    {
        return [
            'invalid bool' => [false, []],
            'invalid empty string' => ['', []],
            'valid empty array' => [[], []],
            'valid with serialized' => [
                '{"AR":"DAT"}',
                [
                    '' => ['country_id' => 'AR', 'delivery_term' => 'DAT'],
                ],
            ],
        ];
    }

    /**
     * Test method @see TaxOverride::beforeSave
     *
     * @param string|bool|array $value
     * @param string $result
     * @dataProvider testBeforeSaveDataProvider
     * @return void
     */
    public function testBeforeSave($value, $result)
    {
        $this->model->setData('value', $value);
        $this->model->beforeSave();
        $this->assertEquals(
            $result,
            $this->model->getValue()
        );
    }

    /**
     * Data provider for @see testBeforeSave
     *
     * @return array
     */
    public function testBeforeSaveDataProvider()
    {
        return [
            'invalid bool' => [false, ''],
            'invalid empty string' => ['', ''],
            'valid empty array' => [[], '[]'],
            'valid delivery term' => [
                [
                    'AF' => 'AD',
                    'AW' => 'CFR',
                ],
                '{"AF":"AD","AW":"CFR"}',
            ],
        ];
    }
}
