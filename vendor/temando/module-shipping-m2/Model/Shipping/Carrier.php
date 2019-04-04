<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\Shipping;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Temando\Shipping\Model\Checkout\RateRequest\QuotingDataInitializer;
use Temando\Shipping\Model\ExperienceInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ExperienceRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentReferenceRepositoryInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ShipmentRepositoryInterface;
use Temando\Shipping\Model\Shipping\RateResult\FreeShipping;
use Temando\Shipping\Webservice\Processor\OrderQualificationProcessorPool;

/**
 * Temando Shipping Carrier
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var StatusFactory
     */
    private $trackStatusFactory;

    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentReferenceRepositoryInterface
     */
    private $shipmentReferenceRepository;

    /**
     * @var ExperienceRepositoryInterface
     */
    private $experienceRepository;

    /**
     * @var QuotingDataInitializer
     */
    private $quotingDataInitializer;

    /**
     * @var OrderQualificationProcessorPool
     */
    private $processorPool;

    /**
     * @var FreeShipping
     */
    private $freeShipping;

    /**
     * Carrier constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param StatusFactory $trackStatusFactory
     * @param MethodFactory $rateMethodFactory
     * @param ResultFactory $rateResultFactory
     * @param ManagerInterface $messageManager
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentReferenceRepositoryInterface $shipmentReferenceRepository
     * @param ExperienceRepositoryInterface $experienceRepository
     * @param QuotingDataInitializer $quotingDataInitializer
     * @param OrderQualificationProcessorPool $processorPool
     * @param FreeShipping $freeShipping
     * @param mixed[] $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        StatusFactory $trackStatusFactory,
        MethodFactory $rateMethodFactory,
        ResultFactory $rateResultFactory,
        ManagerInterface $messageManager,
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentReferenceRepositoryInterface $shipmentReferenceRepository,
        ExperienceRepositoryInterface $experienceRepository,
        QuotingDataInitializer $quotingDataInitializer,
        OrderQualificationProcessorPool $processorPool,
        FreeShipping $freeShipping,
        array $data = []
    ) {
        $this->trackStatusFactory = $trackStatusFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->messageManager = $messageManager;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentReferenceRepository = $shipmentReferenceRepository;
        $this->experienceRepository = $experienceRepository;
        $this->quotingDataInitializer = $quotingDataInitializer;
        $this->processorPool = $processorPool;
        $this->freeShipping = $freeShipping;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Code of the carrier
     */
    const CODE = 'temando';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Fetch shipping rates from API.
     *
     * @param RateRequest $rateRequest
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $rateRequest)
    {
        $result = $this->rateResultFactory->create();

        $activeFlag = $this->getData('active_flag');
        if ($activeFlag && !$this->getConfigFlag($activeFlag)) {
            return $result;
        }

        try {
            // create request data from rate request
            $order = $this->quotingDataInitializer->getOrder($rateRequest);
            // send order to Temando platform, will respond with shipping options
            $qualifications = $this->experienceRepository->getExperiencesForOrder($order);
            // extract applicable shipping options from order qualifications
            $shippingOptions = $this->processorPool->processRatesResponse($rateRequest, $order, $qualifications);
        } catch (LocalizedException $e) {
            $this->_logger->log(LogLevel::WARNING, $e->getMessage(), ['exception' => $e]);
            $shippingOptions = [];
        }

        foreach ($shippingOptions as $shippingOption) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($shippingOption->getCode());
            $method->setMethodTitle($shippingOption->getLabel());

            $method->setPrice($shippingOption->getCost());
            $method->setCost($shippingOption->getCost());

            $result->append($method);
        }

        if (empty($result->getAllRates())) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(['data' => [
                'carrier' => $this->_code,
                'carrier_title' => $this->getConfigData('title'),
                'error_message' => $this->getConfigData('specificerrmsg'),
            ]]);
            $result->append($error);
        } else {
            $this->freeShipping->apply($rateRequest, $result);
        }

        return $result;
    }

    /**
     * Obtain shipping experiences configured at the merchant account for usage
     * in cart price rule conditions.
     *
     * @return string[]
     */
    public function getAllowedMethods()
    {
        $experiences = array_reduce(
            $this->experienceRepository->getExperiences(),
            function (array $carry, ExperienceInterface $experience) {
                if ($experience->getStatus() !== ExperienceInterface::STATUS_DISABLED) {
                    $carry[$experience->getExperienceId()] = $experience->getName();
                }

                return $carry;
            },
            []
        );

        asort($experiences);

        return $experiences;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get tracking information. Original return value annotation is misleading.
     *
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrier::isTrackingAvailable()
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrierOnline::getTrackingInfo()
     * @see \Magento\Dhl\Model\Carrier::getTracking()
     * @param string $trackingNumber
     * @return \Magento\Shipping\Model\Tracking\Result\AbstractResult
     */
    public function getTrackingInfo($trackingNumber)
    {
        /** @var \Magento\Shipping\Model\Tracking\Result\Status $tracking */
        $tracking = $this->trackStatusFactory->create();
        $tracking->setCarrier($this->_code);
        $tracking->setTracking($trackingNumber);

        try {
            $shipmentTrack = $this->shipmentReferenceRepository->getShipmentTrack($this->_code, $trackingNumber);

            $carrierTitle = $shipmentTrack->getTitle() ? $shipmentTrack->getTitle() : $this->getConfigData('title');
            $trackingUrl = $this->getTrackingUrl($shipmentTrack);

            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setUrl($trackingUrl);
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setUrl('');
        }

        return $tracking;
    }

    /**
     * Extract tracking URL from platform shipment. If possible, read url from package level.
     *
     * @param ShipmentTrackInterface $shipmentTrack
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getTrackingUrl(ShipmentTrackInterface $shipmentTrack)
    {
        $shipmentId = $shipmentTrack->getParentId();
        $shipmentReference = $this->shipmentReferenceRepository->getByShipmentId($shipmentId);
        $shipment = $this->shipmentRepository->getById($shipmentReference->getExtShipmentId());

        // read tracking url from booking fulfillment
        $trackingUrl = $shipment->getFulfillment()->getTrackingUrl();

        // check if there is a matching tracking url in the packages
        foreach ($shipment->getPackages() as $package) {
            if (!$package->getTrackingUrl()) {
                continue;
            }

            if ($package->getTrackingReference() === $shipmentTrack->getTrackNumber()) {
                $trackingUrl = $package->getTrackingUrl();
            }
        }

        return (string) $trackingUrl;
    }
}
