<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\Response\Type\Shipment;

use Temando\Shipping\Rest\Response\Type\Generic\Location;
use Temando\Shipping\Rest\Response\Type\Shipment\Attributes\ExportDeclaration;
use Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Order;
use Temando\Shipping\Rest\Response\Type\Shipment\Attributes\SelectedServices;

/**
 * Temando API Shipment Attributes Response Type
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.temando.com/
 */
class Attributes
{
    /**
     * @var string
     */
    private $pickupAt;

    /**
     * @var string
     */
    private $expectedAt;

    /**
     * @var bool
     */
    private $isDutiable;

    /**
     * @var bool
     */
    private $isPaperless;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Location
     */
    private $origin;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Location
     */
    private $destination;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Location
     */
    private $finalRecipient;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Package[]
     */
    private $packages = [];

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Documentation[]
     */
    private $documentation = [];

    /**
     * @var string
     */
    private $instructionsToDeliveryAgent;

    /**
     * Anonymous list of capabilities.
     *
     * $format = [
     *   'capabilityCodeX' => [
     *     'propertyOne' => 'valueOne',
     *     'propertyTwo' => 'valueTwo',
     *   ],
     *   'capabilityCodeY' => [
     *     'propertyOne' => 'valueOne',
     *   ],
     * ]
     *
     * @var mixed[][]
     */
    private $capabilities = [];

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\ExportDeclaration
     */
    private $exportDeclaration;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Order
     */
    private $order;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private $originId;

    /**
     * @var string
     */
    private $destinationId;

    /**
     * Shipment Status OR Shipment Allocation Error Status
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $completionId;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Fulfill
     */
    private $fulfill;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\SelectedServices
     */
    private $selectedServices;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $modifiedAt;

    /**
     * @var \Temando\Shipping\Rest\Response\Type\Generic\Item[]
     */
    private $items = [];

    /**
     * Shipment Allocation Error Title
     * @var string
     */
    private $title;

    /**
     * Shipment Allocation Error Code
     * @var string
     */
    private $code;

    /**
     * Shipment Allocation Error Detail
     * @var string
     */
    private $detail;

    /**
     * @var string[]
     */
    private $customAttributes;

    /**
     * @return string
     */
    public function getPickupAt()
    {
        return $this->pickupAt;
    }

    /**
     * @param string $pickupAt
     * @return void
     */
    public function setPickupAt($pickupAt)
    {
        $this->pickupAt = $pickupAt;
    }

    /**
     * @return string
     */
    public function getExpectedAt()
    {
        return $this->expectedAt;
    }

    /**
     * @param string $expectedAt
     * @return void
     */
    public function setExpectedAt($expectedAt)
    {
        $this->expectedAt = $expectedAt;
    }

    /**
     * @return boolean
     */
    public function getIsDutiable()
    {
        return $this->isDutiable;
    }

    /**
     * @param boolean $isDutiable
     * @return void
     */
    public function setIsDutiable($isDutiable)
    {
        $this->isDutiable = $isDutiable;
    }

    /**
     * @return boolean
     */
    public function getIsPaperless()
    {
        return $this->isPaperless;
    }

    /**
     * @param boolean $isPaperless
     * @return void
     */
    public function setIsPaperless($isPaperless)
    {
        $this->isPaperless = $isPaperless;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Location
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Location $origin
     * @return void
     */
    public function setOrigin(Location $origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Location
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Location $destination
     * @return void
     */
    public function setDestination(Location $destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Location
     */
    public function getFinalRecipient()
    {
        return $this->finalRecipient;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Location $finalRecipient
     * @return void
     */
    public function setFinalRecipient(Location $finalRecipient)
    {
        $this->finalRecipient = $finalRecipient;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Package[]
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Package[] $packages
     * @return void
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Documentation[]
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Documentation[] $documentation
     * @return void
     */
    public function setDocumentation(array $documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * @return string
     */
    public function getInstructionsToDeliveryAgent()
    {
        return $this->instructionsToDeliveryAgent;
    }

    /**
     * @param string $instructionsToDeliveryAgent
     * @return void
     */
    public function setInstructionsToDeliveryAgent($instructionsToDeliveryAgent)
    {
        $this->instructionsToDeliveryAgent = $instructionsToDeliveryAgent;
    }

    /**
     * @return mixed[][]
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * @param mixed[][] $capabilities
     * @return void
     */
    public function setCapabilities(array $capabilities)
    {
        $this->capabilities = $capabilities;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\ExportDeclaration
     */
    public function getExportDeclaration()
    {
        return $this->exportDeclaration;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\ExportDeclaration $exportDeclaration
     * @return void
     */
    public function setExportDeclaration(ExportDeclaration $exportDeclaration)
    {
        $this->exportDeclaration = $exportDeclaration;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Order $order
     * @return void
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return void
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param string $originId
     * @return void
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;
    }

    /**
     * @return string
     */
    public function getDestinationId()
    {
        return $this->destinationId;
    }

    /**
     * @param string $destinationId
     * @return void
     */
    public function setDestinationId($destinationId)
    {
        $this->destinationId = $destinationId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCompletionId()
    {
        return $this->completionId;
    }

    /**
     * @param string $completionId
     * @return void
     */
    public function setCompletionId($completionId)
    {
        $this->completionId = $completionId;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Fulfill
     */
    public function getFulfill()
    {
        return $this->fulfill;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\Fulfill $fulfill
     * @return void
     */
    public function setFulfill($fulfill)
    {
        $this->fulfill = $fulfill;
    }

    /**
     * @return \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\SelectedServices
     */
    public function getSelectedServices()
    {
        return $this->selectedServices;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Shipment\Attributes\SelectedServices $selectedServices
     * @return void
     */
    public function setSelectedServices(SelectedServices $selectedServices)
    {
        $this->selectedServices = $selectedServices;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param string $modifiedAt
     * @return void
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }
    /**
     * @return \Temando\Shipping\Rest\Response\Type\Generic\Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param \Temando\Shipping\Rest\Response\Type\Generic\Item[] $items
     * @return void
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return string[]
     */
    public function getCustomAttributes()
    {
        return $this->customAttributes;
    }

    /**
     * @param string[] $customAttributes
     * @return void
     */
    public function setCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = $customAttributes;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     * @return void
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }
}
