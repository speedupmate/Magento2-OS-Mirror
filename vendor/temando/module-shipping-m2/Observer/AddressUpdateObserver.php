<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface as SalesOrderRepositoryInterface;
use Magento\Sales\Model\Order\Address;
use Temando\Shipping\Model\Order\OrderRecipientInterface;
use Temando\Shipping\Model\Order\OrderRecipientInterfaceFactory;
use Temando\Shipping\Model\ResourceModel\Repository\OrderAttributeRepositoryInterface;
use Temando\Shipping\Model\Shipping\Carrier;

/**
 * Update shipping address observer
 *
 * @package Temando\Shipping\Observer
 * @author  Nathan Wilson <nathan.wilson@temando.com>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class AddressUpdateObserver implements ObserverInterface
{
    /**
     * @var SalesOrderRepositoryInterface
     */
    private $salesOrderRepository;

    /**
     * @var OrderAddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var OrderAttributeRepositoryInterface
     */
    private $orderAttributeRepository;

    /**
     * @var OrderRecipientInterfaceFactory
     */
    private $recipientFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * AddressUpdateObserver constructor.
     * @param SalesOrderRepositoryInterface $salesOrderRepository
     * @param OrderAddressRepositoryInterface $addressRepository
     * @param OrderAttributeRepositoryInterface $orderAttributeRepository
     * @param OrderRecipientInterfaceFactory $recipientFactory
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SalesOrderRepositoryInterface $salesOrderRepository,
        OrderAddressRepositoryInterface $addressRepository,
        OrderAttributeRepositoryInterface $orderAttributeRepository,
        OrderRecipientInterfaceFactory $recipientFactory,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->salesOrderRepository = $salesOrderRepository;
        $this->addressRepository = $addressRepository;
        $this->orderAttributeRepository = $orderAttributeRepository;
        $this->recipientFactory = $recipientFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getData('order_id');

        try {
            /** @var \Magento\Sales\Model\Order $salesOrder */
            $salesOrder = $this->salesOrderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return;
        }

        if (!$salesOrder->getData('shipping_method')) {
            // wrong type, virtual or corrupt order
            return;
        }

        $shippingMethod = $salesOrder->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            // not interested in other carriers
            return;
        }

        $addressId = $this->request->getParam('address_id');
        $address = $this->addressRepository->get($addressId);
        if ($address->getAddressType() !== Address::TYPE_SHIPPING) {
            return;
        }

        $recipient = $this->recipientFactory->create(['data' => [
            OrderRecipientInterface::COMPANY => $address->getCompany(),
            OrderRecipientInterface::LASTNAME => $address->getLastname(),
            OrderRecipientInterface::FIRSTNAME => $address->getFirstname(),
            OrderRecipientInterface::EMAIL => $address->getEmail(),
            OrderRecipientInterface::PHONE => $address->getTelephone(),
            OrderRecipientInterface::FAX => $address->getFax(),
            OrderRecipientInterface::STREET => $address->getStreet(),
            OrderRecipientInterface::COUNTRY_CODE => $address->getCountryId(),
            OrderRecipientInterface::REGION => $address->getRegionCode(),
            OrderRecipientInterface::POSTAL_CODE => $address->getPostcode(),
            OrderRecipientInterface::CITY => $address->getCity(),
        ]]);

        try {
            $this->orderAttributeRepository->saveRecipient($salesOrder, $recipient);
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
