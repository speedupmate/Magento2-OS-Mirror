<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Api\Utility;

use Vertex\Services\Invoice\RequestInterface;
use Vertex\Services\Quote\RequestInterface as QuoteRequestInterface;
use Vertex\Services\Invoice\RequestInterface as InvoiceRequestInterface;
use Vertex\Tax\Model\Config;

class DeliveryTerm
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getDeliveryTermForCountry(string $customerCountry): string
    {
        $deliveryTermOverride = $this->config->getDeliveryTermOverride();
        if ($customerCountry
            && !empty($deliveryTermOverride)
            && isset($deliveryTermOverride[$customerCountry])
        ) {
            return $deliveryTermOverride[$customerCountry];
        }

        return $this->config->getDefaultDeliveryTerm();
    }
}
