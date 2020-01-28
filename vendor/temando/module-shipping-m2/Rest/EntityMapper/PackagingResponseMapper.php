<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Magento\Framework\Exception\LocalizedException;
use Temando\Shipping\Model\PackagingInterface;
use Temando\Shipping\Model\PackagingInterfaceFactory;
use Temando\Shipping\Rest\Response\DataObject\Container;

/**
 * Map API data to application data object
 *
 * @package  Temando\Shipping\Rest
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class PackagingResponseMapper implements PointerAwareInterface
{
    /**
     * @var PackagingInterfaceFactory
     */
    private $packagingFactory;

    /**
     * PackagingResponseMapper constructor.
     * @param PackagingInterfaceFactory $packagingFactory
     */
    public function __construct(PackagingInterfaceFactory $packagingFactory)
    {
        $this->packagingFactory = $packagingFactory;
    }

    /**
     * @param Container $apiContainer
     * @return PackagingInterface
     */
    public function map(Container $apiContainer)
    {
        $innerDimensions = $apiContainer->getAttributes()->getInnerDimensions();
        $tareWeight      = $apiContainer->getAttributes()->getTareWeight();
        $maxWeight       = $apiContainer->getAttributes()->getMaximumWeight();

        $packaging = $this->packagingFactory->create(['data' => [
            PackagingInterface::PACKAGING_ID => $apiContainer->getId(),
            PackagingInterface::NAME => $apiContainer->getAttributes()->getName(),
            PackagingInterface::TYPE => $apiContainer->getAttributes()->getType(),
            PackagingInterface::WIDTH => sprintf('%s %s', $innerDimensions->getWidth(), $innerDimensions->getUnit()),
            PackagingInterface::LENGTH => sprintf('%s %s', $innerDimensions->getLength(), $innerDimensions->getUnit()),
            PackagingInterface::HEIGHT => sprintf('%s %s', $innerDimensions->getHeight(), $innerDimensions->getUnit()),
            PackagingInterface::TARE_WEIGHT => sprintf('%s %s', $tareWeight->getValue(), $tareWeight->getUnit()),
            PackagingInterface::MAX_WEIGHT => sprintf('%s %s', $maxWeight->getValue(), $maxWeight->getUnit()),
        ]]);

        return $packaging;
    }

    /**
     * Obtain the JSON pointer to a platform property that corresponds to a local entity property.
     *
     * @param string $property The local property name
     * @return string The platform property pointer
     * @throws LocalizedException
     */
    public function getPath($property)
    {
        $pathMap = [
            'provider' => '/provider',
        ];

        if (!isset($pathMap[$property])) {
            throw new LocalizedException(__('Filter field %1 is not supported by the API.', $property));
        }

        return $pathMap[$property];
    }
}
