<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Temando\Shipping\Model\DocumentationInterface;
use Temando\Shipping\Model\Shipment\CapabilityInterface;
use Temando\Shipping\Model\Shipment\CapabilityInterfaceFactory;
use Temando\Shipping\Model\Shipment\ExportDeclarationInterface;
use Temando\Shipping\Model\Shipment\ExportDeclarationInterfaceFactory;
use Temando\Shipping\Model\Shipment\FulfillmentInterface;
use Temando\Shipping\Model\Shipment\FulfillmentInterfaceFactory;
use Temando\Shipping\Model\Shipment\LocationInterface;
use Temando\Shipping\Model\Shipment\LocationInterfaceFactory;
use Temando\Shipping\Model\Shipment\PackageInterface;
use Temando\Shipping\Model\Shipment\ShipmentItemInterface;
use Temando\Shipping\Model\Shipment\ShipmentItemInterfaceFactory;
use Temando\Shipping\Model\ShipmentInterface;
use Temando\Shipping\Model\ShipmentInterfaceFactory;
use Temando\Shipping\Rest\Response\DataObject\Shipment;
use Temando\Shipping\Rest\Response\Fields\Generic\Documentation;
use Temando\Shipping\Rest\Response\Fields\Generic\Item;
use Temando\Shipping\Rest\Response\Fields\Generic\Package;
use Temando\Shipping\Rest\Response\Fields\LocationAttributes;
use Temando\Shipping\Rest\Response\Fields\Shipment\Fulfill;

