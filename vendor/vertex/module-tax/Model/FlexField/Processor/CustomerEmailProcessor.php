<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\FlexField\Processor;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Block\Adminhtml\Config\Form\Field\FlexibleFieldSource;
use Vertex\Tax\Model\FlexField\FlexFieldProcessableAttributeFactory;

class CustomerEmailProcessor implements InvoiceFlexFieldProcessorInterface, TaxCalculationFlexFieldProcessorInterface
{
    /** @var FlexFieldProcessableAttributeFactory */
    private $attributeFactory;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var ShippingAddressRetriever */
    private $shippingAddressRetriever;

    /**
     * @param FlexFieldProcessableAttributeFactory $attributeFactory
     * @param CartRepositoryInterface $cartRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ShippingAddressRetriever $shippingAddressRetriever
     */
    public function __construct(
        FlexFieldProcessableAttributeFactory $attributeFactory,
        CartRepositoryInterface $cartRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository,
        ShippingAddressRetriever $shippingAddressRetriever
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->cartRepository = $cartRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->shippingAddressRetriever = $shippingAddressRetriever;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        $attributeCode = 'customer.getEmail';

        $attribute = $this->attributeFactory->create();
        $attribute->setAttributeCode($attributeCode);
        $attribute->setLabel(__('Email')->render());
        $attribute->setOptionGroup(__('Customer')->render());
        $attribute->setType(FlexibleFieldSource::TYPE_CODE);
        $attribute->setProcessor(static::class);

        return [
            $attributeCode => $attribute,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getValueFromCreditmemo(
        CreditmemoItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getEmailFromOrderId($orderItem->getOrderId());
    }

    /**
     * @inheritDoc
     */
    public function getValueFromInvoice(InvoiceItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        try {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $this->getEmailFromOrderId($orderItem->getOrderId());
    }

    /**
     * @inheritDoc
     */
    public function getValueFromOrder(OrderItemInterface $item, $attributeCode, $fieldType = null, $fieldId = null)
    {
        return $this->getEmailFromOrderId($item->getOrderId());
    }

    /**
     * @inheritDoc
     */
    public function getValueFromQuote(
        QuoteDetailsItemInterface $item,
        $attributeCode,
        $fieldType = null,
        $fieldId = null
    ) {
        $extAttributes = $item->getExtensionAttributes();
        if (!$extAttributes || !$extAttributes->getQuoteId()) {
            return null;
        }

        try {
            $cart = $this->cartRepository->get($extAttributes->getQuoteId());
            $address = $this->shippingAddressRetriever->getShippingFromQuote($cart);

            if ($address === null) {
                return null;
            }

            return $address->getEmail();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract the email given an Order ID
     *
     * @param int $orderId
     * @return string
     */
    private function getEmailFromOrderId($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $address = $this->shippingAddressRetriever->getShippingFromOrder($order);

            if ($address === null) {
                return null;
            }

            return $address->getEmail();
        } catch (Exception $e) {
            return null;
        }
    }
}
