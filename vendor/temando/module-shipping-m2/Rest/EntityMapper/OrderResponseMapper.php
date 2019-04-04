<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Magento\Directory\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Api\Data\Order\OrderReferenceInterface;
use Temando\Shipping\Api\Data\Order\OrderReferenceInterfaceFactory;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterface;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterfaceFactory;
use Temando\Shipping\Rest\Response\Type\Order\Included\Attributes\Experience;
use Temando\Shipping\Rest\Response\Type\Order\Included\Attributes\Experience\Cost;
use Temando\Shipping\Rest\Response\Type\Order\Included\Attributes\Experience\Description;
use Temando\Shipping\Rest\Response\Type\OrderIncludedResponseType;
use Temando\Shipping\Rest\Response\UpdateOrder;

/**
 * Map API data to application data object
 *
 * @package  Temando\Shipping\Rest
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class OrderResponseMapper
{
    /**
     * @var OrderReferenceInterfaceFactory
     */
    private $orderReferenceFactory;

    /**
     * @var ShippingExperienceInterfaceFactory
     */
    private $shippingExperienceFactory;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Data
     */
    private $directoryHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderResponseMapper constructor.
     * @param OrderReferenceInterfaceFactory $orderReferenceFactory
     * @param ShippingExperienceInterfaceFactory $shippingExperienceFactory
     * @param ResolverInterface $localeResolver
     * @param Data $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderReferenceInterfaceFactory $orderReferenceFactory,
        ShippingExperienceInterfaceFactory $shippingExperienceFactory,
        ResolverInterface $localeResolver,
        Data $directoryHelper,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->orderReferenceFactory = $orderReferenceFactory;
        $this->shippingExperienceFactory = $shippingExperienceFactory;
        $this->localeResolver = $localeResolver;
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param Description[] $descriptions
     * @return string
     */
    private function getLocalizedDescription(array $descriptions)
    {
        $descriptionFilter = function ($descriptions, $locale) {
            /** @var Description $description */
            foreach ($descriptions as $description) {
                if ($description->getLocale() === $locale) {
                    return $description;
                }
            }

            return null;
        };

        // try locale exact match first
        $locale = $this->localeResolver->getLocale();
        $fallbacks = [$locale, substr($locale, 0, 2), 'en'];

        do {
            $lang = array_shift($fallbacks);
            $localizedDescription = $descriptionFilter($descriptions, $lang);
        } while (!empty($fallbacks) && !$localizedDescription);

        return ($localizedDescription ? $localizedDescription->getText() : '');
    }

    /**
     * @param Cost[] $cost
     *
     * @return float
     * @throws LocalizedException
     */
    private function extractShippingCost(array $cost)
    {
        // (1) no rates available in response
        if (empty($cost)) {
            return 0;
        }

        /** @var \Magento\Store\Model\Store $currentStore */
        $currentStore = $this->storeManager->getStore();
        $currency     = $currentStore->getBaseCurrencyCode();

        // (2) exact match found in response
        foreach ($cost as $item) {
            if ($item->getCurrency() === $currency) {
                return $item->getAmount();
            }
        }

        // (3) return first available cost
        $item = current($cost);
        return $item->getAmount();
    }

    /**
     * @param UpdateOrder $apiOrder
     *
     * @return OrderReferenceInterface
     * @throws LocalizedException
     */
    public function map(UpdateOrder $apiOrder)
    {
        /** @var OrderIncludedResponseType[] $included */
        $included = array_filter($apiOrder->getIncluded(), function (OrderIncludedResponseType $element) {
            return ($element->getType() == 'orderQualification');
        });

        /** @var ShippingExperienceInterface[] $shippingExperiences */
        $apiExperiences = $included[0]->getAttributes()->getExperiences();
        $shippingExperiences = array_map(function (Experience $apiExperience) {
            $description = $this->getLocalizedDescription($apiExperience->getDescription());
            $cost = $this->extractShippingCost($apiExperience->getCost());

            $shippingExperience = $this->shippingExperienceFactory->create([
                ShippingExperienceInterface::LABEL => $description,
                ShippingExperienceInterface::CODE => $apiExperience->getCode(),
                ShippingExperienceInterface::COST => $cost,
            ]);
            return $shippingExperience;
        }, $apiExperiences);

        /** @var \Temando\Shipping\Model\Order\OrderReference $orderReference */
        $orderReference = $this->orderReferenceFactory->create(['data' => [
            OrderReferenceInterface::EXT_ORDER_ID => $apiOrder->getData()->getId(),
            OrderReferenceInterface::SHIPPING_EXPERIENCES => $shippingExperiences,
        ]]);

        return $orderReference;
    }
}
