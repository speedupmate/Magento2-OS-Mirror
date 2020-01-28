<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

namespace Temando\Shipping\Model\Source;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use Temando\Shipping\Model\Packaging;
use Temando\Shipping\Model\ResourceModel\Packaging\PackagingRepository;
use Temando\Shipping\Model\Source\Packaging as PackagingSource;
use Temando\Shipping\Model\Source\PackagingType as PackagingTypeSource;

/**
 * Class PackagingSourceTest
 *
 * @package Temando\Shipping\Test\Integration
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PackagingSourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Provide dummy containers for use in source model.
     *
     * @return Packaging[][]
     */
    public function containersProvider()
    {
        $data = [
            [Packaging::PACKAGING_ID => '123', Packaging::NAME => 'Name 123'],
            [Packaging::PACKAGING_ID => '987', Packaging::NAME => 'Name 987'],
        ];

        $containers = array_map(function (array $data) {
            return Bootstrap::getObjectManager()->create(Packaging::class, ['data' => $data]);
        }, $data);

        return [[$containers]];
    }

    /**
     * Assert empty packaging list does not blow up select.
     *
     * @test
     */
    public function getEmptyPackagingOptions()
    {
        /** @var PackagingRepository|MockObject $repository */
        $repository = $this->getMockBuilder(PackagingRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $repository->method('getList')->willReturn([]);

        /** @var PackagingSource $subject */
        $subject = Bootstrap::getObjectManager()->create(PackagingSource::class, [
            'packagingRepository' => $repository,
        ]);

        $options = $subject->getAllOptions();
        $options = array_filter($options, function (array $option) {
            // skip 'Please Select' pseudo-option
            return !empty($option['value']);
        });

        self::assertInternalType('array', $options);
        self::assertEmpty($options);
    }

    /**
     * Assert containers will be properly displayed in select.
     *
     * @test
     * @dataProvider containersProvider
     *
     * @param Packaging[] $containers
     */
    public function getAllPackagingOptions(array $containers)
    {
        /** @var PackagingRepository|MockObject $repository */
        $repository = $this->getMockBuilder(PackagingRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $repository->method('getList')->willReturn($containers);

        /** @var PackagingSource $subject */
        $subject = Bootstrap::getObjectManager()->create(PackagingSource::class, [
            'packagingRepository' => $repository,
        ]);

        $options = $subject->getAllOptions();
        $options = array_filter($options, function (array $option) {
            // skip 'Please Select' pseudo-option
            return !empty($option['value']);
        });

        self::assertInternalType('array', $options);
        self::assertNotEmpty($options);
        self::assertCount(count($containers), $options);
        foreach ($options as $option) {
            self::assertInternalType('array', $option);
            self::assertArrayHasKey('value', $option);
            self::assertArrayHasKey('label', $option);
        }
    }

    /**
     * Assert packaging types will be properly displayed in select.
     *
     * @test
     */
    public function getAllPackagingTypeOptions()
    {
        /** @var PackagingTypeSource $subject */
        $subject = Bootstrap::getObjectManager()->create(PackagingTypeSource::class);

        $options = $subject->getAllOptions();
        self::assertInternalType('array', $options);
        self::assertNotEmpty($options);
        foreach ($options as $option) {
            self::assertInternalType('array', $option);
            self::assertArrayHasKey('value', $option);
            self::assertArrayHasKey('label', $option);
        }
    }
}
