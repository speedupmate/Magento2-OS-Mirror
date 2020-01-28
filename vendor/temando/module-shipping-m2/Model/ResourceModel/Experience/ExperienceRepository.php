<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Experience;

use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Model\Checkout\Delivery\CollectionPointManagement;
use Temando\Shipping\Model\Checkout\Delivery\PickupLocationManagement;
use Temando\Shipping\Model\OrderInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ExperienceRepositoryInterface;
use Temando\Shipping\Rest\Adapter\ExperienceApiInterface;
use Temando\Shipping\Rest\EntityMapper\ExperienceResponseMapper;
use Temando\Shipping\Rest\EntityMapper\QualificationRequestTypeBuilder;
use Temando\Shipping\Rest\EntityMapper\QualificationResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ListRequestInterfaceFactory;
use Temando\Shipping\Rest\Request\QualifyRequestFactory;
use Temando\Shipping\Rest\Response\DataObject\Experience;
use Temando\Shipping\Webservice\Response\Type\QualificationResponseType;

/**
 * Temando Experience Repository
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class ExperienceRepository implements ExperienceRepositoryInterface
{
    /**
     * @var CollectionPointManagement
     */
    private $collectionPointManagement;

    /**
     * @var PickupLocationManagement
     */
    private $pickupLocationManagement;

    /**
     * @var QualificationRequestTypeBuilder
     */
    private $requestBuilder;

    /**
     * @var QualifyRequestFactory
     */
    private $qualifyRequestFactory;

    /**
     * @var ListRequestInterfaceFactory
     */
    private $listRequestFactory;

    /**
     * @var ExperienceApiInterface
     */
    private $apiAdapter;

    /**
     * @var QualificationResponseMapper
     */
    private $qualificationResponseMapper;

    /**
     * @var ExperienceResponseMapper
     */
    private $experienceMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExperienceRepository constructor.
     * @param CollectionPointManagement $collectionPointManagement
     * @param PickupLocationManagement $pickupLocationManagement
     * @param QualificationRequestTypeBuilder $requestBuilder
     * @param QualifyRequestFactory $qualifyRequestFactory
     * @param ListRequestInterfaceFactory $listRequestFactory
     * @param ExperienceApiInterface $apiAdapter
     * @param QualificationResponseMapper $qualificationResponseMapper
     * @param ExperienceResponseMapper $experienceMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionPointManagement $collectionPointManagement,
        PickupLocationManagement $pickupLocationManagement,
        QualificationRequestTypeBuilder $requestBuilder,
        QualifyRequestFactory $qualifyRequestFactory,
        ListRequestInterfaceFactory $listRequestFactory,
        ExperienceApiInterface $apiAdapter,
        QualificationResponseMapper $qualificationResponseMapper,
        ExperienceResponseMapper $experienceMapper,
        LoggerInterface $logger
    ) {
        $this->collectionPointManagement = $collectionPointManagement;
        $this->pickupLocationManagement = $pickupLocationManagement;
        $this->requestBuilder = $requestBuilder;
        $this->qualifyRequestFactory = $qualifyRequestFactory;
        $this->listRequestFactory = $listRequestFactory;
        $this->apiAdapter = $apiAdapter;
        $this->qualificationResponseMapper = $qualificationResponseMapper;
        $this->experienceMapper = $experienceMapper;
        $this->logger = $logger;
    }

    /**
     * Check whether order qualification call should be triggered or not.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function canQualify(OrderInterface $order)
    {
        $isCollectionPointSelected = (bool) $order->getCollectionPoint();
        $isPickupLocationSelected = (bool) $order->getPickupLocation();
        if ($isCollectionPointSelected || $isPickupLocationSelected) {
            // a delivery location was selected for quoting, rates already exist in database
            return false;
        }

        $searchRequest = $order->getCollectionPointSearchRequest();
        $isSearchPending = $searchRequest && $searchRequest->isPending();
        if ($isSearchPending) {
            // collection point search not triggered yet, no rates to display
            return false;
        }

        $isSearchPerformed = $searchRequest && $searchRequest->getPostcode() && $searchRequest->getCountryId();
        if ($isSearchPerformed) {
            $locations = $this->collectionPointManagement->getCollectionPoints($searchRequest->getShippingAddressId());
            // no need to qualify if collection points are already available for the given address.
            return (empty($locations));
        }

        $searchRequest = $order->getPickupLocationSearchRequest();
        $isSearchPerformed = $searchRequest && $searchRequest->isActive();
        if ($isSearchPerformed) {
            $locations = $this->pickupLocationManagement->getPickupLocations($searchRequest->getShippingAddressId());
            // no need to qualify if pickup locations are already available for the given address.
            return (empty($locations));
        }

        // regular address quoting
        return true;
    }

    /**
     * Fetch order qualifications from platform.
     *
     * Under certain circumstances, platform invocation is not desired:
     * - quoting data incomplete, user input pending
     * - qualifications already available locally
     *
     * @param OrderInterface $order
     * @return QualificationResponseType
     * @throws CouldNotSaveException
     */
    public function getExperiencesForOrder(OrderInterface $order)
    {
        if (!$this->canQualify($order)) {
            return $this->qualificationResponseMapper->map([]);
        }

        // build qualification request type
        $qualificationType = $this->requestBuilder->build($order);

        // create qualification request
        $qualifyRequest = $this->qualifyRequestFactory->create([
            'requestType' => $qualificationType,
        ]);

        try {
            $qualifications = $this->apiAdapter->qualify($qualifyRequest);
        } catch (AdapterException $e) {
            throw new CouldNotSaveException(__('Unable to qualify order.'), $e);
        }

        return $this->qualificationResponseMapper->map($qualifications);
    }

    /**
     * Obtain shipping experiences from platform.
     *
     * @return \Temando\Shipping\Model\ExperienceInterface[]
     */
    public function getExperiences()
    {
        try {
            $request = $this->listRequestFactory->create();

            $apiExperiences = $this->apiAdapter->getExperiences($request);
            $experiences = array_map(function (Experience $apiExperience) {
                return $this->experienceMapper->map($apiExperience);
            }, $apiExperiences);
        } catch (AdapterException $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $experiences = [];
        }

        return $experiences;
    }
}
