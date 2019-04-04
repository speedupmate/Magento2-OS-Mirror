<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Tax\Model\Api\Utility;

use Vertex\Services\Invoice\RequestInterface as InvoiceRequest;
use Vertex\Services\Quote\RequestInterface as QuoteRequest;
use Vertex\Tax\Model\Config;

/**
 * Delivery Term Formatter for Vertex API Calls
 */
class DeliveryTerm
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add a Delivery Term to a Line Item if applicable
     *
     * @param QuoteRequest|InvoiceRequest $request
     * @return QuoteRequest|InvoiceRequest Same object supplied to $request
     */
    public function addIfApplicable($request)
    {
        $customerCountry = $this->getCustomerCountry($request);
        $deliveryTermOverride = $this->config->getDeliveryTermOverride();
        if ($customerCountry
            && !empty($deliveryTermOverride)
            && isset($deliveryTermOverride[$customerCountry])
        ) {
            return $request->setDeliveryTerm($deliveryTermOverride[$customerCountry]);
        }

        return $request->setDeliveryTerm($this->config->getDefaultDeliveryTerm());
    }

    /**
     * Get customer country
     *
     * @param QuoteRequest|InvoiceRequest $request
     * @return null|string
     */
    private function getCustomerCountry($request)
    {
        if ($request->getCustomer()
            && $request->getCustomer()->getDestination()
            && $request->getCustomer()->getDestination()->getCountry()
        ) {
            return $request->getCustomer()->getDestination()->getCountry();
        }

        return null;
    }
}
