<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Utility;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Vertex\Tax\Model\ExceptionLogger;

/**
 * Determine whether or not a line item is virtual
 *
 * @api
 * @since 3.4.0
 */
class IsVirtualLineItemDeterminer
{
    /**  @var ExceptionLogger */
    private $logger;

    /** @var Type */
    private $productType;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var OrderItemRepositoryInterface */
    private $orderItemRepository;

    public function __construct(
        ExceptionLogger $logger,
        ProductRepositoryInterface $productRepository,
        Type $productType,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->productType = $productType;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function isCartItemVirtual(AbstractItem $cartItem) : bool
    {
        if ($cartItem instanceof Item && $cartItem->getIsVirtual() !== null) {
            return (bool)$cartItem->getIsVirtual();
        }

        try {
            $product = $this->productRepository->get($cartItem->getSku());
            return $this->productType->factory($product)->isVirtual($product);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning($e);
            return false;
        }
    }

    public function isCreditMemoItemVirtual(CreditmemoItemInterface $creditMemoItem) : bool
    {
        $orderItemId = $creditMemoItem->getOrderItemId();
        $orderItem = $this->getOrderItemById($orderItemId);
        return $orderItem ? $this->isOrderItemVirtual($orderItem) : false;
    }

    public function isInvoiceItemVirtual(InvoiceItemInterface $invoiceItem) : bool
    {
        $orderItemId = $invoiceItem->getOrderItemId();
        $orderItem = $this->getOrderItemById($orderItemId);
        return $orderItem ? $this->isOrderItemVirtual($orderItem) : false;
    }

    public function isOrderItemVirtual(OrderItemInterface $orderItem) : bool
    {
        return (bool)$orderItem->getIsVirtual();
    }

    public function isQuoteDetailsItemVirtual(QuoteDetailsItemInterface $quoteDetailsItem) : bool
    {
        return $quoteDetailsItem->getExtensionAttributes()
            ? $quoteDetailsItem->getExtensionAttributes()->getIsVirtual()
            : false;
    }

    private function getOrderItemById($orderItemId) : ?OrderItemInterface
    {
        try {
            return $this->orderItemRepository->get($orderItemId);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning($e);
            return null;
        }
    }
}
