<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Data\InvoiceRequestBuilder;

use Magento\Sales\Api\Data\InvoiceInterface;
use Vertex\Services\Invoice\RequestInterface as InvoiceRequestInterface;
use Vertex\Tax\Model\Api\Utility\DeliveryTerm;

class InvoiceDeliveryTermProcessor implements InvoiceProcessorInterface
{
    /** @var DeliveryTerm  */
    private $deliveryTerm;

    public function __construct(DeliveryTerm $deliveryTerm)
    {
        $this->deliveryTerm = $deliveryTerm;
    }

    /**
     * Add a Delivery Term to a Line Item if applicable
     */
    public function process(InvoiceRequestInterface $request, InvoiceInterface $invoice): InvoiceRequestInterface
    {
        $deliveryTerm = $this->deliveryTerm->getDeliveryTermForCountry(
            $this->customerCountryFromRequest($request)
        );
        return $request->setDeliveryTerm($deliveryTerm);
    }

    private function customerCountryFromRequest(InvoiceRequestInterface $request): string
    {
        if ($request->getCustomer()
            && $request->getCustomer()->getDestination()
            && $request->getCustomer()->getDestination()->getCountry()
        ) {
            return $request->getCustomer()->getDestination()->getCountry();
        }

        return '';
    }
}
