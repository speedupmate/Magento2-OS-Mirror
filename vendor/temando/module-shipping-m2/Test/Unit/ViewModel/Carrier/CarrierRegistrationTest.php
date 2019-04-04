<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\ViewModel\Carrier;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Temando Carrier Registration Component Test
 *
 * @package Temando\Shipping\Test\Unit
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CarrierRegistrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        parent::setUp();
    }

    /**
     * Provide different input with expectations.
     *
     * @return string[]
     */
    public function versionStringDataProvider()
    {
        return [
            'dist' => ['2.1.13', '2.1.13'],
            'src 2.2' => ['2.2.8-dev', '2.2.8'],
            'src 2.3' => ['dev-2.3-develop', '2.3'],
            'src PR' => ['dev-pr-foo', ''],
            'unknown' => ['UNKNOWN', ''],
        ];
    }

    /**
     * @test
     * @dataProvider versionStringDataProvider
     *
     * @param string $version
     * @param string $expected
     */
    public function extractVersionNumberFromString($version, $expected)
    {
        $productMetaData = $this->createMock(ProductMetadataInterface::class);
        $productMetaData->expects(self::once())->method('getVersion')->willReturn($version);

        $subject = $this->objectManager->getObject(CarrierRegistration::class, [
            'productMetaData' => $productMetaData,
        ]);

        $extracted = $subject->getMagentoVersion();

        self::assertSame($expected, $extracted);
    }
}
