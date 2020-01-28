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
use Temando\Shipping\Api\Checkout\CartCollectionPointManagementInterface;
use Temando\Shipping\Api\Data\Delivery\QuoteCollectionPointInterface;
use Temando\Shipping\Model\Config\ModuleConfigInterface;
use Temando\Shipping\Model\Delivery\DistanceConverter;
use Temando\Shipping\Model\Delivery\OpeningHoursFormatter;
use Temando\Shipping\Model\Delivery\QuoteCollectionPoint;
use Temando\Shipping\Model\ResourceModel\Repository\CollectionPointSearchRepositoryInterface;

/**
 * CollectionPoints
 *
 * @package Temando\Shipping\CustomerData
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class CollectionPoints implements SectionSourceInterface
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
     * @var CollectionPointSearchRepositoryInterface
     */
    private $searchRequestRepository;

    /**
     * @var CartCollectionPointManagementInterface
     */
    private $cartCollectionPointManagement;

    /**
     * @var OpeningHoursFormatter
     */
    private $openingHoursFormatter;

    /**
     * @var DistanceConverter
     */
    private $distanceConverter;

    /**
     * CollectionPoints constructor.
     * @param StoreManagerInterface $storeManager
     * @param ModuleConfigInterface $config
     * @param SessionManagerInterface $checkoutSession
     * @param CollectionFactory $addressCollectionFactory
     * @param HydratorInterface $hydrator
     * @param CollectionPointSearchRepositoryInterface $searchRequestRepository
     * @param CartCollectionPointManagementInterface $cartCollectionPointManagement
     * @param OpeningHoursFormatter $openingHoursFormatter
     * @param DistanceConverter $distanceConverter
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ModuleConfigInterface $config,
        SessionManagerInterface $checkoutSession,
        CollectionFactory $addressCollectionFactory,
        HydratorInterface $hydrator,
        CollectionPointSearchRepositoryInterface $searchRequestRepository,
        CartCollectionPointManagementInterface $cartCollectionPointManagement,
        OpeningHoursFormatter $openingHoursFormatter,
        DistanceConverter $distanceConverter
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->hydrator = $hydrator;
        $this->searchRequestRepository = $searchRequestRepository;
        $this->cartCollectionPointManagement = $cartCollectionPointManagement;
        $this->openingHoursFormatter = $openingHoursFormatter;
        $this->distanceConverter = $distanceConverter;
    }

    /**
     * Obtain collection points data for display in checkout, shipping method step.
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

        $isEnabled = $this->config->isEnabled($storeId) && $this->config->isCollectionPointsEnabled($storeId);
        $quoteId = $this->checkoutSession->getQuoteId();
        if (!$isEnabled || empty($quoteId)) {
            return [
                'collection-points' => [],
                'search-request' => [],
            ];
        }

        // loading the quote address directly without loading the full quote brings a huge performance gain.
        $addressCollection = $this->addressCollectionFactory->create();
        $addressCollection->setQuoteFilter($quoteId)
            ->addFieldToFilter('address_type', ['eq' => 'shipping'])
            ->setPageSize(1)
            ->setCurPage(1);
        $shippingAddress = $addressCollection->fetchItem();

        // check if customer checks out with collection points delivery option
        try {
            // a search request was performed or is pending (waiting for search input)
            $searchRequest = $this->searchRequestRepository->get($shippingAddress->getId());
            $searchRequestData = $this->hydrator->extract($searchRequest);
        } catch (LocalizedException $e) {
            // no search request found at all for given address
            $searchRequestData = [];
        }

        if (empty($searchRequestData) || !empty($searchRequestData['pending'])) {
            return [
                'collection-points' => [],
                'search-request' => $searchRequestData,
            ];
        }

        $collectionPoints = $this->cartCollectionPointManagement->getCollectionPoints($quoteId);

        // map collection points to data array with formatted/localized opening hours
        $collectionPoints = array_map(function (QuoteCollectionPointInterface $collectionPoint) use ($storeId) {
            /** @var QuoteCollectionPoint $collectionPoint */
            $collectionPointData = $collectionPoint->toArray();

            $openingHours = $this->openingHoursFormatter->format($collectionPoint->getOpeningHours());
            $collectionPointData[QuoteCollectionPointInterface::OPENING_HOURS] = $openingHours;

            $distance = $this->distanceConverter->format($collectionPoint->getDistance(), $storeId, '%1$s%2$s', 2);
            $collectionPointData[QuoteCollectionPointInterface::DISTANCE] = $distance;

            return $collectionPointData;
        }, $collectionPoints);

        return [
            'collection-points' => array_values($collectionPoints),
            'search-request' => $searchRequestData,
        ];
    }
}
