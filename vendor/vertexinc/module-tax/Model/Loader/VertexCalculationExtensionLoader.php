<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Loader;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Vertex\Tax\Model\ExceptionLogger;
use Vertex\Tax\Model\Repository\VatCountryCodeRepository;

/**
 * Handles loading and assignment of necessary data for Tax Calculation purposes
 */
class VertexCalculationExtensionLoader
{
    /** @var OrderAddressRepositoryInterface */
    private $addressRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var VatCountryCodeRepository */
    private $vatCountryCodeRepository;

    /** @var ExceptionLogger */
    private $logger;

    public function __construct(
        OrderAddressRepositoryInterface $addressRepository,
        OrderRepositoryInterface $orderRepository,
        VatCountryCodeRepository $vatCountryCodeRepository,
        ExceptionLogger $logger
    ) {
        $this->addressRepository = $addressRepository;
        $this->orderRepository = $orderRepository;
        $this->vatCountryCodeRepository = $vatCountryCodeRepository;
        $this->logger = $logger;
    }

    /**
     * Load the addresses and Order for an Invoice onto the Vertex Tax Calculation extension attributes
     *
     * This is necessary as there are two different ways an Invoice comes across the wire with respect to the addresses
     * attached to it:
     *
     * The first way is when it is an existing Order that is invoiced.  In this scenario, the Billing Address ID and the
     * Shipping Address ID are set on the Invoice.
     *
     * The second way is when it is a newly placed Order on the frontend, with an online payment method that captures
     * payment and triggers an invoice.  In this scenario, the Order's addresses are not yet in the database and are not
     * yet tied to the Invoice.
     *
     * Additionally, during the second scenario we do not want to load up the Order through the repository, as it can
     * cause additional issues.  In this scenario, we want to use the Order that is already attached to the Invoice.
     *
     * @param Invoice $originalInvoice
     * @return Invoice
     */
    public function loadOnInvoice(Invoice $originalInvoice)
    {
        $invoice = clone $originalInvoice;

        $invoice->setExtensionAttributes(clone $originalInvoice->getExtensionAttributes());

        try {
            $order = $invoice->getOrder() ?: $this->orderRepository->get($invoice->getOrderId());
        } catch (\Exception $e) {
            $order = null;
        }

        $invoice->getExtensionAttributes()
            ->setVertexTaxCalculationOrder($order);

        if ($order && $order->getBillingAddress()) {
            /** Manually loading the Vat Country Code Id since address is not loaded using a repository at this point */
            $countryCode = $this->getVatCountryCode($order->getBillingAddress()->getEntityId());
            $order->getBillingAddress()->getExtensionAttributes()->setVertexVatCountryCode($countryCode);

            $invoice->getExtensionAttributes()
                ->setVertexTaxCalculationBillingAddress($order->getBillingAddress());
        } elseif ($invoice->getBillingAddressId()) {
            $invoice->getExtensionAttributes()
                ->setVertexTaxCalculationBillingAddress(
                    $this->addressRepository->get($invoice->getBillingAddressId())
                );
        }

        if ($order instanceof Order && $order->getShippingAddress()) {
            /** Manually loading the Vat Country Code Id since address is not loaded using a repository at this point */
            $countryCode = $this->getVatCountryCode($order->getShippingAddress()->getEntityId());
            $order->getShippingAddress()->getExtensionAttributes()->setVertexVatCountryCode($countryCode);

            $invoice->getExtensionAttributes()
                ->setVertexTaxCalculationShippingAddress($order->getShippingAddress());
        } elseif ($invoice->getShippingAddressId()) {
            $invoice->getExtensionAttributes()
                ->setVertexTaxCalculationShippingAddress(
                    $this->addressRepository->get($invoice->getShippingAddressId())
                );
        }

        return $invoice;
    }

    /**
     * Load the country code attached to the address, if any
     *
     * @param string $addressId
     * @return string|null
     */
    private function getVatCountryCode($addressId)
    {
        try {
            $countryCode = null;
            $countryCodeItem = $this->vatCountryCodeRepository->getByAddressId($addressId);
            if ($countryCodeItem !== null) {
                $countryCode = $countryCodeItem->getVatCountryCode();
            }
        } catch (NoSuchEntityException $exception) {
            $countryCode = null;
        } catch (\Exception $exception) {
            $countryCode = null;
            $this->logger->critical($exception);
        }

        return $countryCode;
    }
}