/**
 * Map API data to application data object
 *
 * @package  Temando\Shipping\Rest
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class ShipmentResponseMapper
{
    /**
     * @var ShipmentInterfaceFactory
     */
    private $shipmentFactory;

    /**
     * @var LocationInterfaceFactory
     */
    private $locationFactory;

    /**
     * @var FulfillmentInterfaceFactory
     */
    private $fulfillmentFactory;

    /**
     * @var ShipmentItemInterfaceFactory
     */
    private $shipmentItemFactory;

    /**
     * @var ExportDeclarationInterfaceFactory
     */
    private $exportDeclarationFactory;

    /**
     * @var PackageResponseMapper
     */
    private $packageMapper;

    /**
     * @var DocumentationResponseMapper
     */
    private $documentationMapper;

    /**
     * @var CapabilityInterfaceFactory
     */
    private $capabilityFactory;

    /**
     * ShipmentResponseMapper constructor.
     * @param ShipmentInterfaceFactory $shipmentFactory
     * @param LocationInterfaceFactory $locationFactory
     * @param FulfillmentInterfaceFactory $fulfillmentFactory
     * @param ShipmentItemInterfaceFactory $shipmentItemFactory
     * @param ExportDeclarationInterfaceFactory $exportDeclarationFactory
     * @param PackageResponseMapper $packageMapper
     * @param DocumentationResponseMapper $documentationMapper
     * @param CapabilityInterfaceFactory $capabilityFactory
     */
    public function __construct(
        ShipmentInterfaceFactory $shipmentFactory,
        LocationInterfaceFactory $locationFactory,
        FulfillmentInterfaceFactory $fulfillmentFactory,
        ShipmentItemInterfaceFactory $shipmentItemFactory,
        ExportDeclarationInterfaceFactory $exportDeclarationFactory,
        PackageResponseMapper $packageMapper,
        DocumentationResponseMapper $documentationMapper,
        CapabilityInterfaceFactory $capabilityFactory
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->locationFactory = $locationFactory;
        $this->fulfillmentFactory = $fulfillmentFactory;
        $this->shipmentItemFactory = $shipmentItemFactory;
        $this->exportDeclarationFactory = $exportDeclarationFactory;
        $this->packageMapper = $packageMapper;
        $this->documentationMapper = $documentationMapper;
        $this->capabilityFactory = $capabilityFactory;
    }

    /**
     * @param Fulfill|null $apiFulfillment
     * @return FulfillmentInterface|null
     */
    private function mapFulfillment(?Fulfill $apiFulfillment): ?FulfillmentInterface
    {
        if (!$apiFulfillment) {
            return null;
        }

        $booking = $apiFulfillment->getCarrierBooking();
        $fulfillment = $this->fulfillmentFactory->create(['data' => [
            FulfillmentInterface::TRACKING_REFERENCE => $booking->getTrackingReference(),
            FulfillmentInterface::TRACKING_URL => $booking->getTrackingUrl(),
            FulfillmentInterface::SERVICE_NAME => $booking->getServiceName(),
            FulfillmentInterface::CARRIER_NAME => $booking->getCarrierName(),
        ]]);

        return $fulfillment;
    }

    /**
     * @param LocationAttributes|null $apiLocation
     * @return LocationInterface|null
     */
    private function mapLocation(?LocationAttributes $apiLocation): ?LocationInterface
    {
        if (!$apiLocation) {
            return null;
        }

        $contact = $apiLocation->getContact();
        $location = $this->locationFactory->create(['data' => [
            LocationInterface::NAME => '',
            LocationInterface::COMPANY => $contact ? $contact->getOrganisationName() : '',
            LocationInterface::PERSON_FIRST_NAME => $contact ? $contact->getPersonFirstName() : '',
            LocationInterface::PERSON_LAST_NAME => $contact ? $contact->getPersonLastName() : '',
            LocationInterface::EMAIL => $contact ? $contact->getEmail() : '',
            LocationInterface::PHONE_NUMBER => $contact ? $contact->getPhoneNumber() : '',
            LocationInterface::STREET => $apiLocation->getAddress()->getLines(),
            LocationInterface::CITY => $apiLocation->getAddress()->getLocality(),
            LocationInterface::POSTAL_CODE => $apiLocation->getAddress()->getPostalCode(),
            LocationInterface::REGION_CODE => $apiLocation->getAddress()->getAdministrativeArea(),
            LocationInterface::COUNTRY_CODE => $apiLocation->getAddress()->getCountryCode(),
            LocationInterface::TYPE => $apiLocation->getType(),
        ]]);

        return $location;
    }

    /**
     * @param Shipment $apiShipment
     * @return DocumentationInterface[]
     */
    private function mapDocumentation(Shipment $apiShipment): array
    {
        // collect documentation from shipment and packages
        $apiDocs = $apiShipment->getAttributes()->getDocumentation();
        foreach ($apiShipment->getAttributes()->getPackages() as $package) {
            foreach ($package->getDocumentation() as $apiDoc) {
                $apiDocs[]= $apiDoc;
            }
        }

        // map collected documentation
        $documentation = array_map(function (Documentation $apiDoc) {
            return $this->documentationMapper->map($apiDoc);
        }, $apiDocs);

        return $documentation;
    }

    /**
     * @param Shipment $apiShipment
     * @return ExportDeclarationInterface
     */
    private function mapExportDeclaration(Shipment $apiShipment): ?ExportDeclarationInterface
    {
        $apiDeclaration = $apiShipment->getAttributes()->getExportDeclaration();
        if (!$apiDeclaration) {
            return null;
        }

        /** @var \Temando\Shipping\Model\Shipment\ExportDeclaration $exportDeclaration */
        $exportDeclaration = $this->exportDeclarationFactory->create();

        $exportDeclaration->setData(
            ExportDeclarationInterface::IS_DUTIABLE,
            $apiShipment->getAttributes()->getIsDutiable()
        );

        $apiDeclaredValue = $apiDeclaration->getDeclaredValue();
        if ($apiDeclaredValue) {
            $exportDeclaration->setData(
                ExportDeclarationInterface::DECLARED_VALUE,
                "{$apiDeclaredValue->getAmount()} {$apiDeclaredValue->getCurrency()}"
            );
        }

        $exportDeclaration->setData(
            ExportDeclarationInterface::EXPORT_CATEGORY,
            $apiDeclaration->getExportCategory()
        );

        $exportDeclaration->setData(
            ExportDeclarationInterface::EXPORT_REASON,
            $apiDeclaration->getExportReason()
        );

        $exportDeclaration->setData(
            ExportDeclarationInterface::INCOTERM,
            $apiDeclaration->getIncoterm()
        );

        // dependent properties: signatory
        $apiSignatory = $apiDeclaration->getSignatory();
        if ($apiSignatory) {
            $exportDeclaration->setData(
                ExportDeclarationInterface::SIGNATORY_PERSON_TITLE,
                $apiSignatory->getPersonTitle()
            );

            $exportDeclaration->setData(
                ExportDeclarationInterface::SIGNATORY_PERSON_FIRST_NAME,
                $apiSignatory->getPersonFirstName()
            );

            $exportDeclaration->setData(
                ExportDeclarationInterface::SIGNATORY_PERSON_LAST_NAME,
                $apiSignatory->getPersonLastName()
            );
        }

        // dependent properties: export codes
        $apiExportCodes = $apiDeclaration->getExportCodes();
        if ($apiExportCodes) {
            $exportDeclaration->setData(
                ExportDeclarationInterface::EDN,
                $apiExportCodes->getExportDeclarationNumber()
            );

            $exportDeclaration->setData(
                ExportDeclarationInterface::EEI,
                $apiExportCodes->getElectronicExportInformation()
            );

            $exportDeclaration->setData(
                ExportDeclarationInterface::ITN,
                $apiExportCodes->getInternalTransactionNumber()
            );

            $exportDeclaration->setData(
                ExportDeclarationInterface::EEL,
                $apiExportCodes->getExemptionExclusionLegend()
            );
        }

        return $exportDeclaration;
    }

    /**
     * @param Item[] $apiItems
     * @return ShipmentItemInterface[]
     */
    private function mapItems(array $apiItems): array
    {
        $shipmentItems = array_map(function (Item $apiItem) {
            return $this->shipmentItemFactory->create(['data' => [
                ShipmentItemInterface::QTY => $apiItem->getQuantity(),
                ShipmentItemInterface::SKU => $apiItem->getProduct()->getSku(),
            ]]);
        }, $apiItems);

        return $shipmentItems;
    }

    /**
     * @param Package[] $apiPackages
     * @return PackageInterface[]
     */
    private function mapPackages(array $apiPackages): array
    {
        // map collected packages
        $packages = array_map(function (Package $apiPackage) {
            return $this->packageMapper->map($apiPackage);
        }, $apiPackages);

        return $packages;
    }

    /**
     * @param mixed[][] $apiCapabilities
     * @return CapabilityInterface[]
     */
    public function mapCapabilities(array $apiCapabilities): array
    {
        $capabilities = [];

        foreach ($apiCapabilities as $capabilityCode => $capabilityProperties) {
            if (!is_array($capabilityProperties)) {
                $capabilityProperties = [$capabilityProperties];
            }

            $capability = $this->capabilityFactory->create(['data' => [
                CapabilityInterface::CAPABILITY_ID => $capabilityCode,
                CapabilityInterface::PROPERTIES => $capabilityProperties
            ]]);

            $capabilities[]= $capability;
        }

        return $capabilities;
    }

    /**
     * @param Shipment $apiShipment
     * @return ShipmentInterface
     */
    public function map(Shipment $apiShipment)
    {
        $shipmentId          = $apiShipment->getId();
        $shipmentOrderId     = $apiShipment->getAttributes()->getOrderId();
        $shipmentOriginId    = $apiShipment->getAttributes()->getOriginId();
        $shipmentOrder       = $apiShipment->getAttributes()->getOrder();
        $isPaperless         = $apiShipment->getAttributes()->getIsPaperless();
        $status              = $apiShipment->getAttributes()->getStatus();
        $createdAt           = $apiShipment->getAttributes()->getCreatedAt();
        $isCancelable        = $apiShipment->getMeta() ? $apiShipment->getMeta()->getIsCancelable() : false;

        $documentation       = $this->mapDocumentation($apiShipment);
        $exportDeclaration   = $this->mapExportDeclaration($apiShipment);

        $origin              = $this->mapLocation($apiShipment->getAttributes()->getOrigin());
        $destination         = $this->mapLocation($apiShipment->getAttributes()->getDestination());
        $finalRecipient      = $this->mapLocation($apiShipment->getAttributes()->getFinalRecipient());
        $shipmentFulfillment = $this->mapFulfillment($apiShipment->getAttributes()->getFulfill());
        $items               = $this->mapItems($apiShipment->getAttributes()->getItems());
        $packages            = $this->mapPackages($apiShipment->getAttributes()->getPackages());
        $capabilities        = $this->mapCapabilities($apiShipment->getAttributes()->getCapabilities());

        $shipment = $this->shipmentFactory->create(['data' => [
            ShipmentInterface::SHIPMENT_ID => $shipmentId,
            ShipmentInterface::ORDER_ID => $shipmentOrderId,
            ShipmentInterface::ORIGIN_ID => $shipmentOriginId,
            ShipmentInterface::CUSTOMER_REFERENCE => $shipmentOrder ? $shipmentOrder->getCustomerReference() : '',
            ShipmentInterface::ORIGIN_LOCATION => $origin,
            ShipmentInterface::DESTINATION_LOCATION => $destination,
            ShipmentInterface::FINAL_RECIPIENT_LOCATION => $finalRecipient,
            ShipmentInterface::FULFILLMENT => $shipmentFulfillment,
            ShipmentInterface::ITEMS => $items,
            ShipmentInterface::PACKAGES => $packages,
            ShipmentInterface::DOCUMENTATION => $documentation,
            ShipmentInterface::IS_PAPERLESS => $isPaperless,
            ShipmentInterface::EXPORT_DECLARATION => $exportDeclaration,
            ShipmentInterface::STATUS => $status,
            ShipmentInterface::CAPABILITIES => $capabilities,
            ShipmentInterface::CREATED_AT => $createdAt,
            ShipmentInterface::IS_CANCELABLE => $isCancelable,
        ]]);

        return $shipment;
    }
}
