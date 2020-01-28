<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\ViewModel\Carrier;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Temando\Shipping\ViewModel\DataProvider\CarrierUrl;
use Temando\Shipping\ViewModel\DataProvider\ShippingApiAccess;

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
     * @var CarrierRegistration
     */
    private $subject;

    /**
     * @var ProductMetadataInterface|MockObject
     */
    private $productMetaData;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $request = $this->createMock(RequestInterface::class);
        $apiAccess = $this->createMock(ShippingApiAccess::class);
        $carrierUrl = $this->createMock(CarrierUrl::class);

        $this->productMetaData = $this->createMock(ProductMetadataInterface::class);

        $this->subject = $objectManager->getObject(CarrierRegistration::class, [
            'request' => $request,
            'apiAccess' => $apiAccess,
            'carrierUrl' => $carrierUrl,
            'productMetaData' => $this->productMetaData,
        ]);

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
        $this->productMetaData->expects(self::once())->method('getVersion')->willReturn($version);
        $extracted = $this->subject->getMagentoVersion();

        self::assertSame($expected, $extracted);
    }
}
