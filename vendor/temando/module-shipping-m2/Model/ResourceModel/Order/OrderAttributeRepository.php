<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Order;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderInterface;
use Temando\Shipping\Model\Order\OrderRecipientInterface;
use Temando\Shipping\Model\ResourceModel\Repository\OrderAttributeRepositoryInterface;
use Temando\Shipping\Rest\EntityMapper\OrderResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\OrderAdapter;
use Temando\Shipping\Rest\Request\Type\Generic\UpdateOperation;
use Temando\Shipping\Rest\Request\Type\Generic\UpdateOperationFactory;
use Temando\Shipping\Rest\Request\Type\Order\RecipientFactory;
use Temando\Shipping\Rest\Request\UpdateRequestInterfaceFactory;
use Temando\Shipping\Webservice\Response\Type\OrderResponseType;

/**
 * Temando Order Attribute Repository
 *
 * @package Temando\Shipping\Model
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class OrderAttributeRepository implements OrderAttributeRepositoryInterface
{
    /**
     * @var OrderAdapter
     */
    private $apiAdapter;

    /**
     * @var UpdateRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @var RecipientFactory
     */
    private $recipientTypeFactory;

    /**
     * @var UpdateOperationFactory
     */
    private $operationFactory;

    /**
     * @var OrderResponseMapper
     */
    private $orderResponseMapper;

    /**
     * OrderAttributeRepository constructor.
     * @param OrderAdapter $apiAdapter
     * @param UpdateRequestInterfaceFactory $requestFactory
     * @param RecipientFactory $recipientTypeFactory
     * @param UpdateOperationFactory $operationFactory
     * @param OrderResponseMapper $orderResponseMapper
     */
    public function __construct(
        OrderAdapter $apiAdapter,
        UpdateRequestInterfaceFactory $requestFactory,
        RecipientFactory $recipientTypeFactory,
        UpdateOperationFactory $operationFactory,
        OrderResponseMapper $orderResponseMapper
    ) {
        $this->apiAdapter = $apiAdapter;
        $this->requestFactory = $requestFactory;
        $this->recipientTypeFactory = $recipientTypeFactory;
        $this->operationFactory = $operationFactory;
        $this->orderResponseMapper = $orderResponseMapper;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param UpdateOperation[] $operations
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    private function updateOrderAttributes($salesOrder, array $operations)
    {
        $shippingAssignments = $salesOrder->getExtensionAttributes()->getShippingAssignments();
        if (empty($shippingAssignments)) {
            throw new CouldNotSaveException(__('Unable to update order.'));
        }

        $orderId = $shippingAssignments[0]->getShipping()->getExtensionAttributes()->getExtOrderId();

        $orderRequest = $this->requestFactory->create([
            'entityId' => $orderId,
            'operations' => $operations,
        ]);

        try {
            $updatedOrder = $this->apiAdapter->patchOrder($orderRequest);
        } catch (AdapterException $e) {
            throw new CouldNotSaveException(__('Unable to update order.'), $e);
        }

        return $this->orderResponseMapper->map($updatedOrder);
    }

    /**
     * @param OrderInterface $salesOrder
     * @param OrderRecipientInterface $recipient
     * @return OrderResponseType
     * @throws CouldNotSaveException
     */
    public function saveRecipient($salesOrder, OrderRecipientInterface $recipient)
    {
        $path = '/deliverTo';

        $recipientType = $this->recipientTypeFactory->create([
            'organisationName' => $recipient->getCompany(),
            'lastname' => $recipient->getLastname(),
            'firstname' => $recipient->getFirstname(),
            'email' => $recipient->getEmail(),
            'phoneNumber' => $recipient->getPhone(),
            'faxNumber' => $recipient->getFax(),
            'nationalId' => $recipient->getNationalId(),
            'taxId' => $recipient->getTaxId(),
            'street' => (array) $recipient->getStreet(),
            'countryCode' => $recipient->getCountryCode(),
            'administrativeArea' => $recipient->getRegion(),
            'postalCode' => $recipient->getPostalCode(),
            'locality' => $recipient->getCity(),
        ]);

        $operation = $this->operationFactory->create([
            'operation' => UpdateOperation::OPERATION_REPLACE,
            'path' => $path,
            'value' => $recipientType,
        ]);

        return $this->updateOrderAttributes($salesOrder, [$operation]);
    }
}
