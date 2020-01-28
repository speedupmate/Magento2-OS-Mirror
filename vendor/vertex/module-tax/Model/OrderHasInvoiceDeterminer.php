<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Vertex\Tax\Model\Repository\OrderInvoiceStatusRepository;

/**
 * Determines if an order has been sent to Vertex
 */
class OrderHasInvoiceDeterminer
{
    /** @var SearchCriteriaBuilderFactory */
    private $criteriaBuilderFactory;

    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var InvoiceSentRegistry */
    private $invoiceSentRegistry;

    /** @var OrderInvoiceStatusRepository */
    private $orderInvoiceStatusRepository;

    /**
     * @param OrderInvoiceStatusRepository $orderInvoiceStatusRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param InvoiceSentRegistry $invoiceSentRegistry
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     */
    public function __construct(
        OrderInvoiceStatusRepository $orderInvoiceStatusRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceSentRegistry $invoiceSentRegistry,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory
    ) {
        $this->orderInvoiceStatusRepository = $orderInvoiceStatusRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->invoiceSentRegistry = $invoiceSentRegistry;
    }

    /**
     * Determine whether or not an order has at least one invoice sent to Vertex
     *
     * @param int $orderId
     * @return bool
     */
    public function hasInvoice($orderId)
    {
        try {
            $this->orderInvoiceStatusRepository->getByOrderId($orderId);
            return true;
        } catch (NoSuchEntityException $e) {
            // Not a failure condition here
        }

        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter(InvoiceInterface::ORDER_ID, $orderId);
        $criteria = $criteriaBuilder->create();

        $invoices = $this->invoiceRepository->getList($criteria);
        foreach ($invoices->getItems() as $invoice) {
            if ($this->invoiceSentRegistry->hasInvoiceBeenSentToVertex($invoice)) {
                return true;
            }
        }

        return false;
    }
}
