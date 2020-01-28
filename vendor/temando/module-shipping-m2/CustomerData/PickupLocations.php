<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Temando\Shipping\Api\Checkout\CartPickupLocationManagementInterface;
use Temando\Shipping\Api\Data\Delivery\QuotePickupLocationInterface;
use Temando\Shipping\Model\Config\ModuleConfigInterface;
use Temando\Shipping\Model\Delivery\DistanceConverter;
use Temando\Shipping\Model\Delivery\OpeningHoursFormatter;
use Temando\Shipping\Model\Delivery\QuotePickupLocation;
use Temando\Shipping\Model\ResourceModel\Repository\PickupLocationSearchRepositoryInterface;

/**
 * PickupLocations
 *
 * @package Temando\Shipping\CustomerData
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class PickupLocations implements SectionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var SessionManagerInterface|\Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var PickupLocationSearchRepositoryInterface
     */
    private $searchRequestRepository;

    /**
     * @var CartPickupLocationManagementInterface
     */
    private $cartPickupLocationManagement;

    /**
     * @var OpeningHoursFormatter
     */
    private $openingHoursFormatter;

    /**
     * @var DistanceConverter
     */
    private $distanceConverter;

    /**
     * PickupLocations constructor.
     * @param StoreManagerInterface $storeManager
     * @param ModuleConfigInterface $config
     * @param SessionManagerInterface $checkoutSession
     * @param CollectionFactory $addressCollectionFactory
     * @param HydratorInterface $hydrator
     * @param PickupLocationSearchRepositoryInterface $searchRequestRepository
     * @param CartPickupLocationManagementInterface $cartPickupLocationManagement
     * @param OpeningHoursFormatter $openingHoursFormatter
     * @param DistanceConverter $distanceConverter
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ModuleConfigInterface $config,
        SessionManagerInterface $checkoutSession,
        CollectionFactory $addressCollectionFactory,
        HydratorInterface $hydrator,
        PickupLocationSearchRepositoryInterface $searchRequestRepository,
        CartPickupLocationManagementInterface $cartPickupLocationManagement,
        OpeningHoursFormatter $openingHoursFormatter,
        DistanceConverter $distanceConverter
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->hydrator = $hydrator;
        $this->searchRequestRepository = $searchRequestRepository;
        $this->cartPickupLocationManagement = $cartPickupLocationManagement;
        $this->openingHoursFormatter = $openingHoursFormatter;
        $this->distanceConverter = $distanceConverter;
    }

    /**
     * Obtain pickup locations data for display in checkout, shipping method step.
     *
     * @return string[]
     */
    public function getSectionData()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $storeId = null;
        }

        $isEnabled = $this->config->isEnabled($storeId) && $this->config->isClickAndCollectEnabled($storeId);
        $quoteId = $this->checkoutSession->getQuoteId();
        if (!$isEnabled || empty($quoteId)) {
            return [
                'pickup-locations' => [],
                'search-request' => []
            ];
        }

        // loading the quote address directly without loading the full quote brings a huge performance gain.
        $addressCollection = $this->addressCollectionFactory->create();
        $addressCollection->setQuoteFilter($quoteId)
            ->addFieldToFilter('address_type', ['eq' => 'shipping'])
            ->setPageSize(1)
            ->setCurPage(1);
        $shippingAddress = $addressCollection->fetchItem();

        try {
            $searchRequest = $this->searchRequestRepository->get($shippingAddress->getId());
            $searchRequest = $this->hydrator->extract($searchRequest);
            $pickupLocations = $this->cartPickupLocationManagement->getPickupLocations($quoteId);
        } catch (LocalizedException $e) {
            $searchRequest = [];
            $pickupLocations = [];
        }

        // map pickup locations to data array with formatted/localized opening hours
        $pickupLocations = array_map(function (QuotePickupLocationInterface $pickupLocation) use ($storeId) {
            /** @var QuotePickupLocation $pickupLocation */
            $pickupLocationData = $pickupLocation->toArray();

            $openingHours = $this->openingHoursFormatter->format($pickupLocation->getOpeningHours());
            $pickupLocationData[QuotePickupLocationInterface::OPENING_HOURS] = $openingHours;

            $distance = $this->distanceConverter->format($pickupLocation->getDistance(), $storeId);
            $pickupLocationData[QuotePickupLocationInterface::DISTANCE] = $distance;

            return $pickupLocationData;
        }, $pickupLocations);

        return [
            'pickup-locations' => array_values($pickupLocations),
            'search-request' => $searchRequest
        ];
    }
}
